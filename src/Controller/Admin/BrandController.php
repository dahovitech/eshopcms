<?php

namespace App\Controller\Admin;

use App\Entity\Brand;
use App\Entity\BrandTranslation;
use App\Repository\LanguageRepository;
use App\Repository\BrandRepository;
use App\Repository\BrandTranslationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/brand', name: 'admin_brand_')]
#[IsGranted('ROLE_ADMIN')]
class BrandController extends AbstractController
{
    public function __construct(
        private BrandRepository $brandRepository,
        private LanguageRepository $languageRepository,
        private BrandTranslationRepository $brandTranslationRepository,
        private EntityManagerInterface $entityManager
    ) {}

    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        $brands = $this->brandRepository->findAll();
        $languages = $this->languageRepository->findActiveLanguages();

        return $this->render('admin/brand/index.html.twig', [
            'brands' => $brands,
            'languages' => $languages
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $brand = new Brand();
        $languages = $this->languageRepository->findActiveLanguages();
        $defaultLanguage = $this->languageRepository->findDefaultLanguage();

        if ($request->isMethod('POST')) {
            return $this->handleFormSubmission($request, $brand, $languages, true);
        }

        return $this->render('admin/brand/new.html.twig', [
            'brand' => $brand,
            'languages' => $languages,
            'defaultLanguage' => $defaultLanguage
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(Brand $brand): Response
    {
        $languages = $this->languageRepository->findActiveLanguages();
        $translations = [];
        
        foreach ($languages as $language) {
            $translation = $this->brandTranslationRepository->findOneBy([
                'brand' => $brand,
                'language' => $language
            ]);
            if ($translation) {
                $translations[$language->getCode()] = $translation;
            }
        }

        return $this->render('admin/brand/show.html.twig', [
            'brand' => $brand,
            'translations' => $translations,
            'languages' => $languages
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function edit(Request $request, Brand $brand): Response
    {
        $languages = $this->languageRepository->findActiveLanguages();
        $defaultLanguage = $this->languageRepository->findDefaultLanguage();

        if ($request->isMethod('POST')) {
            return $this->handleFormSubmission($request, $brand, $languages, false);
        }

        // Preload existing translations
        $translations = [];
        foreach ($languages as $language) {
            $translation = $this->brandTranslationRepository->findOneBy([
                'brand' => $brand,
                'language' => $language
            ]);
            if ($translation) {
                $translations[$language->getCode()] = $translation;
            }
        }

        return $this->render('admin/brand/edit.html.twig', [
            'brand' => $brand,
            'languages' => $languages,
            'defaultLanguage' => $defaultLanguage,
            'translations' => $translations
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(Request $request, Brand $brand): Response
    {
        if ($this->isCsrfTokenValid('delete'.$brand->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($brand);
            $this->entityManager->flush();
            $this->addFlash('success', 'Marque supprimée avec succès.');
        }

        return $this->redirectToRoute('admin_brand_index');
    }

    #[Route('/{id}/toggle-active', name: 'toggle_active', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function toggleActive(Brand $brand): JsonResponse
    {
        $brand->setIsActive(!$brand->isActive());
        $this->entityManager->flush();

        return new JsonResponse([
            'success' => true,
            'isActive' => $brand->isActive()
        ]);
    }

    private function handleFormSubmission(Request $request, Brand $brand, array $languages, bool $isNew): Response
    {
        $data = $request->request->all();
        
        try {
            // Update brand basic data
            $brand->setIsActive(isset($data['isActive']));

            if ($isNew) {
                $this->entityManager->persist($brand);
            }
            
            $this->entityManager->flush();

            // Handle translations
            foreach ($languages as $language) {
                $langCode = $language->getCode();
                if (isset($data['translations'][$langCode])) {
                    $translationData = $data['translations'][$langCode];
                    
                    if (!empty($translationData['name']) || !empty($translationData['description'])) {
                        $translation = $this->brandTranslationRepository->findOneBy([
                            'brand' => $brand,
                            'language' => $language
                        ]);
                        
                        if (!$translation) {
                            $translation = new BrandTranslation();
                            $translation->setBrand($brand);
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

                        $this->entityManager->persist($translation);
                    }
                }
            }

            $this->entityManager->flush();

            $this->addFlash('success', $isNew ? 'Marque créée avec succès.' : 'Marque modifiée avec succès.');
            return $this->redirectToRoute('admin_brand_show', ['id' => $brand->getId()]);

        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de l\'enregistrement : ' . $e->getMessage());
        }

        return $this->redirectToRoute('admin_brand_index');
    }
}