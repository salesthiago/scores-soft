<?php

namespace App\Controller;

use App\Entity\Transactions;
use App\Entity\User;
use App\Repository\TransactionsRepository;
use App\Service\ScoringService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
final class TransactionsController extends AbstractController
{
    private $entity, $repository;

    public function __construct(EntityManagerInterface $manager, TransactionsRepository $repository)
    {
        $this->entity = $manager;
        $this->repository = $repository;
    }

    #[Route('/transactions', name: 'app_transactions')]
    public function index(Request $request): Response
    {
        $filters = []; #$request->query->get('filters', []);
        $page = $request->query->get('page', 1);
        $perPage = $request->query->get('page', 15);

        $transactions = $this->find($filters, $page, $perPage);

        $response = [];
        foreach($transactions as $item) {
            $response[] = $item->toArray();

        }
        return $this->render('transactions/index.html.twig', [
            'transactions' => $response,
        ]);
    }
    #[Route('/transactions/new', name: 'app_transactions.create')]
    public function create(ScoringService $scoringService, Request $request): Response
    {
        if ($request->isMethod('post')) {
            $data = $request->getPayload()->all();

            if (empty($data['amount'])) {
                $this->addFlash('error', 'Informe o valor');
                return $this->redirectToRoute('app_transactions.create', ['transaction' => $data]);
            }
            if (empty($data['user_id'])) {
                $this->addFlash('error', 'Selecione um usuário cadastrado');
                return $this->redirectToRoute('app_transactions.create', ['transaction' => $data]);
            }

            $user = $this->entity->getRepository(User::class)->find($data['user_id']);
            $creator = $this->getUser();

            if (empty($user)) {
                $this->addFlash('error', 'Usuário inválido');
                return $this->redirectToRoute('app_transactions.create', ['transaction' => $data]);
            }
            try {

                $data['amount'] = (float)str_replace(',', '.', $data['amount']);
                $transaction = new Transactions();
                $transaction->setUser($user);
                $transaction->setCreator($creator);
                $transaction->setCreatedAt(date('Y-m-d H:i:s'));
                
                $transaction->setAmount($data['amount']);
                if (!empty($data['number_amount'])) {
                    $transaction->setOrderNumber($data['number_amount']);
                }
                $this->entity->persist($transaction);
                $this->entity->flush();
                $scoringService->calculatePoints($user, $data['amount']);
                $this->addFlash('success', 'Adicionado com sucesso');
                return $this->redirectToRoute('app_transactions');
            } catch (Exception $e) {
                $this->addFlash('error', $e->getMessage());
                return $this->redirectToRoute('app_transactions.create', ['transaction' => $data]);
            }
        }
        return $this->render('transactions/form.html.twig', []);
    }

    #[Route('/transactions/{id}/update', name: 'app_transactions.update')]
    public function update(#[MapEntity(id: 'id')] Transactions $transaction, Request $request): Response
    {
        if ($request->isMethod('post')) {
            $data = $request->getPayload()->all();

            /*
            $transaction->setAmount((float)$data['amount']);
            if (!empty($data['order_number'])) {
                $transaction->setOrderNumber($data['order_number']);
            }
            $this->entity->persist($transaction);
            $this->entity->flush();
            */
            $result = $this->repository->create($data);
            $this->addFlash('success', 'Registro Atualizado com sucesso');
            return $this->redirectToRoute('app_transactions');
        }
        return $this->render('transactions/create.html.twig', []);
    }

    #[Route('/transactions/{id}/destroy', name: 'app_transactions.destroy', methods: ['delete'])]
    public function destroy(#[MapEntity(id: 'id')] Transactions $transaction): Response
    {
        $this->entity->remove($transaction);
        $this->entity->flush();
        $this->addFlash('success', 'Registro Deletado com sucesso');
        return $this->redirectToRoute('app_transactions');
    }

    protected function find($filter, $page, $perPage)
    {
        /*
        return $this->entity->getRepository(Transactions::class)
            ->findBy($filter, null, $perPage, $page);
        */
        return $this->entity->getRepository(Transactions::class)->findAll();
    }
}
