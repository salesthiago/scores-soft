<?php

namespace App\Repository;

use App\Entity\Rule;
use App\Entity\Transactions;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Service\ScoringService;
/**
 * @extends ServiceEntityRepository<Transactions>
 */
class TransactionsRepository extends ServiceEntityRepository
{

    private ScoringService $service;
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transactions::class);
    }

    public function getCreator($created_by)
    {
        return $this->getDoctrine()->getRepository(User::class)->find($created_by)->toArray();
    }

    public function create(array $data): array
    {
        $transaction = new Transactions();
        $user = $this->getDoctrine()->getRepository(User::class)->find($data['user_id']);
        $creator = $this->getDoctrine()->getRepository(User::class)->find($data['created_by']);
        
        $transaction->setUser($user);
        $transaction->setCreator($creator);
        $transaction->setCreatedAt(date('Y-m-d H:i:s'));
        
        $transaction->setAmount($data['amount']);
        if (!empty($data['number_amount'])) {
            $transaction->setOrderNumber($data['number_amount']);
        }
        $this->getDoctrine()->persist($transaction);
        $this->getDoctrine()->flush();
        
        return $transaction->toArray();
    }

    //    /**
    //     * @return Transactions[] Returns an array of Transactions objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('t.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Transactions
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
