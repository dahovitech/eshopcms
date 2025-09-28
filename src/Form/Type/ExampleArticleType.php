<?php

namespace App\Form\Type;

use App\Form\Type\MediaTextareaType;
use App\Form\Type\MediaSelectorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Exemple d'utilisation des FormTypes de l'éditeur de texte
 * dans un contexte réel - Formulaire d'article de blog
 */
class ExampleArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Titre simple
            ->add('title', TextType::class, [
                'label' => 'Titre de l\'article',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Saisissez le titre de votre article...'
                ]
            ])
            
            // Description courte sans médias
            ->add('excerpt', MediaTextareaType::class, [
                'label' => 'Résumé / Extrait',
                'required' => false,
                'enable_media' => false,  // Pas de médias dans l'extrait
                'enable_editor' => true,
                'editor_height' => 150,
                'attr' => [
                    'placeholder' => 'Rédigez un court résumé de votre article...',
                    'rows' => 3
                ]
            ])
            
            // Contenu principal avec toutes les fonctionnalités
            ->add('content', MediaTextareaType::class, [
                'label' => 'Contenu principal',
                'required' => true,
                'enable_media' => true,   // Médias activés
                'enable_editor' => true,
                'editor_height' => 500,
                'attr' => [
                    'placeholder' => 'Rédigez le contenu de votre article avec images, médias et formatage...',
                    'rows' => 20,
                    // Sauvegarde automatique activée
                    'data-enable-auto-save' => 'true',
                    'data-auto-save-interval' => '30000' // 30 secondes
                ]
            ])
            
            // Image de couverture (sélection unique)
            ->add('featuredImage', MediaSelectorType::class, [
                'label' => 'Image de couverture',
                'required' => false,
                'multiple' => false,       // Une seule image
                'show_preview' => true,
                'allow_upload' => true,
                'attr' => [
                    'class' => 'media-selector',
                    'data-multiple' => 'false'
                ]
            ])
            
            // Galerie d'images (sélection multiple)
            ->add('gallery', MediaSelectorType::class, [
                'label' => 'Galerie d\'images',
                'required' => false,
                'multiple' => true,        // Sélection multiple
                'show_preview' => true,
                'allow_upload' => true,
                'attr' => [
                    'class' => 'media-selector',
                    'data-multiple' => 'true'
                ]
            ])
            
            // Publié ou brouillon
            ->add('published', CheckboxType::class, [
                'label' => 'Publier l\'article',
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input'
                ]
            ])
            
            // Notes internes avec éditeur simple
            ->add('internalNotes', MediaTextareaType::class, [
                'label' => 'Notes internes (administration)',
                'required' => false,
                'enable_media' => false,
                'enable_editor' => true,
                'editor_height' => 200,
                'attr' => [
                    'placeholder' => 'Notes et commentaires internes (non visibles sur le site)...',
                    'rows' => 5
                ]
            ])
            
            // Bouton de soumission
            ->add('save', SubmitType::class, [
                'label' => 'Enregistrer l\'article',
                'attr' => [
                    'class' => 'btn btn-primary btn-lg'
                ]
            ])
            
            ->add('saveAndPublish', SubmitType::class, [
                'label' => 'Enregistrer et publier',
                'attr' => [
                    'class' => 'btn btn-success btn-lg'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Ici vous pourriez spécifier la classe d'entité si elle existait
            // 'data_class' => Article::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'example_article';
    }
}