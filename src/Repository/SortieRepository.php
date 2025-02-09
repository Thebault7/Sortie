<?php

namespace App\Repository;

use App\Constantes\EtatConstantes;
use Doctrine\ORM\Query\Expr\Expr\Comparison;
//use Doctrine\Common\Collections\Expr\Comparison;
use App\Entity\Sortie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Sortie|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sortie|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sortie[]    findAll()
 * @method Sortie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SortieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sortie::class);
    }

    public function findBySite($site)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.site = :val')
            ->setParameter('val', $site)
            ->getQuery()
            ->getResult();
    }

    public function findByEtat($etat)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.etat = :val')
            ->setParameter('val', $etat)
            ->getQuery()
            ->getResult();
    }

    public function findBySiteAndEtat($site, $etat)
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.site = :val1', 'f.etat = :val2')
            ->setParameter('val1', $site)
            ->setParameter('val2', $etat)
            ->getQuery()
            ->getResult();
    }

    public function findSortieDontJeSuisOrganisateur($idUser){
           // $etat1 = EtatConstantes::ANNULE;
            $etat2 = EtatConstantes::ARCHIVE;

            return $this->createQueryBuilder('s')
                ->join('s.etat', 'e')
                ->andWhere('s.participant = :val1')
                //->andWhere('e.libelle != :etat1')
                ->andWhere('e.libelle != :etat2')
                ->setParameter('val1', $idUser)
               // ->setParameter('etat1', $etat1)
                ->setParameter('etat2', $etat2)
                ->getQuery()
                ->getResult();
    }

    public function findSortieAuxquellesJeSuisInscrit($idUser){

        $etat2 = EtatConstantes::ARCHIVE;

        return $this->createQueryBuilder('s')
            ->join('s.participants', 'p')
            ->join('s.etat', 'e')
            ->where('p.id = :val1')
            ->andWhere('e.libelle != :etat2')
            ->setParameter('val1', $idUser)
            ->setParameter('etat2', $etat2)
            ->getQuery()
            ->getResult();
    }

    // /**
    //  * @return Sortie[] Returns an array of Sortie objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Sortie
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
