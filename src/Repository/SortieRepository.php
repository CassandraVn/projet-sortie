<?php

namespace App\Repository;

use App\Entity\Sortie;
use App\Entity\Utilisateur;
use App\Form\model\FiltreFormModel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query;
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

    public function findAllSorties(Utilisateur $user) {
        $query = $this->createQueryBuilder('s')
            ->select('s, e, l, c, o, v, p')
            ->join('s.etat', 'e')
            ->join('s.lieu', 'l')
            ->join('s.campus', 'c')
            ->join('s.Organisateur', 'o')
            ->join('l.ville', 'v')
            ->leftJoin('s.Participant', 'p');

        if(
            is_array($user->getRoles()) and !in_array('ROLE_ADMIN', $user->getRoles()) or
            !is_array($user->getRoles()) and !$user->getRoles() == 'ROLE_ADMIN'
        )
        {
            $query = $query->where("e.libelle != 'Annulée' and e.libelle != 'Historisée'");
        }

        return $query->orderBy('s.dateHeureDebut', 'ASC')
            ->getQuery()
            ->getResult(Query::HYDRATE_OBJECT);
    }

    public function findSortieById(int $id) {
        return $this->createQueryBuilder('s')
            ->select('s, e, l, c, o, v, p')
            ->join('s.etat', 'e')
            ->join('s.lieu', 'l')
            ->join('s.campus', 'c')
            ->join('s.Organisateur', 'o')
            ->join('l.ville', 'v')
            ->leftJoin('s.Participant', 'p')
            ->where('s.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findParticipantsSortieById(int $id) {
        return $this->createQueryBuilder('s')
            ->select('s')
            ->innerJoin('s.Participant', 'p')
            ->where('s.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getResult(Query::HYDRATE_OBJECT);
    }

    public function findByFiltre(FiltreFormModel $filtre, Utilisateur $user)
    {
        $query =  $this->createQueryBuilder('s')
            ->select('s, e, l, c, o, v, p')
            ->join('s.etat', 'e')
            ->join('s.lieu', 'l')
            ->join('s.campus', 'c')
            ->join('s.Organisateur', 'o')
            ->join('l.ville', 'v')
            ->leftJoin('s.Participant', 'p');

        if(
            is_array($user->getRoles()) and !in_array('ROLE_ADMIN', $user->getRoles()) or
            !is_array($user->getRoles()) and !$user->getRoles() == 'ROLE_ADMIN'
        )
        {
            $query = $query->andWhere("e.libelle != 'Annulée'");
            $query = $query->andWhere("e.libelle != 'Historisée'");
        }


        if( $filtre->getCampus() )
        {
            $query = $query->andWhere("s.campus = :campus");
        }
        if( $filtre->getNomSortie() )
        {
            $query = $query->andWhere("s.nom like :nomSortie");
        }
        if($filtre->getDateDepuis() and $filtre->getDateUntil() )
        {
            $query = $query->andWhere('s.dateHeureDebut BETWEEN :dateDepuis AND :dateUntil');
        }
        if( $filtre->getOrganisateur() )
        {
            $query = $query->andWhere("s.Organisateur = :user");
        }
        if(  $filtre->getInscrit() )
        {
            $query = $query->andWhere(":user MEMBER OF s.Participant");
        }
        if( $filtre->getPasInscrit() )
        {
            $query = $query->andWhere(":user NOT MEMBER OF s.Participant");
        }
        if( $filtre->getPassees() )
        {
            $query = $query->andWhere("e.libelle = 'Passée'");
        }
        if( $filtre->getCampus() )
        {
            $query = $query->setParameter('campus', $filtre->getCampus());
        }
        if( $filtre->getNomSortie() )
        {
            $query = $query->setParameter('nomSortie', "%".$filtre->getNomSortie()."%");
        }
        if($filtre->getDateDepuis() and $filtre->getDateUntil() )
        {
            $query = $query->setParameter('dateDepuis', $filtre->getDateDepuis() );
            $query = $query->setParameter('dateUntil', $filtre->getDateUntil() );
        }
        if( $filtre->getPasInscrit() or $filtre->getInscrit() or  $filtre->getOrganisateur() )
        {
            $query = $query->setParameter('user', $user->getId());
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
