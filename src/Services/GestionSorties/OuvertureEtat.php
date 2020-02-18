<?php


namespace App\Services\GestionSorties;

use App\Entity\Sortie;
use App\Entity\Etat;
use Doctrine\ORM\EntityManagerInterface;

class OuvertureEtat
{
    private $em;

//$etatOuvert = $etatRepository->findOneBy(['libelle' => 'Ouvert']);
    // $etatFerme = $etatRepository->findOneBy(['libelle' => 'Clôturé']);

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    // si il y a un desistement et que la date limite d'inscriptions n'a pas ete depasse
    public function ouvrir()
    {
        $sorties = $this->em->getRepository(Sortie::class)->findAll();
        $etatRepository = $this->em->getRepository(Etat::class);
        $etatOuvert = $etatRepository->findOneBy(['libelle' => 'Ouvert']);

        for($i = 0; $i < count($sorties); $i++){

            $nbInscriptionsMax = $sorties[$i]->getNbInscriptionMax();
            $participantsInscrits = count($sorties[$i]->getParticipants());
            $dateLimiteInscriptions = $sorties[$i]->getDateLimiteInscription();

            if(($participantsInscrits < $nbInscriptionsMax) && (new \DateTime() < $dateLimiteInscriptions) ){
                    $sorties[$i]->setEtat($etatOuvert);
                    $this->em->persist($sorties[$i]);
                    $this->em->flush();
            }
        }
    }

    //supprime la sortie qui n'a pas ete publiee et dont la date d'inscriptions maximale a ete depassee
    public function suprimerSiPasPublie(){

        $sorties = $this->em->getRepository(Sortie::class)->findAll();
        $etatRepository = $this->em->getRepository(Etat::class);
        $etatCree = $etatRepository->findOneBy(['libelle' => 'Créé']);
        $maintenant = new \DateTime();

        for($i = 0; $i < count($sorties); $i++){
            if(
                $sorties[$i]->getEtat()->getId() === $etatCree->getId() &&
                $sorties[$i]->getDateLimiteInscription() >= $maintenant)
            {
                $this->em->remove($sorties[$i]);
                dump($sorties[$i]);
                $this->em->flush();
            }
        }

    }


}