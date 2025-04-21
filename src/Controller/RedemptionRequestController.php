<?php

namespace App\Controller;

use App\Entity\RedemptionRequest;
use App\Entity\Reward;
use App\Service\RedemptionService;
use App\Service\ScoringService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
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
        $user = $this->getUser();
        $filter =  ['status' => $request->query->get('status', 'pending')];
        // SE NAO FOR ADMIN, FILTRA PELO USUARIO PARA VER SOMENTE AS SUAS REQUISIÇÕES.
        $isAdmin = array_search('ROLE_ADMIN', $user->getRoles(), true) ? true : false;
        if (!$isAdmin) {
            $filter['user'] = $user;
        }
        $redemptions = $this->entity->getRepository(RedemptionRequest::class)
            ->findBy($filter);
        $items = [];

        foreach ($redemptions as $item) {
            $items[] = $item->toArray();
        }
        return $this->render('redemption_request/index.html.twig', [
            'items' => $items,
        ]);
    }

    #[Route('/redemption/request/create', name: 'app_redemption_request.create')]
    public function create(RedemptionService $redemptionService, Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $user = $this->getUser();
            $data = $request->getPayload()->all();
            $rewardFounded = $this->getRewardById($data['reward_id']);
            if (!$rewardFounded) {
                $this->addFlash('error', 'Recompensa não encontrada');
                return $this->redirectToRoute('app_redemption_request.create');
            }
            
            try {

                $redemptionService->createRedemptionRequest($user, $rewardFounded);
                $this->addFlash('success', 'Requisição solicitada com sucesso');
                return $this->redirectToRoute('app_redemption_request');
            } catch (Exception $e) {
                $this->addFlash('error', $e->getMessage());
                return $this->redirectToRoute('app_show_reward', ['id' => $data['reward_id']]);
            }
        }
        return $this->redirectToRoute('app_rewards');
    }

    #[Route('/redemption/request/update/{id}', name: 'app_redemption_request.update')]
    public function update(
        #[MapEntity(id: 'id')] RedemptionRequest $redemption,
        RedemptionService $redemptionService,
        Request $request
    ): Response
    {
        if ($request->isMethod('POST')) {
            $data = $request->getPayload()->all();
            /*$rewardFounded = $this->getRewardById($data['reward_id']);
            if (!$rewardFounded) {
                $this->addFlash('error', 'Recompensa não encontrada');
                return $this->redirectToRoute('app_redemption_request.create');
            }*/
            $status = '';
            
            switch ($data['status']) {
                case 'pending':
                    $status = 'Pendente';
                break;
                case 'approved':
                    $status = 'Aprovado';
                    //$redemption->setStatus($data['status']);
                    $redemptionService->processRedemptionRequest($redemption, $data['status']);
                    break;
                case 'rejected':
                    $status = 'Rejeitado';
                break;
                default:
                    $status = $data['status'];
                break;
            }
            // $this->entity->persist($redemption);
            // $this->entity->flush();

            $this->addFlash('success', 'Requisição atualizada para '. $status);
            return $this->redirectToRoute('app_redemption_request');
        }
        // return $this->render('redemption_request/.html.twig');
        return $this->redirectToRoute('app_rewards');
    }

    #[Route('/redemption/request/destroy/{id}', name: 'app_redemption_request.destroy', methods: ['DELETE', 'POST'])]
    public function destroy(#[MapEntity(id: 'id')] RedemptionRequest $redemption, Request $request)
    {
        $id = $redemption->getId();
        if ($this->isCsrfTokenValid('delete' . $id, $request->request->get('_token'))) {
            $user = $this->getUser()->toArray();
            
            if (array_search('ROLE_USER', $user['roles'], true) && $user['id'] !== $redemption->getUser()->getId()) {
                $this->addFlash('error', 'Somente o usuario que solicitou pode remover esta requisição.');
                return $this->redirectToRoute('app_redemption_request');
            }

            $this->entity->remove($redemption);
            $this->entity->flush();

            return $this->redirectToRoute('app_redemption_request');
        }
        throw new Exception('Token inválido');
    }

    private function getRewardById($id)
    {
        return $this->entity->getRepository(Reward::class)->find($id);
    }

}
