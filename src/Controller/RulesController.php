<?php

namespace App\Controller;

use App\Entity\Rule;
use App\Entity\Settings;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;

final class RulesController extends AbstractController
{
    private $entity;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entity = $entityManager;
    }
    #[Route('/rules', name: 'app_rules')]
    public function index(): Response
    {
        $rules = $this->entity->getRepository(Rule::class)->findAll();
        return $this->render('rules/index.html.twig', [
            'rules' => $rules
        ]);
    }
    #[Route('/rules/new', name: 'app_rules.create')]
    public function create(Request $request): Response
    {
        $rule = ['amount_min' => 0, 'amount_max' => 0, 'score' => 0, 'id' => null];
        if ($request->isMethod('POST')) {
            $data = $request->getPayload()->all();
            if (empty($data['amount_min']) || empty($data['amount_max']) || empty($data['score'])) {
                return $this->render('rules/form.html.twig', [
                    'rule' => $data
                ]);
            }
            $rule = new Rule($data);
            
            $this->entity->persist($rule);
            $this->entity->flush();

            $this->addFlash('success', 'Gravado com sucesso!');
            return $this->redirectToRoute('app_rules.update', [
                'id' => $rule->getId()
            ]);
        }

        return $this->render('rules/form.html.twig', [
            'rule' => $rule
        ]);

    }
    #[Route('/rules/update/{id}', name: 'app_rules.update')]
    public function update(#[MapEntity(id: 'id')] Rule $rule, Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $data = $request->getPayload()->all();
            if (empty($data['amount_min']) || empty($data['amount_max']) || empty($data['score'])) {
                $this->addFlash('success', 'Gravado com sucesso!');
                return $this->redirectToRoute('app_rules.update', [
                    'rule' => $rule
                ]);
            }
            $rule = new Rule($data);
            $this->entity->persist($rule);
            $this->entity->flush();
        }
        return $this->render('rules/form.html.twig', [
            'rule' => $rule->toArray()
        ]);
    }
    #[Route('/rules/destroy/{id}', name: 'app_rules.destroy', methods: ['delete'])]
    public function destroy(#[MapEntity(id: 'id')] Rule $rule, Request $request): Response
    {

        $this->entity->remove($rule);
        $this->entity->flush();
        
        $this->addFlash('success', 'Deletetado com sucesso!');
        return $this->redirectToRoute('app_rules');
    }

    #[Route('/rules-settings', name: 'app_rules.settings')]
    public function settings(Request $request): Response
    {
        $limit = $this->entity->getRepository(Settings::class)->findOneBy(['name' => 'max_buys']);
        if ($request->isMethod('POST')) {
            $data = $request->getPayload()->all();
            if (empty($data['max_buys'])) {
                $this->addFlash('error', 'Informe o limite de compras');
                return $this->render('rules/settings.html.twig', [
                    'limit' => $limit ? $limit->getValue() : 0
                ]);
            }
            if (!$limit) {
                $setting = new Settings();
                $setting->setName('max_buys');
            }
            
            $setting->setValue($data['max_buys']);
            $this->entity->persist($setting);
            $this->entity->flush();

            $this->addFlash('success', 'Atualizado com sucesso!');
            return $this->redirectToRoute('app_rules');
        }

        return $this->render('rules/settings.html.twig', [
            'limit' => $limit ? $limit->getValue() : 0
        ]);

    }
}
