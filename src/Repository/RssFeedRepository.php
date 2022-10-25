<?php

namespace App\Repository;

use App\Entity\RssFeed;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

class RssFeedRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RssFeed::class);
    }

    public function getFeedCountForUser(UserInterface $user)
    {
        $query = $this->getEntityManager()->createQuery('SELECT COUNT(rf) FROM ' . RssFeed::class . ' rf WHERE rf.user = ?1');
        $query->setParameter(1, $user);

        return $query->getSingleScalarResult();
    }

    /*
    public function findBySomething($value)
    {
        return $this->createQueryBuilder('r')
            ->where('r.something = :value')->setParameter('value', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */
}
