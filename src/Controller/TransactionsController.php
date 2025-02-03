<?php

namespace App\Controller;

use App\Entity\Transactions;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;

final class TransactionsController extends AbstractController
{
    private $entity;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->entity = $manager;
    }

    #[Route('/transactions', name: 'app_transactions')]
    public function index(): Response
    {
        return $this->render('transactions/index.html.twig', [
            'controller_name' => 'TransactionsController',
        ]);
    }
    #[Route('/transactions/new', name: 'app_transactions.create')]
    public function create(Request $request): Response
    {
        if ($request->isMethod('post')) {
            $data = $request->getPayload()->all();
            $transaction = new Transactions($data);

            $this->entity->persist($transaction);
            $this->entity->flush();
            $this->addFlash('success', 'Adicionado com sucesso');
            return $this->redirectToRoute('app_transactions.create');
        }
        return $this->render('transactions/create.html.twig', []);
    }
}
