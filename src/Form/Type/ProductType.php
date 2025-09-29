<?php

namespace App\Form\Type;

use App\Entity\Product;
use App\Entity\Brand;
use App\Entity\Category;
use App\Repository\BrandRepository;
use App\Repository\CategoryRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Basic Information
            ->add('sku', TextType::class, [
                'label' => 'SKU',
                'attr' => ['placeholder' => 'Code unique du produit']
            ])
            ->add('price', MoneyType::class, [
                'label' => 'Prix de vente (€)',
                'currency' => 'EUR',
                'required' => true
            ])
            ->add('compareAtPrice', MoneyType::class, [
                'label' => 'Prix de comparaison (€)',
                'currency' => 'EUR',
                'required' => false,
                'help' => 'Prix barré affiché (doit être supérieur au prix de vente)'
            ])
            ->add('costPrice', MoneyType::class, [
                'label' => 'Prix de revient (€)',
                'currency' => 'EUR',
                'required' => false,
                'help' => 'Prix d\'achat/fabrication (pour le calcul de marge)'
            ])

            // Stock Management
            ->add('stock', IntegerType::class, [
                'label' => 'Stock',
                'attr' => ['min' => 0],
                'required' => false
            ])
            ->add('lowStockThreshold', IntegerType::class, [
                'label' => 'Seuil de stock faible',
                'attr' => ['min' => 0],
                'required' => false,
                'help' => 'Alerte quand le stock atteint ce niveau'
            ])
            ->add('trackStock', CheckboxType::class, [
                'label' => 'Suivre le stock',
                'required' => false,
                'help' => 'Décrémenter automatiquement le stock lors des commandes'
            ])

            // Physical Properties
            ->add('weight', NumberType::class, [
                'label' => 'Poids (kg)',
                'scale' => 3,
                'attr' => ['step' => 0.001, 'min' => 0],
                'required' => false
            ])

            // Product Type & Status
            ->add('status', ChoiceType::class, [
                'label' => 'Statut',
                'choices' => [
                    'Brouillon' => Product::STATUS_DRAFT,
                    'Actif' => Product::STATUS_ACTIVE,
                    'Inactif' => Product::STATUS_INACTIVE,
                    'Archivé' => Product::STATUS_ARCHIVED,
                ]
            ])
            ->add('isVariable', CheckboxType::class, [
                'label' => 'Produit variable',
                'required' => false,
                'help' => 'Ce produit a des variations (taille, couleur, etc.)'
            ])
            ->add('isDigital', CheckboxType::class, [
                'label' => 'Produit numérique',
                'required' => false,
                'help' => 'Ebook, logiciel, service en ligne, etc.'
            ])

            // Relationships
            ->add('brand', EntityType::class, [
                'class' => Brand::class,
                'choice_label' => function(Brand $brand) {
                    return $brand->getName('fr', 'en') ?: 'Marque sans nom';
                },
                'placeholder' => 'Sélectionner une marque',
                'required' => false,
                'query_builder' => function (BrandRepository $repository) {
                    return $repository->createQueryBuilder('b')
                        ->orderBy('b.id', 'ASC');
                }
            ])
            ->add('categories', EntityType::class, [
                'class' => Category::class,
                'choice_label' => function(Category $category) {
                    return $category->getName('fr', 'en') ?: 'Catégorie sans nom';
                },
                'multiple' => true,
                'expanded' => false,
                'required' => false,
                'attr' => ['class' => 'form-select'],
                'query_builder' => function (CategoryRepository $repository) {
                    return $repository->createQueryBuilder('c')
                        ->orderBy('c.id', 'ASC');
                }
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}