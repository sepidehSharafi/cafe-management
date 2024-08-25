<?php

namespace App\Repository;

use App\Entity\Comment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Comment>
 */
class CommentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comment::class);
    }

    public function findByItem($item)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.item = :item')
            ->setParameter('item', $item)
            ->orderBy('c.id', 'DESC')
            ->getQuery()
            ->getResult();
    }
}