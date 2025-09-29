<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductDimensionsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('length', NumberType::class, [
                'label' => 'Longueur (cm)',
                'scale' => 2,
                'attr' => ['step' => 0.01, 'min' => 0, 'placeholder' => '0.00'],
                'required' => false
            ])
            ->add('width', NumberType::class, [
                'label' => 'Largeur (cm)',
                'scale' => 2,
                'attr' => ['step' => 0.01, 'min' => 0, 'placeholder' => '0.00'],
                'required' => false
            ])
            ->add('height', NumberType::class, [
                'label' => 'Hauteur (cm)',
                'scale' => 2,
                'attr' => ['step' => 0.01, 'min' => 0, 'placeholder' => '0.00'],
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}