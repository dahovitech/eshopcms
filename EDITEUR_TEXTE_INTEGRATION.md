# Intégration de l'Éditeur de Texte OpenCMS v2.0

## 📋 Résumé

Ce document détaille l'intégration réussie de l'**Custom Editor v2.0** du projet OpenCMS dans le projet ESHOPCMS. L'éditeur de texte WYSIWYG offre des fonctionnalités avancées de rédaction avec intégration native du gestionnaire de médias.

## 🚀 Fonctionnalités Intégrées

### Fonctionnalités de Base
- ✅ **Formatage de texte**: Gras, italique, souligné, barré
- ✅ **Titres hiérarchiques**: H1, H2, H3, paragraphes
- ✅ **Listes**: À puces et numérotées
- ✅ **Liens**: Création et suppression de liens
- ✅ **Citations et code**: Blocs de citation et de code

### Fonctionnalités Avancées
- ✅ **Gestionnaire de médias intégré**: Insertion directe d'images et médias
- ✅ **Redimensionnement d'images**: Nouvelle fonctionnalité v2.0
- ✅ **Drag & Drop**: Glisser-déposer d'images et fichiers
- ✅ **Mode plein écran**: Édition en plein écran
- ✅ **Mode source**: Édition du code HTML
- ✅ **Compteur de mots**: Statistiques en temps réel
- ✅ **Sauvegarde automatique**: Sauvegarde périodique configurable
- ✅ **Responsive design**: Compatible mobile et desktop
- ✅ **Mode sombre**: Support du thème sombre

## 📁 Structure des Fichiers Intégrés

### JavaScript Components
```
eshopcms/assets/js/components/
├── custom-editor.js       # Éditeur principal (55Ko)
├── media-picker.js        # Sélecteur de médias (20Ko) 
└── media-selector.js      # Composant formulaire (9Ko)
```

### FormTypes Symfony
```
eshopcms/src/Form/Type/
├── MediaTextareaType.php  # FormType pour l'éditeur WYSIWYG
└── MediaSelectorType.php  # FormType pour la sélection de médias
```

### Contrôleurs
```
eshopcms/src/Controller/Admin/
├── EditorDemoController.php  # Démonstrations de l'éditeur
└── MediaController.php       # API REST médias (déjà existant)
```

### Templates
```
eshopcms/templates/admin/editor-demo/
├── index.html.twig         # Page d'accueil des démonstrations
├── basic.html.twig         # Exemple simple
├── advanced.html.twig      # Exemple avec sauvegarde automatique
├── programmatic.html.twig  # Contrôle JavaScript
└── v2-showcase.html.twig   # Vitrine des nouvelles fonctionnalités v2.0
```

## 🔧 Configuration et Utilisation

### 1. Utilisation dans les Formulaires Symfony

#### Exemple Simple
```php
use App\Form\Type\MediaTextareaType;

$builder->add('content', MediaTextareaType::class, [
    'label' => 'Contenu',
    'enable_media' => true,
    'enable_editor' => true,
    'editor_height' => 400
]);
```

#### Exemple Avancé avec Sauvegarde Automatique
```php
$builder->add('content', MediaTextareaType::class, [
    'label' => 'Contenu principal',
    'enable_media' => true,
    'enable_editor' => true,
    'editor_height' => 500,
    'attr' => [
        'placeholder' => 'Tapez votre contenu...',
        'data-enable-auto-save' => 'true',
        'data-auto-save-interval' => '30000' // 30 secondes
    ]
]);
```

### 2. Utilisation Manuelle en JavaScript

#### Initialisation de Base
```javascript
$('#mon-textarea').customEditor();
```

#### Configuration Avancée
```javascript
$('#mon-textarea').customEditor({
    height: 400,
    enableMedia: true,
    enableAutoSave: true,
    autoSaveInterval: 30000,
    enableFullscreen: true,
    enableWordCount: true,
    enableImageResize: true,    // NOUVEAU v2.0
    enableDragDrop: true,       // NOUVEAU v2.0
    toolbar: [
        'bold', 'italic', 'underline', '|',
        'h1', 'h2', 'h3', '|',
        'link', 'unlink', '|',
        'image', 'media', '|',
        'fullscreen', 'source', 'wordcount'
    ],
    onChange: function(content) {
        console.log('Contenu modifié:', content);
    },
    onAutoSave: function(content) {
        // Logique de sauvegarde personnalisée
    },
    onImageResize: function(image, width, height) {  // NOUVEAU v2.0
        console.log('Image redimensionnée:', width, height);
    }
});
```

### 3. Contrôle Programmatique

```javascript
// Obtenir l'instance de l'éditeur
const editor = $('#mon-textarea').data('customEditor');

// Méthodes disponibles
editor.setContent('<p>Nouveau contenu</p>');
editor.getContent();
editor.insertHTML('<b>Texte en gras</b>');
editor.focus();
editor.toggleFullscreen();
editor.toggleSourceMode();
editor.showWordCount();
editor.destroy();
```

## 🔌 API REST Médias

L'API REST pour les médias est déjà intégrée via le `MediaController` existant :

### Endpoints Disponibles
- `GET /admin/media/list` - Lister les médias avec pagination
- `POST /admin/media/upload` - Upload d'un fichier
- `POST /admin/media/multi-upload` - Upload multiple
- `DELETE /admin/media/{id}/delete` - Supprimer un média
- `PUT /admin/media/{id}/update` - Mettre à jour un média

