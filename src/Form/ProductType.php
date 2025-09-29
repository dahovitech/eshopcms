<?php

namespace App\Form;

use App\Entity\Brand;
use App\Entity\Category;
use App\Entity\Product;
use App\Repository\BrandRepository;
use App\Repository\CategoryRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('sku', TextType::class, [
                'label' => 'SKU *',
                'help' => 'Code unique du produit',
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le SKU ne peut pas être vide']),
                    new Assert\Length(['max' => 100])
                ]
            ])
            ->add('price', MoneyType::class, [
                'label' => 'Prix (€) *',
                'currency' => 'EUR',
                'attr' => ['class' => 'form-control', 'step' => '0.01'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le prix ne peut pas être vide']),
                    new Assert\PositiveOrZero(['message' => 'Le prix doit être positif ou nul'])
                ]
            ])
            ->add('compareAtPrice', MoneyType::class, [
                'label' => 'Prix de comparaison (€)',
                'currency' => 'EUR',
                'required' => false,
                'attr' => ['class' => 'form-control', 'step' => '0.01'],
                'help' => 'Prix barré pour les promotions',
                'constraints' => [
                    new Assert\PositiveOrZero(['message' => 'Le prix de comparaison doit être positif ou nul'])
                ]
            ])
            ->add('costPrice', MoneyType::class, [
                'label' => 'Prix de revient (€)',
                'currency' => 'EUR',
                'required' => false,
                'attr' => ['class' => 'form-control', 'step' => '0.01'],
                'help' => 'Prix d\'achat du produit (non affiché)',
                'constraints' => [
                    new Assert\PositiveOrZero(['message' => 'Le prix de revient doit être positif ou nul'])
                ]
            ])
            ->add('stock', IntegerType::class, [
                'label' => 'Stock',
                'required' => false,
                'attr' => ['class' => 'form-control', 'min' => '0'],
                'constraints' => [
                    new Assert\PositiveOrZero(['message' => 'Le stock doit être positif ou nul'])
                ]
            ])
            ->add('lowStockThreshold', IntegerType::class, [
                'label' => 'Seuil stock faible',
                'required' => false,
                'attr' => ['class' => 'form-control', 'min' => '0'],
                'help' => 'Alerte quand le stock descend en dessous de cette valeur',
                'constraints' => [
                    new Assert\PositiveOrZero(['message' => 'Le seuil doit être positif ou nul'])
                ]
            ])
            ->add('weight', NumberType::class, [
                'label' => 'Poids (kg)',
                'required' => false,
                'attr' => ['class' => 'form-control', 'step' => '0.001'],
                'constraints' => [
                    new Assert\PositiveOrZero(['message' => 'Le poids doit être positif ou nul'])
                ]
            ])
            ->add('brand', EntityType::class, [
                'label' => 'Marque',
                'class' => Brand::class,
                'choice_label' => function(Brand $brand) {
                    return $brand->getName('fr', 'en');
                },
                'placeholder' => 'Sélectionner une marque',
                'required' => false,
                'attr' => ['class' => 'form-select'],
                'query_builder' => function(BrandRepository $repo) {
                    return $repo->createQueryBuilder('b')
                        ->orderBy('b.id', 'ASC');
                }
            ])
            ->add('categories', EntityType::class, [
                'label' => 'Catégories',
                'class' => Category::class,
                'choice_label' => function(Category $category) {
                    return $category->getName('fr', 'en');
                },
                'multiple' => true,
                'required' => false,
                'attr' => [
                    'class' => 'form-select',
                    'multiple' => 'multiple',
                    'data-bs-toggle' => 'select'
                ],
                'query_builder' => function(CategoryRepository $repo) {
                    return $repo->createQueryBuilder('c')
                        ->orderBy('c.id', 'ASC');
                }
            ])
            ->add('trackStock', CheckboxType::class, [
                'label' => 'Suivre le stock',
                'required' => false,
                'attr' => ['class' => 'form-check-input'],
                'help' => 'Cochez pour gérer automatiquement le stock'
            ])
            ->add('isVariable', CheckboxType::class, [
                'label' => 'Produit variable',
                'required' => false,
                'attr' => ['class' => 'form-check-input'],
                'help' => 'Cochez si ce produit a des variantes (taille, couleur, etc.)'
            ])
            ->add('isDigital', CheckboxType::class, [
                'label' => 'Produit numérique',
                'required' => false,
                'attr' => ['class' => 'form-check-input'],
                'help' => 'Cochez pour les produits téléchargeables'
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Statut',
                'choices' => [
                    'Brouillon' => Product::STATUS_DRAFT,
                    'Actif' => Product::STATUS_ACTIVE,
                    'Inactif' => Product::STATUS_INACTIVE,
                    'Archivé' => Product::STATUS_ARCHIVED
                ],
                'attr' => ['class' => 'form-select']
            ])
            ->add('variants', CollectionType::class, [
                'label' => 'Variantes',
                'entry_type' => ProductVariantType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'required' => false,
                'attr' => ['class' => 'variants-collection']
            ])
            ->add('translations', CollectionType::class, [
                'label' => false,
                'entry_type' => ProductTranslationType::class,
                'entry_options' => ['label' => false],
                'by_reference' => false,
                'required' => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
            'csrf_protection' => true
        ]);
    }
}
