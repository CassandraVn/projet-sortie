<?php

namespace App\Repository;

use App\Entity\Sortie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Sortie>
 *
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

    public function save(Sortie $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Sortie $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByFiltre($params=false)
    {
        $query =  $this->createQueryBuilder('s')
                        ->join("s.etat", "e")
                        ->where("e.libelle != 'Annulée'");

        if( isset($params["campus"]) )
        {
            $query = $query->andWhere("s.campus = :campus");
        }
        if( isset($params["nomSortie"]) )
        {
            $query = $query->andWhere("s.nom like :nomSortie");
        }

        if( isset($params["dateDepuis"]) and isset($params["dateUntil"]) )
        {
            $query = $query->andWhere('s.dateHeureDebut BETWEEN :dateDepuis AND :dateUntil');
        }

        if( isset($params["orga"]) )
        {
            $query = $query->andWhere("s.Organisateur = :user");
        }
        if( isset($params["inscrit"]) )
        {
            $query = $query->andWhere(":user MEMBER OF s.Participant");
        }
        if( isset($params["pasInscrit"]) )
        {
            $query = $query->andWhere(":user NOT MEMBER OF s.Participant");
        }
        if( isset($params["passees"]) )
        {
            $query = $query->andWhere("e.libelle = 'Passée'");
        }

        if( isset($params["campus"]) )
        {
            $query = $query->setParameter('campus', $params["campus"]);
        }
        if( isset($params["dateDepuis"]) and isset($params["dateUntil"]) )
        {
            $query = $query->setParameter('dateDepuis', $params["dateDepuis"]);
            $query = $query->setParameter('dateUntil', $params["dateUntil"]);
        }
        if( isset($params["pasInscrit"]) or isset($params["inscrit"]) or isset($params["orga"]) )
        {
            $query = $query->setParameter('user', $params["user"]);
        }
        if( isset($params["nomSortie"]) )
        {
            $query = $query->setParameter('nomSortie', $params["nomSortie"]);
        }

        return $query->getQuery()->getResult();
    }

//    /**
//     * @return Sortie[] Returns an array of Sortie objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Sortie
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
