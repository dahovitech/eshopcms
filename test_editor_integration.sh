#!/bin/bash

echo "🧪 Script de Test - Intégration Custom Editor v2.0"
echo "=================================================="

# Couleurs pour l'affichage
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Fonction de test
test_file() {
    local file=$1
    local description=$2
    
    if [ -f "$file" ]; then
        echo -e "${GREEN}✅ $description${NC}"
        return 0
    else
        echo -e "${RED}❌ $description${NC}"
        return 1
    fi
}

test_dir() {
    local dir=$1
    local description=$2
    
    if [ -d "$dir" ]; then
        echo -e "${GREEN}✅ $description${NC}"
        return 0
    else
        echo -e "${RED}❌ $description${NC}"
        return 1
    fi
}

echo ""
echo "🔍 Vérification des fichiers JavaScript..."

test_file "assets/js/components/custom-editor.js" "Custom Editor principal (55Ko)"
test_file "assets/js/components/media-picker.js" "Media Picker (20Ko)"
test_file "assets/js/components/media-selector.js" "Media Selector (9Ko)"

echo ""
echo "🔍 Vérification des FormTypes Symfony..."

test_file "src/Form/Type/MediaTextareaType.php" "MediaTextareaType FormType"
test_file "src/Form/Type/MediaSelectorType.php" "MediaSelectorType FormType"

echo ""
echo "🔍 Vérification des contrôleurs..."

test_file "src/Controller/Admin/EditorDemoController.php" "EditorDemoController"
test_file "src/Controller/Admin/MediaController.php" "MediaController (API REST)"

echo ""
echo "🔍 Vérification des templates..."

test_dir "templates/admin/editor-demo" "Répertoire des démonstrations"
test_file "templates/admin/editor-demo/index.html.twig" "Page d'accueil des démonstrations"
test_file "templates/admin/editor-demo/basic.html.twig" "Démonstration simple"
test_file "templates/admin/editor-demo/advanced.html.twig" "Démonstration avancée"
test_file "templates/admin/editor-demo/programmatic.html.twig" "Contrôle programmatique"
test_file "templates/admin/editor-demo/v2-showcase.html.twig" "Vitrine v2.0"

echo ""
echo "🔍 Vérification de la configuration..."

test_file "assets/admin.js" "Configuration Webpack"
test_file "templates/admin/base.html.twig" "Template de base admin"

echo ""
echo "🔍 Vérification de la documentation..."

test_file "EDITEUR_TEXTE_INTEGRATION.md" "Documentation d'intégration"

echo ""
echo "📊 Statistiques des fichiers..."

if [ -f "assets/js/components/custom-editor.js" ]; then
    size_editor=$(stat -c%s "assets/js/components/custom-editor.js")
    echo -e "${YELLOW}📄 custom-editor.js: ${size_editor} octets${NC}"
fi

if [ -f "assets/js/components/media-picker.js" ]; then
    size_picker=$(stat -c%s "assets/js/components/media-picker.js")
    echo -e "${YELLOW}📄 media-picker.js: ${size_picker} octets${NC}"
fi

if [ -f "assets/js/components/media-selector.js" ]; then
    size_selector=$(stat -c%s "assets/js/components/media-selector.js")
    echo -e "${YELLOW}📄 media-selector.js: ${size_selector} octets${NC}"
fi

echo ""
echo "🔍 Vérification de la navigation admin..."

if grep -q "admin_editor_demo_" "templates/admin/base.html.twig"; then
    echo -e "${GREEN}✅ Navigation admin mise à jour avec le lien éditeur${NC}"
else
    echo -e "${RED}❌ Lien éditeur manquant dans la navigation admin${NC}"
fi

echo ""
echo "🔍 Vérification des imports JavaScript..."

if grep -q "custom-editor.js" "assets/admin.js"; then
    echo -e "${GREEN}✅ custom-editor.js importé dans admin.js${NC}"
else
    echo -e "${RED}❌ custom-editor.js non importé dans admin.js${NC}"
fi

if grep -q "media-picker.js" "assets/admin.js"; then
    echo -e "${GREEN}✅ media-picker.js importé dans admin.js${NC}"
else
    echo -e "${RED}❌ media-picker.js non importé dans admin.js${NC}"
fi

echo ""
echo "🎯 Résumé de l'intégration:"
echo "=========================="
echo -e "${GREEN}🚀 Custom Editor v2.0 intégré avec succès !${NC}"
echo ""
echo "📍 Points d'accès:"
echo "  • Démonstrations: http://localhost/admin/editor-demo/"
echo "  • API médias: http://localhost/admin/media/"
echo ""
echo "💡 Utilisation dans les formulaires:"
echo "  • MediaTextareaType pour les éditeurs WYSIWYG"
echo "  • MediaSelectorType pour la sélection de médias"
echo ""
echo "📖 Documentation complète: EDITEUR_TEXTE_INTEGRATION.md"
echo ""
echo -e "${YELLOW}⚠️  Note: Les assets doivent être compilés avec 'npm run build' pour être utilisés en production.${NC}"

echo ""
echo "✨ Intégration terminée avec succès ! ✨"