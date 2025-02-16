<?php

namespace App\Controller;

use App\Entity\Reward;
use App\Service\FileUploader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Log\Logger;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;

final class RewardController extends AbstractController
{
    private $entity;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entity = $entityManager;
    }

    #[Route('/admin/rewards', name: 'app_admin_reward')]
    public function index(): Response
    {
        $rewards = $this->entity->getRepository(Reward::class)->findAll();

        $response = [];
        foreach($rewards as $item) {
            $response[] = $item->toArray();
        }
        return $this->render('reward/index.html.twig', [
            'rewards' => $response,
        ]);
    }

    #[Route('/admin/reward/create', name: 'app_admin_reward.create')]
    public function create(
        FileUploader $fileUploader,
        Request $request,
    ): Response
    {
        if ($request->isMethod("POST")) {
            $image = $request->files->get('image', null);
            $data = $request->getPayload()->all();
            $reward = new Reward();
            $reward->setName($data['name']);
            $reward->setDescription($data['description']);
            $reward->setStatus($data['status']);
            $reward->setPointsCost($data['pointsCost']);
            if ($image) {
                $path = $fileUploader->upload($image);
                $reward->setImage($path);
            }
            $this->entity->persist($reward);
            $this->entity->flush();
            $this->addFlash('success', 'Recompensa atualizada com sucesso!!');
            return $this->redirectToRoute('app_admin_reward');
        }

        return $this->render('reward/form.html.twig', []);

    }
    #[Route('/admin/reward/update/{id}', name: 'app_admin_reward.update')]
    public function update(
        #[MapEntity(id: 'id')] Reward $reward,
        FileUploader $fileUploader,
        Request $request,
    ): Response
    {
        if ($request->isMethod("POST")) {
            $data = $request->getPayload()->all();
            $image = $request->files->get('image', null);

            if (!empty($data['name'])) {
                $reward->setName($data['name']);
            }
            if (!empty($data['description'])) {
                $reward->setDescription($data['description']);
            }
            if (!empty($data['pointsCost'])) {
                $reward->setPointsCost($data['pointsCost']);
            }

            $reward->setStatus($data['status']);
            
            if ($image) {
                $path = $fileUploader->upload($image);
                $reward->setImage($path);
            }
            $this->entity->persist($reward);
            $this->entity->flush();
            $this->addFlash('success', 'Recompensa Adicionada com sucesso!!');
            return $this->redirectToRoute('app_admin_reward');
        }

        return $this->render('reward/form.html.twig', ['reward' => $reward->toArray()]);
    }

    #[Route('/admin/reward/{id}', name: 'app_admin_reward.destroy', methods: ['delete'])]
    public function destroy(#[MapEntity(id: 'id')] Reward $reward)
    {
        $this->entity->remove($reward);
        $this->entity->flush();
        $this->addFlash('success', 'Recompensa deletada com sucesso!!');
        return $this->redirectToRoute('app_admin_reward');
    }
}