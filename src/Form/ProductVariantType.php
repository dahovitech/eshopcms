<?php

namespace App\Form;

use App\Entity\AttributeValue;
use App\Entity\ProductVariant;
use App\Repository\AttributeValueRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ProductVariantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('sku', TextType::class, [
                'label' => 'SKU Variante *',
                'attr' => ['class' => 'form-control form-control-sm'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le SKU de la variante ne peut pas être vide']),
                    new Assert\Length(['max' => 100])
                ]
            ])
            ->add('price', MoneyType::class, [
                'label' => 'Prix spécifique (€)',
                'currency' => 'EUR',
                'required' => false,
                'attr' => ['class' => 'form-control form-control-sm', 'step' => '0.01'],
                'help' => 'Laisser vide pour utiliser le prix du produit parent',
                'constraints' => [
                    new Assert\PositiveOrZero(['message' => 'Le prix doit être positif ou nul'])
                ]
            ])
            ->add('compareAtPrice', MoneyType::class, [
                'label' => 'Prix de comparaison (€)',
                'currency' => 'EUR',
                'required' => false,
                'attr' => ['class' => 'form-control form-control-sm', 'step' => '0.01'],
                'constraints' => [
                    new Assert\PositiveOrZero(['message' => 'Le prix de comparaison doit être positif ou nul'])
                ]
            ])
            ->add('costPrice', MoneyType::class, [
                'label' => 'Prix de revient (€)',
                'currency' => 'EUR',
                'required' => false,
                'attr' => ['class' => 'form-control form-control-sm', 'step' => '0.01'],
                'constraints' => [
                    new Assert\PositiveOrZero(['message' => 'Le prix de revient doit être positif ou nul'])
                ]
            ])
            ->add('stock', IntegerType::class, [
                'label' => 'Stock',
                'attr' => ['class' => 'form-control form-control-sm', 'min' => '0'],
                'constraints' => [
                    new Assert\PositiveOrZero(['message' => 'Le stock doit être positif ou nul'])
                ]
            ])
            ->add('lowStockThreshold', IntegerType::class, [
                'label' => 'Seuil stock faible',
                'required' => false,
                'attr' => ['class' => 'form-control form-control-sm', 'min' => '0'],
                'constraints' => [
                    new Assert\PositiveOrZero(['message' => 'Le seuil doit être positif ou nul'])
                ]
            ])
            ->add('weight', NumberType::class, [
                'label' => 'Poids (kg)',
                'required' => false,
                'attr' => ['class' => 'form-control form-control-sm', 'step' => '0.001'],
                'constraints' => [
                    new Assert\PositiveOrZero(['message' => 'Le poids doit être positif ou nul'])
                ]
            ])
            ->add('trackStock', CheckboxType::class, [
                'label' => 'Suivre le stock',
                'required' => false,
                'attr' => ['class' => 'form-check-input']
            ])
            ->add('isActive', CheckboxType::class, [
                'label' => 'Variante active',
                'required' => false,
                'attr' => ['class' => 'form-check-input']
            ])
            ->add('sortOrder', IntegerType::class, [
                'label' => 'Ordre de tri',
                'attr' => ['class' => 'form-control form-control-sm'],
                'data' => 0
            ])
            ->add('attributeValues', EntityType::class, [
                'label' => 'Attributs',
                'class' => AttributeValue::class,
                'choice_label' => function(AttributeValue $attributeValue) {
                    return $attributeValue->getAttribute()->getName('fr') . ': ' . $attributeValue->getName('fr');
                },
                'multiple' => true,
                'required' => false,
                'attr' => [
                    'class' => 'form-select form-select-sm',
                    'multiple' => 'multiple'
                ],
                'query_builder' => function(AttributeValueRepository $repo) {
                    return $repo->createQueryBuilder('av')
                        ->join('av.attribute', 'a')
                        ->where('a.isVariant = :isVariant')
                        ->andWhere('a.isActive = :isActive')
                        ->andWhere('av.isActive = :isValueActive')
                        ->setParameter('isVariant', true)
                        ->setParameter('isActive', true)
                        ->setParameter('isValueActive', true)
                        ->orderBy('a.sortOrder', 'ASC')
                        ->addOrderBy('av.sortOrder', 'ASC');
                }
            ])
            ->add('translations', CollectionType::class, [
                'label' => false,
                'entry_type' => ProductVariantTranslationType::class,
                'entry_options' => ['label' => false],
                'by_reference' => false,
                'required' => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProductVariant::class,
            'csrf_protection' => false
        ]);
    }
}
