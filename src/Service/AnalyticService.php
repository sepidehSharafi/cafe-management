<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;

class AnalyticService
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getSalesOverTime(\DateTime $startDate, \DateTime $endDate): array
    {
        $qb = $this->entityManager->createQueryBuilder();
        $result = $qb->select('oi.createdAt as date, COUNT(oi.id) as orderCount, SUM(oi.quantity * oi.price) as totalSales')
            ->from('App\Entity\OrderItem', 'oi')
            ->join('oi.purchase', 'o')
            ->where('oi.createdAt BETWEEN :startDate AND :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->groupBy('oi.createdAt')
            ->orderBy('oi.createdAt', 'ASC')
            ->getQuery()
            ->getResult();
    
        return $result;
    }
}
