<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Entity\Sortie;
use App\Entity\Etat;
use App\Constantes\EtatConstantes;
use App\Form\ParticipantType;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        $participant = new Participant();
        $form = $this->createForm(ParticipantType::class, $participant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $participant->setActif(true);
            $participant->setPhoto(" ");
            $motDePasse = "azerty";

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

            $participant->setPhoto(null);
            // mise en base de données
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($participant);
            $entityManager->flush();

            return $this->redirectToRoute('login');
        }

        return $this->render(
            'registration/register.html.twig',
            [
                'ParticipantForm' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/admin/delete/{id}", name="delete_user")
     * @param $id
     * @param EntityManagerInterface $entityManager
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function delete($id, EntityManagerInterface $entityManager, UserPasswordEncoderInterface $passwordEncoder): Response
    {
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
            if ($sorties[$i]->getEtat() === $etatArchive) {
                $participantPeutEtreSupprime = false;
            } else {

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

        $motDePasse = "";
        // génération d'un mot de passe aléatoire de 10 chiffres
        for ($i = 0; $i < 10; $i++) {
            $motDePasse = $motDePasse.rand() % (10);
        }

        // cryptage du mot de passe
        $participant->setPassword(
            $passwordEncoder->encodePassword(
                $participant,
                $motDePasse
            )
        );

        if ($participantPeutEtreSupprime) {
            $participant->setActif(false);
            $entityManager->persist($participant);
            $entityManager->flush();
        } else {
            $this->addFlash(
                "failure",
                "L'utilisateur est encore actif. Veuillez supprimer toutes ses inscriptions et ses sorties avant de pouvoir supprimer sont compte."
            );
        }

        return $this->redirectToRoute('accueil');
    }
}
