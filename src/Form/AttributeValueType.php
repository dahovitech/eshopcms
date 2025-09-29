<?php

namespace App\Form;

use App\Entity\AttributeValue;
use App\Entity\Media;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class AttributeValueType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('value', TextType::class, [
                'label' => 'Valeur *',
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'La valeur ne peut pas être vide']),
                    new Assert\Length(['max' => 255])
                ]
            ])
            ->add('hexColor', TextType::class, [
                'label' => 'Couleur (hex)',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'type' => 'color',
                    'pattern' => '#[0-9A-Fa-f]{6}',
                    'title' => 'Format: #RRGGBB'
                ],
                'constraints' => [
                    new Assert\Regex([
                        'pattern' => '/^#[0-9A-F]{6}$/i',
                        'message' => 'Format de couleur hexadécimal invalide'
                    ])
                ],
                'help' => 'Pour les attributs de type couleur'
            ])
            ->add('image', EntityType::class, [
                'label' => 'Image',
                'class' => Media::class,
                'choice_label' => 'fileName',
                'placeholder' => 'Sélectionner une image',
                'required' => false,
                'attr' => ['class' => 'form-select'],
                'help' => 'Image représentant cette valeur'
            ])
            ->add('isActive', CheckboxType::class, [
                'label' => 'Active',
                'required' => false,
                'attr' => ['class' => 'form-check-input']
            ])
            ->add('sortOrder', IntegerType::class, [
                'label' => 'Ordre de tri',
                'attr' => ['class' => 'form-control'],
                'data' => 0
            ])
            ->add('translations', CollectionType::class, [
                'label' => false,
                'entry_type' => AttributeValueTranslationType::class,
                'entry_options' => ['label' => false],
                'by_reference' => false,
                'required' => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => AttributeValue::class,
            'csrf_protection' => false
        ]);
    }
}
