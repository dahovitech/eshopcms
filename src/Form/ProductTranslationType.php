<?php

namespace App\Form;

use App\Entity\Language;
use App\Entity\ProductTranslation;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ProductTranslationType extends AbstractType
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
                'label' => 'Nom du produit',
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le nom ne peut pas être vide']),
                    new Assert\Length(['max' => 255])
                ]
            ])
            ->add('slug', TextType::class, [
                'label' => 'URL (slug)',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'readonly' => true
                ],
                'help' => 'Généré automatiquement à partir du nom'
            ])
            ->add('shortDescription', TextareaType::class, [
                'label' => 'Description courte',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 3,
                    'maxlength' => 255
                ],
                'constraints' => [
                    new Assert\Length(['max' => 255])
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description complète',
                'required' => false,
                'attr' => [
                    'class' => 'form-control editor',
                    'rows' => 6
                ]
            ])
            ->add('metaTitle', TextType::class, [
                'label' => 'Titre SEO',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'maxlength' => 70
                ],
                'constraints' => [
                    new Assert\Length(['max' => 70])
                ]
            ])
            ->add('metaDescription', TextareaType::class, [
                'label' => 'Description SEO',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 3,
                    'maxlength' => 160
                ],
                'constraints' => [
                    new Assert\Length(['max' => 160])
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProductTranslation::class,
            'csrf_protection' => false
        ]);
    }
}
