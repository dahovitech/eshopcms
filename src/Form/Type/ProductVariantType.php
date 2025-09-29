<?php

namespace App\Form\Type;

use App\Entity\ProductVariant;
use App\Entity\AttributeValue;
use App\Repository\AttributeValueRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductVariantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('sku', TextType::class, [
                'label' => 'SKU de la variation',
                'attr' => ['placeholder' => 'Code unique de cette variation']
            ])
            ->add('price', MoneyType::class, [
                'label' => 'Prix (€)',
                'currency' => 'EUR',
                'required' => false,
                'help' => 'Laissez vide pour utiliser le prix du produit parent'
            ])
            ->add('compareAtPrice', MoneyType::class, [
                'label' => 'Prix de comparaison (€)',
                'currency' => 'EUR',
                'required' => false
            ])
            ->add('costPrice', MoneyType::class, [
                'label' => 'Prix de revient (€)',
                'currency' => 'EUR',
                'required' => false
            ])
            ->add('stock', IntegerType::class, [
                'label' => 'Stock',
                'attr' => ['min' => 0],
                'required' => false
            ])
            ->add('lowStockThreshold', IntegerType::class, [
                'label' => 'Seuil de stock faible',
                'attr' => ['min' => 0],
                'required' => false
            ])
            ->add('trackStock', CheckboxType::class, [
                'label' => 'Suivre le stock',
                'required' => false
            ])
            ->add('weight', NumberType::class, [
                'label' => 'Poids (kg)',
                'scale' => 3,
                'attr' => ['step' => 0.001, 'min' => 0],
                'required' => false
            ])
            ->add('isActive', CheckboxType::class, [
                'label' => 'Variation active',
                'required' => false
            ])
            ->add('sortOrder', IntegerType::class, [
                'label' => 'Ordre d\'affichage',
                'attr' => ['min' => 0],
                'required' => false,
                'data' => 0
            ])
            ->add('attributeValues', EntityType::class, [
                'class' => AttributeValue::class,
                'choice_label' => function(AttributeValue $attributeValue) {
                    $attribute = $attributeValue->getAttribute();
                    return $attribute->getName('fr', 'en') . ': ' . $attributeValue->getValue('fr', 'en');
                },
                'multiple' => true,
                'expanded' => true,
                'required' => false,
                'label' => 'Attributs de cette variation',
                'query_builder' => function (AttributeValueRepository $repository) {
                    return $repository->createQueryBuilder('av')
                        ->leftJoin('av.attribute', 'a')
                        ->orderBy('a.name', 'ASC')
                        ->addOrderBy('av.value', 'ASC');
                }
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProductVariant::class,
        ]);
    }
}