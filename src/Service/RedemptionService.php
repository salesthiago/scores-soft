<?php
namespace App\Service;

use App\Entity\User;
use App\Entity\UserBalance;
use App\Entity\RedemptionRequest;
use App\Entity\Reward;
use App\Exception\InsufficientPointsException;
use App\Exception\InvalidRewardException;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

class RedemptionService
{
    private EntityManagerInterface $entityManager;
    private ScoringService $scoringService;

    public function __construct(
        EntityManagerInterface $entityManager,
        ScoringService $scoringService
    ) {
        $this->entityManager = $entityManager;
        $this->scoringService = $scoringService;
    }

    /**
     * Processa uma solicitação de resgate de pontos
     * 
     * @param User $user
     * @param Reward $reward
     * @return RedemptionRequest
     */
    public function createRedemptionRequest(User $user, Reward $reward): RedemptionRequest
    {
        // Verifica se o prêmio está ativo
        if (!$reward->isActive()) {
            throw new Exception('Este prêmio não está disponível para resgate.');
        }

        // Obtém o saldo atual do usuário
        $userBalance = $this->scoringService->getUserBalance($user);

        // Verifica se o usuário tem pontos suficientes
        if ($userBalance->getPoints() < $reward->getPointsCost()) {
            throw new Exception(
                'Pontos insuficientes para este resgate.'
            );
        }

        // Cria a solicitação de resgate
        $redemption = new RedemptionRequest();
        $redemption->setUser($user);
        $redemption->setReward($reward);
        $redemption->setPointsCost($reward->getPointsCost());
        $redemption->setStatus('pending');
        $redemption->setRequestDate(new \DateTime());

        // Reserva os pontos (opcional - depende do requisito de negócio)
        $userBalance->reservePoints($reward->getPointsCost());

        $this->entityManager->persist($redemption);
        $this->entityManager->persist($userBalance);
        $this->entityManager->flush();

        return $redemption;
    }

    /**
     * Processa a aprovação de uma solicitação de resgate
     * 
     * @param RedemptionRequest $redemption
     * @param string $status ('approved' ou 'rejected')
     * @return RedemptionRequest
     */
    public function processRedemptionRequest(RedemptionRequest $redemption, string $status): RedemptionRequest
    {
        $userBalance = $this->scoringService->getUserBalance($redemption->getUser());

        if ($status === 'approved') {
            // Deduz os pontos do saldo do usuário
            $userBalance->deductPoints($redemption->getPointsCost());
            $redemption->setStatus('approved');
            $redemption->setProcessedDate(new \DateTime());
        } else {
            // Devolve os pontos reservados
            $userBalance->unreservePoints($redemption->getPointsCost());
            $redemption->setStatus('rejected');
            $redemption->setProcessedDate(new \DateTime());
        }

        $this->entityManager->persist($redemption);
        $this->entityManager->persist($userBalance);
        $this->entityManager->flush();

        return $redemption;
    }

    /**
     * Obtém o histórico de resgates do usuário
     * 
     * @param User $user
     * @return array
     */
    public function getRedemptionHistory(User $user): array
    {
        return $this->entityManager
            ->getRepository(RedemptionRequest::class)
            ->findBy(
                ['user' => $user],
                ['requestDate' => 'DESC']
            );
    }
}