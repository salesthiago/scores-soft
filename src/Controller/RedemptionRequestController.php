<?php

namespace App\Controller;

use App\Entity\RedemptionRequest;
use App\Entity\Reward;
use App\Service\ScoringService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;

final class RedemptionRequestController extends AbstractController
{
    private $entity;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entity = $entityManager;
    }

    #[Route('/redemption/request', name: 'app_redemption_request')]
    public function index(Request $request): Response
    {
        $redemptions = $this->entity->getRepository(RedemptionRequest::class)
            ->findBy(['status' => $request->query->get('status', 'pending')]);
        $items = [];

        foreach ($redemptions as $item) {
            $items[] = $item->toArray();
        }
        return $this->render('redemption_request/index.html.twig', [
            'items' => $items,
        ]);
    }

    #[Route('/redemption/request/create', name: 'app_redemption_request.create')]
    public function create(ScoringService $scoringService, Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $user = $this->getUser();
            $data = $request->getPayload()->all();
            $rewardFounded = $this->getRewardById($data['reward_id']);
            if (!$rewardFounded) {
                $this->addFlash('error', 'Recompensa não encontrada');
                return $this->redirectToRoute('app_redemption_request.create');
            }
            $balanceUser = $scoringService->getUserBalance($user)->toArray();
            $points = $balanceUser['points'];
            $minimalPoints = $rewardFounded->getPointsCost();

            if ($points < $minimalPoints) {
                $this->addFlash('error', 'Desculpe, Você não possui saldo para esta recompensa. Seu saldo é de '.$points. ' pontos');
                return $this->redirectToRoute('app_show_reward');
            }
            $redemption = new RedemptionRequest();
            $redemption->setStatus('pending');
            $redemption->setRequestDate(new DateTime('now'));
            $redemption->setReward($rewardFounded);
            $redemption->setUser($user);

            $this->entity->persist($redemption);
            $this->entity->flush();

            $this->addFlash('success', 'Requisição solicitada com sucesso');
            return $this->redirectToRoute('app_redemption_request');
        }
        return $this->render('redemption_request/form.html.twig');
    }

    #[Route('/redemption/request/update/{id}', name: 'app_redemption_request.update')]
    public function update(#[MapEntity(id: 'id')] RedemptionRequest $redemption, Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $data = $request->getPayload()->all();
            $rewardFounded = $this->getRewardById($data['reward_id']);
            if (!$rewardFounded) {
                $this->addFlash('error', 'Recompensa não encontrada');
                return $this->redirectToRoute('app_redemption_request.create');
            }
            $status = '';
            $redemption->setStatus($data['status']);
            $this->entity->persist($redemption);
            $this->entity->flush();
            switch ($data['status']) {
                case 'pending':
                    $status = 'Pendente';
                break;
                case 'approved':
                    $status = 'Aprovado';
                    break;
                case 'rejected':
                    $status = 'Rejeitado';
                break;
                default:
                    $status = $data['status'];
                break;
            }
            $this->addFlash('success', 'Requisição atualizada para '. $status);
            return $this->redirectToRoute('app_redemption_request');
        }
        return $this->render('redemption_request/form.html.twig');
    }

    #[Route('/redemption/request/destroy/{id}', name: 'app_redemption_request.destroy', methods: ['DELETE'])]
    public function destroy(#[MapEntity(id: 'id')] RedemptionRequest $redemption)
    {
        $user = $this->getUser();
        if ($user !== $redemption->getUser()) {
            $this->addFlash('error', 'Somente o usuario que solicitou pode remover esta requisição.');
            return $this->redirectToRoute('app_redemption_request');
        }

        $this->entity->remove($redemption);
        $this->entity->flush();

        return $this->redirectToRoute('app_redemption_request');
    }

    private function getRewardById($id)
    {
        return $this->entity->getRepository(Reward::class)->find($id);
    }

}
