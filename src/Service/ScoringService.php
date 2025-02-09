<?php
namespace App\Service;

use App\Entity\Rule;
use App\Entity\Transactions;
use App\Entity\UserBalance;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class ScoringService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function calculatePoints(User $user, float $transactionValue): array
    {
        $userBalance = $this->getUserBalance($user);
        $totalValue = $transactionValue + $userBalance->getRemainingValue();

        $rules = $this->entityManager->getRepository(Rule::class)
            ->findBy([], ['amount_min' => 'ASC']);

        $pointsEarned = 0;
        $remainingValue = $totalValue;

        foreach ($rules as $rule) {
            while ($remainingValue >= $rule->getAmount_min() &&
                   $remainingValue >= $rule->getAmount_max()) {
                $pointsEarned += $rule->getScore();
                $remainingValue -= $rule->getAmount_max();
            }
        }

        // Atualiza o saldo do usuário
        $userBalance->addPoints($pointsEarned);
        $userBalance->setRemainingValue($remainingValue);

        $this->entityManager->persist($userBalance);
        $this->entityManager->flush();

        return [
            'pointsEarned' => $pointsEarned,
            'remainingValue' => $remainingValue,
            'totalPoints' => $userBalance->getPoints()
        ];
    }

    public function getUserBalance(User $user): UserBalance
    {
        $userBalance = $this->entityManager->getRepository(UserBalance::class)
            ->findOneBy(['user' => $user]);

        if (!$userBalance) {
            $userBalance = new UserBalance();
            $userBalance->setUser($user);
        }

        return $userBalance;
    }

    public function getHistory(User $user): array
    {
        $transactions = $this->entityManager
            ->getRepository(Transactions::class)
            ->findBy(['user' => $user]);

        $history = [];
        foreach ($transactions as $item) {
            $history[] = $item->toArray();
        }
        return $history;
    }
}