<?php

namespace App\Form;

use App\Entity\Attribute;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class AttributeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('code', TextType::class, [
                'label' => 'Code *',
                'help' => 'Code unique pour cet attribut (ex: color, size)',
                'attr' => [
                    'class' => 'form-control',
                    'pattern' => '[a-z0-9_]+',
                    'title' => 'Seules les lettres minuscules, chiffres et underscore sont autorisés'
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le code ne peut pas être vide']),
                    new Assert\Length(['max' => 100]),
                    new Assert\Regex([
                        'pattern' => '/^[a-z0-9_]+$/',
                        'message' => 'Le code doit contenir uniquement des lettres minuscules, chiffres et underscores'
                    ])
                ]
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Type',
                'choices' => [
                    'Texte' => Attribute::TYPE_TEXT,
                    'Nombre' => Attribute::TYPE_NUMBER,
                    'Sélection' => Attribute::TYPE_SELECT,
                    'Couleur' => Attribute::TYPE_COLOR,
                    'Booléen (Oui/Non)' => Attribute::TYPE_BOOLEAN
                ],
                'attr' => ['class' => 'form-select'],
                'help' => 'Choisissez le type d\'attribut approprié'
            ])
            ->add('isRequired', CheckboxType::class, [
                'label' => 'Obligatoire',
                'required' => false,
                'attr' => ['class' => 'form-check-input'],
                'help' => 'Cet attribut est-il obligatoire ?'
            ])
            ->add('isVariant', CheckboxType::class, [
                'label' => 'Utilisé pour les variantes',
                'required' => false,
                'attr' => ['class' => 'form-check-input'],
                'help' => 'Cet attribut peut-il créer des variantes de produit ?'
            ])
            ->add('isFilterable', CheckboxType::class, [
                'label' => 'Filtrable',
                'required' => false,
                'attr' => ['class' => 'form-check-input'],
                'help' => 'Peut être utilisé comme filtre dans la boutique'
            ])
            ->add('isActive', CheckboxType::class, [
                'label' => 'Actif',
                'required' => false,
                'attr' => ['class' => 'form-check-input']
            ])
            ->add('sortOrder', IntegerType::class, [
                'label' => 'Ordre de tri',
                'attr' => ['class' => 'form-control'],
                'data' => 0
            ])
            ->add('configuration', TextareaType::class, [
                'label' => 'Configuration JSON',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 4,
                    'placeholder' => '{"min": 0, "max": 100, "unit": "cm"}'
                ],
                'help' => 'Configuration avancée au format JSON'
            ])
            ->add('values', CollectionType::class, [
                'label' => 'Valeurs possibles',
                'entry_type' => AttributeValueType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'required' => false,
                'attr' => ['class' => 'attribute-values-collection']
            ])
            ->add('translations', CollectionType::class, [
                'label' => false,
                'entry_type' => AttributeTranslationType::class,
                'entry_options' => ['label' => false],
                'by_reference' => false,
                'required' => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Attribute::class,
            'csrf_protection' => true
        ]);
    }
}