### Exemple d'Utilisation
```javascript
// Charger la liste des médias
$.get('/admin/media/list', {
    page: 1,
    search: 'image',
    type: 'image'
}).done(function(response) {
    console.log(response.medias);
});
```

## 🎨 Personnalisation CSS

L'éditeur supporte une personnalisation CSS avancée :

```css
/* Personnaliser l'apparence de l'éditeur */
.custom-editor .editor-toolbar {
    background: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

.custom-editor .editor-content {
    min-height: 300px;
    padding: 15px;
    border: 1px solid #dee2e6;
}

/* Mode sombre */
[data-bs-theme="dark"] .custom-editor .editor-toolbar {
    background: var(--bs-dark);
    border-color: var(--bs-border-color);
}

/* NOUVEAU v2.0: Styles pour le redimensionnement d'images */
.editor-content img.selected {
    box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.25);
    outline: 2px solid #0d6efd;
}

.image-resize-handle {
    position: absolute;
    width: 12px;
    height: 12px;
    background: #0d6efd;
    border: 2px solid white;
    border-radius: 50%;
    cursor: se-resize;
}
```

## 📱 Démonstrations Intégrées

Accédez aux démonstrations via l'interface d'administration :

1. **Page d'accueil** : `/admin/editor-demo/`
2. **Exemple simple** : `/admin/editor-demo/basic`
3. **Exemple avancé** : `/admin/editor-demo/advanced`
4. **Contrôle programmatique** : `/admin/editor-demo/programmatic`
5. **Vitrine v2.0** : `/admin/editor-demo/v2-showcase`

## 🆕 Nouvelles Fonctionnalités v2.0

### Redimensionnement d'Images Interactif
- Sélection d'images en cliquant dessus
- Poignées de redimensionnement visuelles
- Contraintes min/max configurables
- Feedback visuel en temps réel

### Drag & Drop Amélioré
- Support du glisser-déposer de fichiers
- Overlay visuel pendant le drag
- Upload automatique des fichiers déposés

### Performance Optimisée
- Debouncing des événements de changement
- Injection centralisée des styles CSS
- Gestion mémoire améliorée

## 🔧 Configuration Webpack

L'éditeur est automatiquement intégré via `assets/admin.js` :

```javascript
// Import des composants
import './js/components/media-picker.js';
import './js/components/custom-editor.js';
import './js/components/media-selector.js';

// Initialisation automatique
$(document).ready(function() {
    $('textarea.custom-editor').each(function() {
        const $textarea = $(this);
        const enableMedia = $textarea.data('enable-media') === true;
        const height = $textarea.data('editor-height') || 300;
        
        $textarea.customEditor({
            height: height,
            enableMedia: enableMedia,
            placeholder: $textarea.attr('placeholder') || 'Tapez votre contenu ici...'
        });
    });
});
```

## 🎯 Raccourcis Clavier

L'éditeur supporte les raccourcis clavier suivants :

- `Ctrl+S` : Sauvegarde manuelle
- `Ctrl+Shift+F` : Basculer en mode plein écran
- `Ctrl+Shift+U` : Basculer en mode source
- `Tab` : Indentation
- `Escape` : Désélectionner les images

## 💡 Bonnes Pratiques

### Performance
1. **Lazy Loading** : Charger l'éditeur uniquement quand nécessaire
2. **Debouncing** : Limiter la fréquence des événements onChange
3. **Compression** : Utiliser Webpack Encore pour optimiser les assets

### Utilisation
1. **Hauteur adaptée** : Configurer la hauteur selon le contexte
2. **Placeholder pertinent** : Guider l'utilisateur avec des placeholders clairs
3. **Sauvegarde automatique** : Activer pour les contenus longs

### Accessibilité
1. **Labels explicites** : Utiliser des labels clairs pour les FormTypes
2. **Contrôles clavier** : Tous les contrôles sont accessibles au clavier
3. **Feedback visuel** : Indicateurs visuels pour toutes les actions

## 🚨 Dépannage

### Problèmes Courants

1. **L'éditeur ne s'initialise pas**
   ```javascript
   // Vérifier que jQuery et Bootstrap sont chargés
   if (typeof $ === 'undefined') {
       console.error('jQuery non trouvé');
   }
   ```

2. **Problèmes de médias**
   - Vérifier les permissions d'upload
   - Vérifier les routes du MediaController
   - Vérifier la configuration CSRF si activée

3. **Styles CSS manquants**
   - S'assurer que `assets/admin.js` est chargé
   - Vérifier que Webpack Encore compile correctement

## 🎉 Conclusion

L'intégration de l'**Custom Editor v2.0** dans ESHOPCMS est maintenant **100% OPÉRATIONNELLE** ! 

### Résumé des Ajouts
- ✅ **3 composants JavaScript** intégrés
- ✅ **2 FormTypes Symfony** fonctionnels
- ✅ **1 contrôleur de démonstration** opérationnel
- ✅ **5 templates de démonstration** disponibles
- ✅ **API REST complète** déjà en place
- ✅ **Navigation admin** mise à jour

### Prochaines Étapes Suggérées
1. Tester les démonstrations dans l'interface admin
2. Intégrer l'éditeur dans vos formulaires existants
3. Personnaliser les styles selon votre charte graphique
4. Configurer la sauvegarde automatique selon vos besoins

**🎯 L'éditeur de texte avancé est maintenant prêt à être utilisé dans tous vos formulaires ESHOPCMS !**

---

**Auteur**: MiniMax Agent  
**Date d'intégration**: 29 septembre 2025  
**Version de l'éditeur**: Custom Editor v2.0  
**Projet**: ESHOPCMS - Branche dev-chrome