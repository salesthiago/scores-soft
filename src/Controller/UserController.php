<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class UserController extends AbstractController
{
    private $entity;
    private $logger;

    public function __construct(EntityManagerInterface $manager, LoggerInterface $logger)
    {
        $this->entity = $manager;
        $this->logger = $logger;
    }
    #[Route('/users', name: 'app_user')]
    public function index(Request $request): Response
    {
        $page = $request->query->get('page', 1);
        $rowsPerPage = $request->query->get('rowsPerPage', 10);
        $search = $request->query->get('search', null);

        try {

            $offset = ($page - 1) * $rowsPerPage;

            $repository = $this->entity->getRepository(User::class);
            $queryBuilder = $repository->createQueryBuilder('u')
                ->where('u.status = 1')
                ->orderBy('u.id', 'DESC');

            // Adicionando condições de busca se houver um termo de pesquisa
            if ($search) {
                $queryBuilder->andWhere(
                    $queryBuilder->expr()->orX(
                        $queryBuilder->expr()->like('u.name', ':search'),
                        $queryBuilder->expr()->like('u.phone', ':search')
                    )
                )
                ->setParameter('search', '%' . $search . '%');
            }

            // Executando a consulta com paginação
            $query = $queryBuilder->getQuery()
                ->setFirstResult($offset)
                ->setMaxResults($rowsPerPage);

            $users = $query->getResult();

            // Contagem total de registros para paginação (considerando a busca)
            $countQueryBuilder = clone $queryBuilder;
            $countQueryBuilder->select('COUNT(u.id)')
                ->resetDQLPart('orderBy');
            $totalUsers = $countQueryBuilder->getQuery()->getSingleScalarResult();

            $totalPages = ceil($totalUsers / $rowsPerPage);

            $this->logger->info('Consulta de usuários realizada com sucesso', [
                'count' => count($users),
                'totalUsers' => $totalUsers,
                'page' => $page,
                'totalPages' => $totalPages,
                'search' => $search
            ]);

            return $this->render('user/index.html.twig', [
                'users' => $users,
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'rowsPerPage' => $rowsPerPage,
                'totalUsers' => $totalUsers,
                'search' => $search
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Erro ao buscar usuários', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'search' => $search
            ]);

            // Retorna uma resposta amigável de erro
            return $this->render('error/custom_error.html.twig', [
                'error_message' => 'Ocorreu um erro ao carregar a lista de usuários. Por favor, tente novamente mais tarde.',
                'debug' => $this->getParameter('kernel.debug') ? $e->getMessage() : null
            ]);
        }
    }

    #[Route('/search-user-by-phone/{phone}', name: 'app_user.search')]
    public function searchByPhone($phone): Response
    {
        $user = $this->entity->getRepository(User::class)->findOneBy(['phone' => $phone]);
        return $this->json($user ? $user->toArray() : null);
    }
    #[Route('/users/create', name: 'app_user.create')]
    public function create(UserPasswordHasherInterface $passwordHash, Request $request): Response
    {
        $user = [
            'id' => null,
            'name' => '',
            'phone' => '',
            'password' => '',
            'status' => '1',
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
    ): Response {
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
    #[Route('/user/{id}/destroy', name: 'app_user.destroy')]
    public function destroy(#[MapEntity(id: 'id')] User $user): Response {

        $user->setStatus(0);

        $this->entity->persist($user);
        $this->entity->flush();

        $this->addFlash('success', 'Usuário desativado com sucesso !!');
        return $this->redirectToRoute('app_user');
    }
}
