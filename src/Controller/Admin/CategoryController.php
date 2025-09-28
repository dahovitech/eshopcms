<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Entity\CategoryTranslation;
use App\Repository\LanguageRepository;
use App\Repository\CategoryRepository;
use App\Repository\CategoryTranslationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/category', name: 'admin_category_')]
#[IsGranted('ROLE_ADMIN')]
class CategoryController extends AbstractController
{
    public function __construct(
        private CategoryRepository $categoryRepository,
        private LanguageRepository $languageRepository,
        private CategoryTranslationRepository $categoryTranslationRepository,
        private EntityManagerInterface $entityManager
    ) {}

    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        $categories = $this->categoryRepository->findAll();
        $languages = $this->languageRepository->findActiveLanguages();
        
        // Calcul des statistiques simples
        $statistics = [
            'totalCategories' => count($categories),
            'activeCategories' => count(array_filter($categories, fn($c) => $c->isActive())),
            'parentCategories' => count(array_filter($categories, fn($c) => $c->getParent() === null)),
            'childCategories' => count(array_filter($categories, fn($c) => $c->getParent() !== null))
        ];

        return $this->render('admin/category/index.html.twig', [
            'categories' => $categories,
            'languages' => $languages,
            'statistics' => $statistics
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $category = new Category();
        $languages = $this->languageRepository->findActiveLanguages();
        $defaultLanguage = $this->languageRepository->findDefaultLanguage();
        $parentCategories = $this->categoryRepository->findAll();

        if ($request->isMethod('POST')) {
            return $this->handleFormSubmission($request, $category, $languages, true);
        }

        return $this->render('admin/category/new.html.twig', [
            'category' => $category,
            'languages' => $languages,
            'defaultLanguage' => $defaultLanguage,
            'parentCategories' => $parentCategories
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(Category $category): Response
    {
        $languages = $this->languageRepository->findActiveLanguages();
        $translations = [];
        
        foreach ($languages as $language) {
            $translation = $this->categoryTranslationRepository->findOneBy([
                'category' => $category,
                'language' => $language
            ]);
            if ($translation) {
                $translations[$language->getCode()] = $translation;
            }
        }

        return $this->render('admin/category/show.html.twig', [
            'category' => $category,
            'translations' => $translations,
            'languages' => $languages
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function edit(Request $request, Category $category): Response
    {
        $languages = $this->languageRepository->findActiveLanguages();
        $defaultLanguage = $this->languageRepository->findDefaultLanguage();
        $parentCategories = $this->categoryRepository->findAll();

        if ($request->isMethod('POST')) {
            return $this->handleFormSubmission($request, $category, $languages, false);
        }

        // Preload existing translations
        $translations = [];
        foreach ($languages as $language) {
            $translation = $this->categoryTranslationRepository->findOneBy([
                'category' => $category,
                'language' => $language
            ]);
            if ($translation) {
                $translations[$language->getCode()] = $translation;
            }
        }

        return $this->render('admin/category/edit.html.twig', [
            'category' => $category,
            'languages' => $languages,
            'defaultLanguage' => $defaultLanguage,
            'parentCategories' => $parentCategories,
            'translations' => $translations
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(Request $request, Category $category): Response
    {
        if ($this->isCsrfTokenValid('delete'.$category->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($category);
            $this->entityManager->flush();
            $this->addFlash('success', 'Catégorie supprimée avec succès.');
        }

        return $this->redirectToRoute('admin_category_index');
    }

    #[Route('/{id}/toggle-active', name: 'toggle_active', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function toggleActive(Category $category): JsonResponse
    {
        $category->setIsActive(!$category->isActive());
        $this->entityManager->flush();

        return new JsonResponse([
            'success' => true,
            'isActive' => $category->isActive()
        ]);
    }

    private function handleFormSubmission(Request $request, Category $category, array $languages, bool $isNew): Response
    {
        $data = $request->request->all();
        
        try {
            // Update category basic data
            if (isset($data['sortOrder'])) {
                $category->setSortOrder((int)$data['sortOrder']);
            }
            if (isset($data['parentId'])) {
                $parent = $this->categoryRepository->find($data['parentId']);
                $category->setParent($parent);
            }
            
            $category->setIsActive(isset($data['isActive']));

            if ($isNew) {
                $this->entityManager->persist($category);
            }
            
            $this->entityManager->flush();

            // Handle translations
            foreach ($languages as $language) {
                $langCode = $language->getCode();
                if (isset($data['translations'][$langCode])) {
                    $translationData = $data['translations'][$langCode];
                    
                    if (!empty($translationData['name']) || !empty($translationData['description'])) {
                        $translation = $this->categoryTranslationRepository->findOneBy([
                            'category' => $category,
                            'language' => $language
                        ]);
                        
                        if (!$translation) {
                            $translation = new CategoryTranslation();
                            $translation->setCategory($category);
                            $translation->setLanguage($language);
                        }

                        if (!empty($translationData['name'])) {
                            $translation->setName($translationData['name']);
                        }
                        if (!empty($translationData['description'])) {
                            $translation->setDescription($translationData['description']);
                        }
                        if (!empty($translationData['slug'])) {
                            $translation->setSlug($translationData['slug']);
                        }
                        if (!empty($translationData['metaTitle'])) {
                            $translation->setMetaTitle($translationData['metaTitle']);
                        }
                        if (!empty($translationData['metaDescription'])) {
                            $translation->setMetaDescription($translationData['metaDescription']);
                        }

                        $this->entityManager->persist($translation);
                    }
                }
            }

            $this->entityManager->flush();

            $this->addFlash('success', $isNew ? 'Catégorie créée avec succès.' : 'Catégorie modifiée avec succès.');
            return $this->redirectToRoute('admin_category_show', ['id' => $category->getId()]);

        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de l\'enregistrement : ' . $e->getMessage());
        }

        return $this->redirectToRoute('admin_category_index');
    }
}