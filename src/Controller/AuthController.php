<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

final class AuthController extends AbstractController
{
    private $entity;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entity = $entityManager;
    }

    #[Route('/login', name: 'app_login', methods: ['GET', 'POST'])]
    public function login(AuthenticationUtils $authenticationUtils): Response {
        $user = $this->getUser();
        if ($user) {
           return $this->redirectToRoute('app_home');
        }
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        if ($error) {
            $this->addFlash('error', $error->getMessage());
        }
        return $this->render('auth/login.html.twig', [
            'last_username' => $lastUsername
        ]);
    }

    #[Route('/register', name: 'app_register', methods: ['GET', 'POST'])]
    public function register(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordEncoder
    ): Response {
        if ($request->isMethod('POST')) {
            $name = $request->request->get('name');
            $phone = preg_replace('/\D/', '', $request->request->get('phone'));
            $password = $request->request->get('password');
            $confirmPassword = $request->request->get('confirm-password');

            if (empty($password) || empty($confirmPassword)) {
                return $this->redirectToRoute('app_register', ['error' => 'Informe e confirme a senha']);
            }

            if ($password !== $confirmPassword) {
                $this->addFlash('error', 'As senhas não conferem');
                return $this->redirectToRoute('app_register');
            }

            if (!$this->validatePhone($phone)) {
                $this->addFlash('error', 'Este número já está cadastrado.');
                return $this->redirectToRoute('app_register');
            }
            $user = new User();
            $user->setName($name);
            $user->setPhone($phone);
            $user->setPassword($passwordEncoder->hashPassword($user, $password));
            $user->setRoles(['ROLE_USER']);
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_login', ['message' => 'Usuário cadastrado com sucesso!']);
        }

        return $this->render('auth/register.html.twig');
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        // será interceptado pelo firewall
    }

    private function validatePhone($phone)
    {
        $user = $this->entity->getRepository(User::class)->findOneBy(['phone' => $phone]);
        if ($user) {
            return false;
        }
        return true;
    }
}
