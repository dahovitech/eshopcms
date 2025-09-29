<?php

namespace App\Form;

use App\Entity\Language;
use App\Entity\ProductVariantTranslation;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ProductVariantTranslationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('language', EntityType::class, [
                'class' => Language::class,
                'choice_label' => 'name',
                'disabled' => true
            ])
            ->add('name', TextType::class, [
                'label' => 'Nom de la variante',
                'required' => false,
                'attr' => ['class' => 'form-control form-control-sm'],
                'help' => 'Laisser vide pour utiliser le nom automatique',
                'constraints' => [
                    new Assert\Length(['max' => 255])
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => [
                    'class' => 'form-control form-control-sm',
                    'rows' => 2
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProductVariantTranslation::class,
            'csrf_protection' => false
        ]);
    }
}
