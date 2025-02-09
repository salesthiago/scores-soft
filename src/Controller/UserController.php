<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class UserController extends AbstractController
{
    private $entity;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->entity = $manager;
    }
    #[Route('/users', name: 'app_user')]
    public function index(): Response
    {
        $users = $this->entity->getRepository(User::class)->findAll();
        return $this->render('user/index.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/search-user-by-phone/{phone}', name: 'app_user.search')]
    public function searchByPhone($phone): Response
    {
        $user = $this->entity->getRepository(User::class)->findOneBy(['phone' => $phone]);
        return $this->json($user ? $user->toArray() : null);
    }
    #[Route('/user/create', name: 'app_user.create')]
    public function create(UserPasswordHasherInterface $passwordHash, Request $request): Response
    {
        $user = [
            'id' => null,
            'name' => '',
            'phone' => '',
            'password' => '',
            'roles' => 'ROLE_USER'
        ];
        if ($request->isMethod("post")) {
            $data = $request->getPayload()->all();
            $user = new User();
            $user->setPhone($data['phone']);
            $user->setName($data['name']);
            $user->setPassword($passwordHash->hashPassword($user, $data['password']));
            $user->setRoles([$data['roles']]);

            $this->entity->persist($user);
            $this->entity->flush();

            $this->addFlash('success', 'Registro Alterado com sucesso');
            return $this->redirectToRoute('app_user');
        }

        return $this->render('user/form.html.twig', [
            'user' => $user
        ]);
    }
    #[Route('/user/{id}/update', name: 'app_user.update')]
    public function update(
        #[MapEntity(id: 'id')] User $user,
        UserPasswordHasherInterface $passwordHash,
        Request $request
    ): Response
    {
        if ($request->isMethod('post')) {
            $data = $request->getPayload()->all();

            $user->setName($data['name']);
            $user->setPhone($data['phone']);
            $user->setRoles([$data['roles']]);
            $user->setStatus((int)$data['status']);

            if (!empty($data['password'])) {
                $user->setPassword($passwordHash->hashPassword($user, $data['password']));
            }
            $this->entity->persist($user);
            $this->entity->flush();

            $this->addFlash('success', 'Registro Alterado com sucesso');
            return $this->redirectToRoute('app_user');
        }

        return $this->render('user/form.html.twig', [
            'user' => $user->toArray(),
        ]);
    }
}
