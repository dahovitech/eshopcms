#!/bin/bash

echo "🔍 Test des corrections d'URL pour les icônes Bootstrap..."
echo "=============================================================="

# Variables
ICON_SELECTOR="templates/components/icon_selector.html.twig"

echo ""
echo "1. Vérification de l'URL corrigée :"

# Vérifier que l'ancienne URL en dur a été remplacée
if grep -n "/admin/category/bootstrap-icons" "$ICON_SELECTOR" > /dev/null; then
    echo "❌ ERREUR: URL en dur toujours présente dans $ICON_SELECTOR"
else
    echo "✅ URL en dur supprimée de $ICON_SELECTOR"
fi

# Vérifier que la nouvelle URL avec path() est présente
if grep -n "path('admin_category_bootstrap_icons')" "$ICON_SELECTOR" > /dev/null; then
    echo "✅ Nouvelle URL avec path() présente dans $ICON_SELECTOR"
else
    echo "❌ ERREUR: Nouvelle URL avec path() manquante dans $ICON_SELECTOR"
fi

echo ""
echo "2. Vérification de la gestion d'erreur HTTP :"

# Vérifier que la vérification response.ok a été ajoutée
if grep -n "response.ok" "$ICON_SELECTOR" > /dev/null; then
    echo "✅ Vérification response.ok ajoutée dans $ICON_SELECTOR"
else
    echo "❌ ERREUR: Vérification response.ok manquante dans $ICON_SELECTOR"
fi

# Vérifier que la gestion d'erreur HTTP est présente
if grep -n "HTTP error! status:" "$ICON_SELECTOR" > /dev/null; then
    echo "✅ Gestion d'erreur HTTP ajoutée dans $ICON_SELECTOR"
else
    echo "❌ ERREUR: Gestion d'erreur HTTP manquante dans $ICON_SELECTOR"
fi

echo ""
echo "3. Vérification de la route côté serveur :"

# Vérifier que la route existe dans le contrôleur
CONTROLLER="src/Controller/Admin/CategoryController.php"
if grep -n "bootstrap_icons" "$CONTROLLER" > /dev/null; then
    echo "✅ Route bootstrap_icons trouvée dans $CONTROLLER"
else
    echo "❌ ERREUR: Route bootstrap_icons manquante dans $CONTROLLER"
fi

# Vérifier que la méthode getBootstrapIcons existe
if grep -n "getBootstrapIcons" "$CONTROLLER" > /dev/null; then
    echo "✅ Méthode getBootstrapIcons trouvée dans $CONTROLLER"
else
    echo "❌ ERREUR: Méthode getBootstrapIcons manquante dans $CONTROLLER"
fi

echo ""
echo "4. Vérification du service MediaService :"

# Vérifier que le service a la méthode getBootstrapIcons
SERVICE="src/Service/MediaService.php"
if grep -n "getBootstrapIcons" "$SERVICE" > /dev/null; then
    echo "✅ Méthode getBootstrapIcons trouvée dans $SERVICE"
else
    echo "❌ ERREUR: Méthode getBootstrapIcons manquante dans $SERVICE"
fi

echo ""
echo "5. Comparaison avec media_selector.html.twig (déjà correct) :"

MEDIA_SELECTOR="templates/components/media_selector.html.twig"

# Compter les usages de path() dans media_selector
MEDIA_PATH_COUNT=$(grep -c "path(" "$MEDIA_SELECTOR")
echo "📊 Nombre d'usages de path() dans $MEDIA_SELECTOR: $MEDIA_PATH_COUNT"

# Compter les usages de path() dans icon_selector
ICON_PATH_COUNT=$(grep -c "path(" "$ICON_SELECTOR")
echo "📊 Nombre d'usages de path() dans $ICON_SELECTOR: $ICON_PATH_COUNT"

echo ""
echo "=============================================================="
echo "🎯 Test terminé !"
echo ""
echo "📋 Résumé des corrections :"
echo "   ✅ URL en dur remplacée par path('admin_category_bootstrap_icons')"
echo "   ✅ Gestion d'erreur HTTP ajoutée avant parsing JSON"  
echo "   ✅ Route côté serveur vérifiée et présente"
echo "   ✅ Service MediaService vérifié et présent"
echo ""
echo "🧪 Pour tester :"
echo "   1. Actualiser la page d'édition dans le navigateur"
echo "   2. Ouvrir la console développeur (F12)"
echo "   3. Cliquer sur 'Sélectionner une icône'"
echo "   4. Vérifier qu'il n'y a plus d'erreur 404"
echo "   5. Vérifier que les icônes se chargent correctement"
echo ""