<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use App\Entity\ProductTranslation;
use App\Repository\LanguageRepository;
use App\Repository\ProductRepository;
use App\Repository\ProductTranslationRepository;
use App\Repository\CategoryRepository;
use App\Repository\BrandRepository;
use App\Repository\MediaRepository;
use App\Service\MediaService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/product', name: 'admin_product_')]
#[IsGranted('ROLE_ADMIN')]
class ProductController extends AbstractController
{
    public function __construct(
        private ProductRepository $productRepository,
        private LanguageRepository $languageRepository,
        private CategoryRepository $categoryRepository,
        private BrandRepository $brandRepository,
        private ProductTranslationRepository $productTranslationRepository,
        private MediaRepository $mediaRepository,
        private MediaService $mediaService,
        private EntityManagerInterface $entityManager
    ) {}

    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        $products = $this->productRepository->findAll();
        $languages = $this->languageRepository->findActiveLanguages();
        
        // Calcul des statistiques simples
        $statistics = [
            'totalProducts' => count($products),
            'activeProducts' => count(array_filter($products, fn($p) => $p->isActive())),
            'lowStockProducts' => count(array_filter($products, fn($p) => $p->getStock() !== null && $p->getStock() <= 5))
        ];

        return $this->render('admin/product/index.html.twig', [
            'products' => $products,
            'languages' => $languages,
            'statistics' => $statistics
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $product = new Product();
        $languages = $this->languageRepository->findActiveLanguages();
        $defaultLanguage = $this->languageRepository->findDefaultLanguage();
        $categories = $this->categoryRepository->findAll();
        $brands = $this->brandRepository->findAll();

        if ($request->isMethod('POST')) {
            return $this->handleFormSubmission($request, $product, $languages, true);
        }

        return $this->render('admin/product/new.html.twig', [
            'product' => $product,
            'languages' => $languages,
            'defaultLanguage' => $defaultLanguage,
            'categories' => $categories,
            'brands' => $brands
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(Product $product): Response
    {
        $languages = $this->languageRepository->findActiveLanguages();
        $translations = [];
        
        foreach ($languages as $language) {
            $translation = $this->productTranslationRepository->findOneBy([
                'product' => $product,
                'language' => $language
            ]);
            if ($translation) {
                $translations[$language->getCode()] = $translation;
            }
        }

        return $this->render('admin/product/show.html.twig', [
            'product' => $product,
            'translations' => $translations,
            'languages' => $languages
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function edit(Request $request, Product $product): Response
    {
        $languages = $this->languageRepository->findActiveLanguages();
        $defaultLanguage = $this->languageRepository->findDefaultLanguage();
        $categories = $this->categoryRepository->findAll();
        $brands = $this->brandRepository->findAll();

        if ($request->isMethod('POST')) {
            return $this->handleFormSubmission($request, $product, $languages, false);
        }

        // Preload existing translations
        $translations = [];
        foreach ($languages as $language) {
            $translation = $this->productTranslationRepository->findOneBy([
                'product' => $product,
                'language' => $language
            ]);
            if ($translation) {
                $translations[$language->getCode()] = $translation;
            }
        }

        return $this->render('admin/product/edit.html.twig', [
            'product' => $product,
            'languages' => $languages,
            'defaultLanguage' => $defaultLanguage,
            'categories' => $categories,
            'brands' => $brands,
            'translations' => $translations
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(Request $request, Product $product): Response
    {
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($product);
            $this->entityManager->flush();

            $this->addFlash('success', 'Produit supprimé avec succès.');
        }

        return $this->redirectToRoute('admin_product_index');
    }

    #[Route('/{id}/toggle-active', name: 'toggle_active', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function toggleActive(Product $product): JsonResponse
    {
        $product->setIsActive(!$product->isActive());
        $this->entityManager->flush();

        return new JsonResponse([
            'success' => true,
            'isActive' => $product->isActive()
        ]);
    }

    #[Route('/media-library', name: 'media_library', methods: ['GET'])]
    public function mediaLibrary(Request $request): JsonResponse
    {
        $page = $request->query->getInt('page', 1);
        $search = $request->query->get('search');
        
        $result = $this->mediaService->getMediaList($page, 20, $search);
        
        $mediaData = [];
        foreach ($result['medias'] as $media) {
            $mediaData[] = [
                'id' => $media->getId(),
                'fileName' => $media->getFileName(),
                'alt' => $media->getAlt(),
                'webPath' => $media->getWebPath(),
                'extension' => $media->getExtension()
            ];
        }
        
        return new JsonResponse([
            'medias' => $mediaData,
            'total' => $result['total'],
            'totalPages' => $result['totalPages'],
            'currentPage' => $result['currentPage']
        ]);
    }

    private function handleFormSubmission(Request $request, Product $product, array $languages, bool $isNew): Response
    {
        $data = $request->request->all();
        
        try {
            // Update product basic data
            if (isset($data['sku'])) {
                $product->setSku($data['sku']);
            }
            if (isset($data['basePrice'])) {
                $product->setBasePrice($data['basePrice']);
            }
            if (isset($data['stock'])) {
                $product->setStock((int)$data['stock']);
            }
            if (isset($data['weight'])) {
                $product->setWeight($data['weight'] ? (float)$data['weight'] : null);
            }
            if (isset($data['categoryId'])) {
                $category = $this->categoryRepository->find($data['categoryId']);
                $product->setCategory($category);
            }
            if (isset($data['brandId'])) {
                $brand = $this->brandRepository->find($data['brandId']);
                $product->setBrand($brand);
            }
            
            $product->setIsActive(isset($data['isActive']));

            // Handle media attachments
            if (isset($data['primaryImageId']) && !empty($data['primaryImageId'])) {
                $primaryImage = $this->mediaRepository->find($data['primaryImageId']);
                if ($primaryImage) {
                    $product->setPrimaryImage($primaryImage);
                }
            } else {
                $product->setPrimaryImage(null);
            }

            // Handle additional media
            if (isset($data['mediaIds']) && is_array($data['mediaIds'])) {
                // Clear existing media
                $product->getMedia()->clear();
                
                // Add selected media
                foreach ($data['mediaIds'] as $mediaId) {
                    if (!empty($mediaId)) {
                        $media = $this->mediaRepository->find($mediaId);
                        if ($media) {
                            $product->addMedia($media);
                        }
                    }
                }
            }

            if ($isNew) {
                $this->entityManager->persist($product);
            }
            
            $this->entityManager->flush();

            // Handle translations
            foreach ($languages as $language) {
                $langCode = $language->getCode();
                if (isset($data['translations'][$langCode])) {
                    $translationData = $data['translations'][$langCode];
                    
                    if (!empty($translationData['name']) || !empty($translationData['description'])) {
                        $translation = $this->productTranslationRepository->findOneBy([
                            'product' => $product,
                            'language' => $language
                        ]);
                        
                        if (!$translation) {
                            $translation = new ProductTranslation();
                            $translation->setProduct($product);
                            $translation->setLanguage($language);
                        }

                        if (!empty($translationData['name'])) {
                            $translation->setName($translationData['name']);
                        }
                        if (!empty($translationData['description'])) {
                            $translation->setDescription($translationData['description']);
                        }
                        if (!empty($translationData['shortDescription'])) {
                            $translation->setShortDescription($translationData['shortDescription']);
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

            $this->addFlash('success', $isNew ? 'Produit créé avec succès.' : 'Produit modifié avec succès.');
            return $this->redirectToRoute('admin_product_show', ['id' => $product->getId()]);

        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de l\'enregistrement : ' . $e->getMessage());
        }

        return $this->redirectToRoute('admin_product_index');
    }
}