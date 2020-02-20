<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ModifProfilType;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class ParticipantController extends Controller
{
    /**
     * @Route("/participant", name="participant")
     */
    public function index()
    {
        return $this->render('registration/register.html.twig');
    }

    /**
     * @Route("/afficherprofil/{id}", name="afficherprofil", requirements={"id": "\d+"})
     */
    public function afficherprofil($id, EntityManagerInterface $entityManager)
    {
        $participantRepository = $entityManager->getRepository(Participant::class);
        $participant = $participantRepository->find($id);

        if ($participant->getPhoto() === "") {
            // le participant n'a pas de photo de définie. On lui donne une taille par défaut.
            $widthPhoto = 250;
            $heightPhoto = 250;
        } else {
            $photo = $participant->getPhoto();
            $taillePhoto = getimagesize('assets/img/' . $photo);
            $widthPhoto = $taillePhoto[0];
            $heightPhoto = $taillePhoto[1];
        }

        return $this->render
        (
            'profil/afficherprofil.html.twig',
            compact('participant', 'widthPhoto', 'heightPhoto')
        );
    }

    /**
     * @Route("/modifprofil", name="modifprofil")
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return
     */
    public function modifprofil(Request $request, EntityManagerInterface $entityManager, UserPasswordEncoderInterface $passwordEncoder)
    {
        $user = $this->getUser();
        $form = $this->createForm(ModifProfilType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // on vérifie si le pseudo fourni n'existe pas déjà en base de données
            $pseudoAChercher = $user->getPseudo();
            $participantRepository = $entityManager->getRepository(Participant::class);
            $participant = $participantRepository->findByPseudo($pseudoAChercher);

            if ($participant === [] || $participant[0]->getPseudo() === $user->getPseudo()) {
                $user->setPassword(
                    $passwordEncoder->encodePassword(
                        $user,
                        $user->getPassword()
                    )
                );

                $image = $form->get('photo')->getData();
                // tester si le champ est vide ou pas
                if ($image) {
                    $ancienNomImg = $user->getPhoto();
                    $nomOriginalImg = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
                    // this is needed to safely include the file name as part of the URL
                    $safeFilename = transliterator_transliterate(
                        'Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()',
                        $nomOriginalImg
                    );
                    $nouveauNomImg = $safeFilename . '-' . uniqid() . '.' . $image->guessExtension();

                    // Move the file to the directory where brochures are stored
                    try {
                        $image->move(
                            $this->getParameter('images_directory'),
                            $nouveauNomImg
                        );
                    } catch (FileException $e) {
                        // ... handle exception if something happens during file upload
                        $this->addFlash("danger", "Echec du téléchargement de l'image.");
                    }

                    // permet de stocker dans la bdd le nouveau nom du fichier
                    $user->setPhoto($nouveauNomImg);
                }

                // l'ancienne photo doit être enlevée de /assets/img pour être remplacée par la nouvelle, si
                // une nouvelle photo a été fournie par l'utilisateur
                if ($image) {
                    $filesystem = new Filesystem();
                    try {
                        $filesystem->remove('assets/img/'.$ancienNomImg);
                    } catch (IOExceptionInterface $exception) {
                        $this->addFlash( "danger","Une Erreur est apparue lors de la suppression du fichier  ".$exception->getPath());
                    }
                }

                // mise en base de données
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash("success", "Modification du profil réussie.");
                return $this->redirectToRoute('afficherprofil', ['id' => $user->getId()]);

            } else {
                $this->addFlash("warning", "Ce pseudo existe déjà. Veuillez en choisir un autre.");
            }
        }

        return $this->render('profil/modifprofil.html.twig', [
            'ModifProfilForm' => $form->createView(),
            'user' => $user,
        ]);
    }
}
