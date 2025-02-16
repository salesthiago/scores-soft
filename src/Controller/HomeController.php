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
use Exception;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;

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
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
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
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
        $history = $scoringService->getHistory($user);

        return $this->render('home/my-history.html.twig', [
            'history' => $history,
        ]);
    }

    #[Route('/rewards', name: 'app_rewards')]
    public function rewards(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
        $items = $entityManager->getRepository(Reward::class)->findBy(['status' => 'enabled']);
        $rewards = [];
        foreach($items as $item) {
            $a = $item->toArray();
            $a['description'] = substr($a['description'], 0, 20) .' ...';
            $rewards[] = $a;
        }

        return $this->render('home/rewards.html.twig', [
            'rewards' => $rewards,
        ]);
    }
    
    #[Route('/reward/{id}', name: 'app_show_reward')]
    public function requestReward(#[MapEntity(id: 'id')] Reward $reward, ScoringService $scoringService): Response
    {
        $user = $this->getUser();
        $balance = $scoringService->getUserBalance($user);
        return $this->render('home/request.html.twig', [
            'reward' => $reward->toArray(),
            'balance' => $balance
        ]);
    }

    #[Route('/profile', name: 'app_profile', methods: ['POST'])]
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
        $user = $entityManager->getRepository(User::class)->find($userLogged->getId());
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

    #[Route('/logout', name: 'app_logout')]
    public function logout(Security $security)
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
        try {
            $security->logout();
            $this->redirectToRoute('app_login');
        } catch(Exception $e) {
            echo $e->getMessage();
        }
    }
}
