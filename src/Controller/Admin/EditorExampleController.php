<?php

namespace App\Controller\Admin;

use App\Form\Type\ExampleArticleType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Contrôleur d'exemple montrant l'utilisation complète 
 * de l'éditeur de texte dans un formulaire réel
 */
#[Route('/editor-example', name: 'admin_editor_example_')]
class EditorExampleController extends AbstractController
{
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('admin/editor-example/index.html.twig');
    }

    #[Route('/article-form', name: 'article_form', methods: ['GET', 'POST'])]
    public function articleForm(Request $request): Response
    {
        // Données factices pour simuler un article
        $articleData = [
            'title' => '',
            'excerpt' => '',
            'content' => '',
            'featuredImage' => null,
            'gallery' => [],
            'published' => false,
            'internalNotes' => ''
        ];

        $form = $this->createForm(ExampleArticleType::class, $articleData);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            
            // Simuler la sauvegarde
            $this->addFlash('success', sprintf(
                'Article "%s" enregistré avec succès !<br>' .
                'Contenu principal: %d caractères<br>' .
                'Résumé: %d caractères<br>' .
                'Image de couverture: %s<br>' .
                'Galerie: %d images<br>' .
                'Status: %s',
                $data['title'],
                strlen(strip_tags($data['content'])),
                strlen(strip_tags($data['excerpt'])),
                $data['featuredImage'] ? 'Sélectionnée' : 'Aucune',
                count($data['gallery'] ?? []),
                $data['published'] ? 'Publié' : 'Brouillon'
            ));

            return $this->redirectToRoute('admin_editor_example_article_form');
        }

        return $this->render('admin/editor-example/article-form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/auto-save', name: 'auto_save', methods: ['POST'])]
    public function autoSave(Request $request): Response
    {
        $content = $request->getContent();
        $data = json_decode($content, true);

        // Simuler la sauvegarde automatique
        if (isset($data['content'])) {
            // Ici vous pourriez sauvegarder en base de données ou en session
            return $this->json([
                'success' => true,
                'message' => 'Brouillon sauvegardé automatiquement',
                'timestamp' => new \DateTime(),
                'word_count' => str_word_count(strip_tags($data['content'])),
                'character_count' => strlen(strip_tags($data['content']))
            ]);
        }

        return $this->json([
            'success' => false,
            'message' => 'Erreur lors de la sauvegarde automatique'
        ], 400);
    }

    #[Route('/quick-test', name: 'quick_test', methods: ['GET', 'POST'])]
    public function quickTest(Request $request): Response
    {
        // Formulaire simple pour test rapide
        $form = $this->createFormBuilder()
            ->add('quickContent', \App\Form\Type\MediaTextareaType::class, [
                'label' => 'Test rapide de l\'éditeur',
                'required' => false,
                'enable_media' => true,
                'enable_editor' => true,
                'editor_height' => 350,
                'attr' => [
                    'placeholder' => 'Testez rapidement l\'éditeur ici...',
                    'data-enable-auto-save' => 'true',
                    'data-auto-save-interval' => '10000' // 10 secondes
                ]
            ])
            ->add('save', \Symfony\Component\Form\Extension\Core\Type\SubmitType::class, [
                'label' => 'Tester',
                'attr' => ['class' => 'btn btn-primary']
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            
            $this->addFlash('info', sprintf(
                'Test réussi !<br>Contenu traité: %d caractères<br>Mots: %d',
                strlen(strip_tags($data['quickContent'])),
                str_word_count(strip_tags($data['quickContent']))
            ));

            return $this->redirectToRoute('admin_editor_example_quick_test');
        }

        return $this->render('admin/editor-example/quick-test.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}