<?php

namespace App\Controller\Admin;

use App\Entity\AttributeValue;
use App\Entity\AttributeValueTranslation;
use App\Form\AttributeValueType;
use App\Repository\AttributeRepository;
use App\Repository\AttributeValueRepository;
use App\Repository\LanguageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/attribute-value', name: 'admin_attribute_value_')]
#[IsGranted('ROLE_ADMIN')]
class AttributeValueController extends AbstractController
{
    public function __construct(
        private AttributeValueRepository $attributeValueRepository,
        private AttributeRepository $attributeRepository,
        private LanguageRepository $languageRepository,
        private EntityManagerInterface $entityManager
    ) {}

    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $attributeId = $request->query->get('attribute');
        $attribute = null;
        
        if ($attributeId) {
            $attribute = $this->attributeRepository->find($attributeId);
            $attributeValues = $this->attributeValueRepository->findBy(
                ['attribute' => $attribute], 
                ['sortOrder' => 'ASC', 'value' => 'ASC']
            );
        } else {
            $attributeValues = $this->attributeValueRepository->findBy([], ['sortOrder' => 'ASC', 'value' => 'ASC']);
        }
        
        $attributes = $this->attributeRepository->findBy(['isActive' => true], ['sortOrder' => 'ASC']);
        $languages = $this->languageRepository->findActiveLanguages();
        
        return $this->render('admin/attribute-value/index.html.twig', [
            'attributeValues' => $attributeValues,
            'attributes' => $attributes,
            'currentAttribute' => $attribute,
            'languages' => $languages
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $attributeValue = new AttributeValue();
        $languages = $this->languageRepository->findActiveLanguages();
        
        // Set default attribute if provided
        $attributeId = $request->query->get('attribute');
        if ($attributeId) {
            $attribute = $this->attributeRepository->find($attributeId);
            if ($attribute) {
                $attributeValue->setAttribute($attribute);
            }
        }
        
        // Initialize translations for all active languages
        foreach ($languages as $language) {
            $translation = new AttributeValueTranslation();
            $translation->setLanguage($language);
            $attributeValue->addTranslation($translation);
        }

        $form = $this->createForm(AttributeValueType::class, $attributeValue);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->entityManager->persist($attributeValue);
                $this->entityManager->flush();

                $this->addFlash('success', 'Valeur d\'attribut créée avec succès.');
                return $this->redirectToRoute('admin_attribute_value_show', ['id' => $attributeValue->getId()]);

            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de la création : ' . $e->getMessage());
            }
        }

        return $this->render('admin/attribute-value/new.html.twig', [
            'attributeValue' => $attributeValue,
            'form' => $form->createView(),
            'languages' => $languages
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(AttributeValue $attributeValue): Response
    {
        $languages = $this->languageRepository->findActiveLanguages();
        
        return $this->render('admin/attribute-value/show.html.twig', [
            'attributeValue' => $attributeValue,
            'languages' => $languages
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function edit(Request $request, AttributeValue $attributeValue): Response
    {
        $languages = $this->languageRepository->findActiveLanguages();
        
        // Ensure translations exist for all active languages
        foreach ($languages as $language) {
            if (!$attributeValue->hasTranslation($language->getCode())) {
                $translation = new AttributeValueTranslation();
                $translation->setLanguage($language);
                $attributeValue->addTranslation($translation);
            }
        }

        $form = $this->createForm(AttributeValueType::class, $attributeValue);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->entityManager->flush();

                $this->addFlash('success', 'Valeur d\'attribut modifiée avec succès.');
                return $this->redirectToRoute('admin_attribute_value_show', ['id' => $attributeValue->getId()]);

            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de la modification : ' . $e->getMessage());
            }
        }

        return $this->render('admin/attribute-value/edit.html.twig', [
            'attributeValue' => $attributeValue,
            'form' => $form->createView(),
            'languages' => $languages
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(Request $request, AttributeValue $attributeValue): Response
    {
        if ($this->isCsrfTokenValid('delete'.$attributeValue->getId(), $request->request->get('_token'))) {
            try {
                $this->entityManager->remove($attributeValue);
                $this->entityManager->flush();
                $this->addFlash('success', 'Valeur d\'attribut supprimée avec succès.');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de la suppression : ' . $e->getMessage());
            }
        }

        return $this->redirectToRoute('admin_attribute_value_index');
    }
}
