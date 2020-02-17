<?php

namespace App\Controller;
use App\Entity\Participant;
use App\Form\ModifProfilType;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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

        return $this->render
        (
            'profil/afficherprofil.html.twig',
            compact( 'participant'));

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

                    $nomOriginalImg = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
                    // this is needed to safely include the file name as part of the URL
                    $safeFilename = transliterator_transliterate(
                        'Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()',
                        $nomOriginalImg
                    );
                    $nouveauNomImg = $safeFilename.'-'.uniqid().'.'.$image->guessExtension();

                    // Move the file to the directory where brochures are stored
                    try {
                        $image->move(
                            $this->getParameter('images_directory'),
                            $nouveauNomImg
                        );
                    } catch (FileException $e) {
                        // ... handle exception if something happens during file upload
                    }

                    // permet de stocker dans la bdd le nouveau nom du fichier
                    $user->setPhoto($nouveauNomImg);
                }

                // mise en base de données
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash("success", "Modification du profil réussie.");
                return $this->redirectToRoute('afficherprofil');

            } else {
                $this->addFlash("échec", "Ce pseudo existe déjà. Veuillez en choisir un autre.");
            }
        }

        return $this->render('profil/modifprofil.html.twig', [
            'ModifProfilForm' => $form->createView(),
            'user' => $user,
        ]);
    }
}
