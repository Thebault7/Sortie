<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Entity\Sortie;
use App\Entity\Etat;
use App\Constantes\EtatConstantes;
use App\Form\ParticipantType;
use App\Form\RegistrationFormType;
use App\Form\UploadUsersCSVType;
use App\Services\UploadUsersFromCsv;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class RegistrationController extends Controller
{
    /**
     * @Route("/admin/register", name="register")
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return Response
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder, EntityManagerInterface $entityManager): Response
    {
        $participant = new Participant();
        $form = $this->createForm(ParticipantType::class, $participant);
        $form->handleRequest($request);


        $uploadForm = $this->createForm(UploadUsersCSVType::class, ['uploadUsersCsv' => '']);
        $uploadForm->handleRequest($request);



        if ($form->isSubmitted() && $form->isValid()) {
            $participant->setActif(true);
            $participant->setPhoto(" ");
            $motDePasse = "Azerty44";

//            // génération d'un mot de passe aléatoire de 10 chiffres
//            for ($i = 0; $i < 10; $i++) {
//                $motDePasse = $motDePasse . rand() % (10);
//            }

            // cryptage du mot de passe
            $participant->setPassword(
                $passwordEncoder->encodePassword(
                    $participant,
                    $motDePasse
                )
            );

            $participant->setPhoto('a garder/silhouette.jpg');
            // mise en base de données
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($participant);
            $entityManager->flush();

            return $this->redirectToRoute('login');
        }




        if ($uploadForm->isSubmitted() && $uploadForm->isValid()) {
            $csvFile = $uploadForm->get('uploadUsersCsv')->getData();

            if($csvFile){
                $nomOriginal = pathinfo($csvFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = transliterator_transliterate(
                    'Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()',
                    $nomOriginal
                );

                $nouveauNom = $safeFilename .'.csv' ; //$csvFile->guessExtension()

                try {
                    $csvFile->move(
                        $this->getParameter('users_csv_directory'),
                        $nouveauNom
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                    $this->addFlash("danger", "Echec du téléchargement du fichier.");
                }
                $path=$this->getParameter('users_csv_directory');
                $path = $path . '/' . $nouveauNom;


                //appel service qui lit le fichier et insere les donnees dans la BDD
                $uploadCSV = new UploadUsersFromCsv($entityManager, $passwordEncoder, $path);
                $message = $uploadCSV->uploadCsv();

                // suppression du fichier du dossier 'public'
                if ($csvFile) {
                    $filesystem = new Filesystem();
                    try {
                        $filesystem->remove($path);
                    } catch (IOExceptionInterface $exception) {
                        $this->addFlash( "danger","Une Erreur est apparue lors de la suppression du fichier  ".$exception->getPath());
                    }
                }

                if($message){
                    $this->addFlash("success", $message);
                }
                else{
                    $this->addFlash("danger", $message);
                }

                return $this->redirectToRoute('accueil');
            }else {
                $this->addFlash("danger", "Echec lors de l'ajout de nouveaux utilisateurs.");
                return $this->redirectToRoute('accueil');
            }


        }


        return $this->render(
            'registration/register.html.twig',
            [
                'ParticipantForm' => $form->createView(),
                'UploadCSVFormView' => $uploadForm->createView()
            ]
        );
    }

    /**
     * @Route("/admin/delete/{id}", name="delete_user")
     * @param $id
     * @param EntityManagerInterface $entityManager
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function delete(
        $id,
        EntityManagerInterface $entityManager,
        UserPasswordEncoderInterface $passwordEncoder,
        Request $request
    ): Response {
        $participantRepository = $entityManager->getRepository(Participant::class);
        $participant = $participantRepository->find($id);

        $sortieRepository = $entityManager->getRepository(Sortie::class);
        $sorties = $sortieRepository->findAll();

        $etatRepository = $entityManager->getRepository(Etat::class);
        $etatArchive = $etatRepository->findOneBy(['libelle' => EtatConstantes::ARCHIVE]);

        $participantPeutEtreSupprime = true;

        for ($i = 0; $i < count($sorties); $i++) {

            // Si la sortie est archivée, on ne la considère pas pour savoir si l'utilisateur qui
            // y participait est encore actif
            if ($sorties[$i]->getEtat() !== $etatArchive) {
                
                // on vérifie si l'utilisateur est inscrit dans une sortie
                for ($j = 0; $j < count($sorties[$i]->getParticipants()); $j++) {
                    if ($sorties[$i]->getParticipants()[$j]->getId() === $participant->getId()) {
                        $participantPeutEtreSupprime = false;
                    }
                }

                // on vérifie si l'utilisateur est l'organisateur de la sortie
                if ($sorties[$i]->getParticipant()->getId() === $participant->getId()) {
                    $participantPeutEtreSupprime = false;
                }
            }
        }

        if ($participantPeutEtreSupprime) {
            if ($request->query->get('a_supprimer') === "1") {
                // on supprime le fichier image qui est dans assts/img
                $photo = $participant->getPhoto();
                if ($photo !== "a garder/silhouette.jpg") {
                    $filesystem = new Filesystem();
                    try {
                        $filesystem->remove('assets/img/'.$photo);
                    } catch (IOExceptionInterface $exception) {
                        echo "Une Erreur est apparue lors de la suppression du fichier  ".$exception->getPath();
                    }
                }


                $entityManager->remove($participant);
                $entityManager->flush();    // si suppression totale, on enlève l'utilisateur de la base de données

            } else {
                $participant->setActif(false);
                $entityManager->persist($participant);
                $entityManager->flush();    // si inactivation du compte, on ne fait que changer le booléen 'actif'
            }
        } else {
            $this->addFlash(
                "warning",
                "L'utilisateur est encore actif. Veuillez supprimer toutes ses inscriptions et ses sorties avant de pouvoir inactiver ou supprimer son compte."
            );
        }

        return $this->redirectToRoute('accueil');
    }

    /**
     * @Route("/admin/activate/{id}", name="activate_user")
     * @param $id
     * @param EntityManagerInterface $entityManager
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function activate(
        $id,
        EntityManagerInterface $entityManager,
        Request $request
    ): Response {
        $participantRepository = $entityManager->getRepository(Participant::class);
        $participant = $participantRepository->find($id);

        $participant->setActif(true);

        $entityManager->persist($participant);
        $entityManager->flush();

        return $this->redirectToRoute('accueil');
    }
}
