<?php

namespace App\Controller;

use App\Entity\Reward;
use App\Entity\User;
use App\Entity\UserBalance;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Log\Logger;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\ScoringService;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\Request;

final class HomeController extends AbstractController
{
    private $entity;
    public function __construct(EntityManagerInterface $manager)
    {
        $this->entity = $manager;
    }
    #[Route('/home', name: 'app_home')]
    public function index(AuthorizationCheckerInterface $authChecker): Response
    {
        
        /*
        if (!$authChecker->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('app_login');
        }*/
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }
    #[Route('/scores', name: 'app_my_scores')]
    public function myScore(ScoringService $scoringService): Response
    {
        $user = $this->getUser();
        $scores = $scoringService->getUserBalance($user)->toArray();
        return $this->render('home/my-score.html.twig', [
            'scores' => $scores,
        ]);
    }

    #[Route('/history', name: 'app_my_history')]
    public function history(ScoringService $scoringService): Response
    {
        $user = $this->getUser();
        $history = $scoringService->getHistory($user);

        return $this->render('home/my-history.html.twig', [
            'history' => $history,
        ]);
    }
    
    #[Route('/rewards', name: 'app_rewards')]
    public function rewards(EntityManagerInterface $entityManager): Response
    {
        
        $items = $entityManager->getRepository(Reward::class)->findBy(['status' => 'enabled']);
        $rewards = [];
        foreach($items as $item) {
            $rewards[] = $item->toArray();
        }

        return $this->render('home/rewards.html.twig', [
            'rewards' => $rewards,
        ]);
    }
    
    #[Route('/profile', name: 'app_profile')]
    public function profile(
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordEncoder,
        Request $request
    ): Response
    {
        $data = $request->getPayload()->all();
        if (empty($data['name'])) {
            $this->addFlash('error', 'Informe um nome');
            return $this->redirectToRoute('app_home');
        }
        if (!empty($data['password']) && empty($data['confirmPassword'])) {
            $this->addFlash('error', 'Confirme a senha');
            return $this->redirectToRoute('app_home');
        }
        if (!empty($data['password']) && !empty($data['confirmPassword'])) {
            if ($data['password'] !== $data['confirmPassword']) {
                $this->addFlash('error', 'As senhas não conferem!!');
                return $this->redirectToRoute('app_home');
            }
        }
        $userLogged = $this->getUser();
        $user = $entityManager->getRepository(User::class)->find($userLogged->id);
        $user->setName($data['name']);
        if (!empty($data['password']) && !empty($data['confirmPassword'])) {
            if (!empty($data['password'])) {
                $user->setPassword($passwordEncoder->hashPassword(new User(), $data['password'] ));
            }
        }
        $entityManager->persist($user);
        $entityManager->flush();
        $this->addFlash('success', 'Dados do usuário atualizados com sucesso!!');
        return $this->redirectToRoute('app_home');
    }
}
