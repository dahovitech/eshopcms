<?php

namespace App\Controller\Admin;

use App\Entity\Attribute;
use App\Entity\AttributeTranslation;
use App\Form\AttributeType;
use App\Repository\AttributeRepository;
use App\Repository\LanguageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/attribute', name: 'admin_attribute_')]
#[IsGranted('ROLE_ADMIN')]
class AttributeController extends AbstractController
{
    public function __construct(
        private AttributeRepository $attributeRepository,
        private LanguageRepository $languageRepository,
        private EntityManagerInterface $entityManager
    ) {}

    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        $attributes = $this->attributeRepository->findBy([], ['sortOrder' => 'ASC', 'code' => 'ASC']);
        $languages = $this->languageRepository->findActiveLanguages();
        
        // Calcul des statistiques
        $statistics = [
            'totalAttributes' => count($attributes),
            'activeAttributes' => count(array_filter($attributes, fn($a) => $a->isActive())),
            'variantAttributes' => count(array_filter($attributes, fn($a) => $a->isVariant())),
            'filterableAttributes' => count(array_filter($attributes, fn($a) => $a->isFilterable()))
        ];

        return $this->render('admin/attribute/index.html.twig', [
            'attributes' => $attributes,
            'languages' => $languages,
            'statistics' => $statistics
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $attribute = new Attribute();
        $languages = $this->languageRepository->findActiveLanguages();
        
        // Initialize translations for all active languages
        foreach ($languages as $language) {
            $translation = new AttributeTranslation();
            $translation->setLanguage($language);
            $attribute->addTranslation($translation);
        }

        $form = $this->createForm(AttributeType::class, $attribute);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Parse JSON configuration if provided
                $configData = $form->get('configuration')->getData();
                if ($configData) {
                    $config = json_decode($configData, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $attribute->setConfiguration($config);
                    } else {
                        throw new \InvalidArgumentException('Configuration JSON invalide');
                    }
                }

                $this->entityManager->persist($attribute);
                $this->entityManager->flush();

                $this->addFlash('success', 'Attribut créé avec succès.');
                return $this->redirectToRoute('admin_attribute_show', ['id' => $attribute->getId()]);

            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de la création : ' . $e->getMessage());
            }
        }

        return $this->render('admin/attribute/new.html.twig', [
            'attribute' => $attribute,
            'form' => $form->createView(),
            'languages' => $languages
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(Attribute $attribute): Response
    {
        $languages = $this->languageRepository->findActiveLanguages();
        
        return $this->render('admin/attribute/show.html.twig', [
            'attribute' => $attribute,
            'languages' => $languages
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function edit(Request $request, Attribute $attribute): Response
    {
        $languages = $this->languageRepository->findActiveLanguages();
        
        // Ensure translations exist for all active languages
        foreach ($languages as $language) {
            if (!$attribute->hasTranslation($language->getCode())) {
                $translation = new AttributeTranslation();
                $translation->setLanguage($language);
                $attribute->addTranslation($translation);
            }
        }

        // Convert configuration array to JSON for form
        $configJson = $attribute->getConfiguration() ? json_encode($attribute->getConfiguration(), JSON_PRETTY_PRINT) : '';
        
        $form = $this->createForm(AttributeType::class, $attribute);
        $form->get('configuration')->setData($configJson);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Parse JSON configuration if provided
                $configData = $form->get('configuration')->getData();
                if ($configData) {
                    $config = json_decode($configData, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $attribute->setConfiguration($config);
                    } else {
                        throw new \InvalidArgumentException('Configuration JSON invalide');
                    }
                } else {
                    $attribute->setConfiguration(null);
                }

                $this->entityManager->flush();

                $this->addFlash('success', 'Attribut modifié avec succès.');
                return $this->redirectToRoute('admin_attribute_show', ['id' => $attribute->getId()]);

            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de la modification : ' . $e->getMessage());
            }
        }

        return $this->render('admin/attribute/edit.html.twig', [
            'attribute' => $attribute,
            'form' => $form->createView(),
            'languages' => $languages
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(Request $request, Attribute $attribute): Response
    {
        if ($this->isCsrfTokenValid('delete'.$attribute->getId(), $request->request->get('_token'))) {
            try {
                $this->entityManager->remove($attribute);
                $this->entityManager->flush();
                $this->addFlash('success', 'Attribut supprimé avec succès.');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de la suppression : ' . $e->getMessage());
            }
        }

        return $this->redirectToRoute('admin_attribute_index');
    }
}
