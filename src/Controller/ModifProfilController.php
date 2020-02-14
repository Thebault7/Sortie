<?php


namespace App\Controller;


use App\Entity\Participant;
use App\Form\ModifProfilType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


class ModifProfilController extends Controller
{
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

                // mise en base de données
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash("success", "Modification du profil réussie.");
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