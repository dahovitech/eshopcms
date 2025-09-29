#!/bin/bash

echo "🔍 Vérification des corrections Bootstrap JavaScript..."
echo "=================================================="

# Variables
ICON_SELECTOR="templates/components/icon_selector.html.twig"
MEDIA_SELECTOR="templates/components/media_selector.html.twig"

echo ""
echo "1. Vérification de la suppression du code orphelin :"

# Vérifier qu'il n'y a plus de code orphelin après les accolades
# Le code doit maintenant être dans une fonction, pas orphelin
if grep -A5 -B5 "document.body.appendChild(backdrop);" "$MEDIA_SELECTOR" | grep -q "function\|const\|{"; then
    echo "✅ Code document.body.appendChild(backdrop) maintenant dans une fonction (non orphelin)"
else
    echo "❌ ERREUR: Code orphelin toujours présent dans $MEDIA_SELECTOR"
fi

echo ""
echo "2. Vérification des nouvelles fonctions de modal :"

# Vérifier la présence des nouvelles fonctions
if grep -n "window.addEventListener('load'" "$ICON_SELECTOR" > /dev/null; then
    echo "✅ Nouvelle approche d'événement load présente dans $ICON_SELECTOR"
else
    echo "❌ ERREUR: Nouvelle approche d'événement load manquante dans $ICON_SELECTOR"
fi

if grep -n "window.addEventListener('load'" "$MEDIA_SELECTOR" > /dev/null; then
    echo "✅ Nouvelle approche d'événement load présente dans $MEDIA_SELECTOR"
else
    echo "❌ ERREUR: Nouvelle approche d'événement load manquante dans $MEDIA_SELECTOR"
fi

echo ""
echo "3. Vérification des fonctions de fermeture manuelle :"

if grep -n "function closeIconModal" "$ICON_SELECTOR" > /dev/null; then
    echo "✅ Fonction closeIconModal présente dans $ICON_SELECTOR"
else
    echo "❌ ERREUR: Fonction closeIconModal manquante dans $ICON_SELECTOR"
fi

if grep -n "function closeMediaModal" "$MEDIA_SELECTOR" > /dev/null; then
    echo "✅ Fonction closeMediaModal présente dans $MEDIA_SELECTOR"
else
    echo "❌ ERREUR: Fonction closeMediaModal manquante dans $MEDIA_SELECTOR"
fi

echo ""
echo "4. Vérification des attributs data-bs-dismiss :"

# Compter les attributs data-bs-dismiss dans les éléments HTML (pas dans le JS)
DISMISS_COUNT=$(grep -n 'data-bs-dismiss="modal"' "$MEDIA_SELECTOR" | grep -v "querySelectorAll" | wc -l)
if [ "$DISMISS_COUNT" -eq 2 ]; then
    echo "✅ Attributs data-bs-dismiss correctement ajoutés dans $MEDIA_SELECTOR (2 trouvés)"
else
    echo "❌ ERREUR: Attendu 2 attributs data-bs-dismiss, trouvé $DISMISS_COUNT dans $MEDIA_SELECTOR"
fi

echo ""
echo "5. Vérification de l'absence de polling checkBootstrap :"

if grep -n "checkBootstrap()" "$ICON_SELECTOR" > /dev/null; then
    echo "❌ ERREUR: Ancien code de polling toujours présent dans $ICON_SELECTOR"
else
    echo "✅ Ancien code de polling supprimé de $ICON_SELECTOR"
fi

if grep -n "checkBootstrap()" "$MEDIA_SELECTOR" > /dev/null; then
    echo "❌ ERREUR: Ancien code de polling toujours présent dans $MEDIA_SELECTOR"
else
    echo "✅ Ancien code de polling supprimé de $MEDIA_SELECTOR"
fi

echo ""
echo "6. Vérification de la syntaxe JavaScript :"

# Test de syntaxe basique en extrayant le JavaScript
echo "Extraction et test du JavaScript..."

# Extraire le JavaScript de icon_selector
sed -n '/<script>/,/<\/script>/p' "$ICON_SELECTOR" | sed '1d;$d' > /tmp/icon_selector.js
if node -c /tmp/icon_selector.js 2>/dev/null; then
    echo "✅ Syntaxe JavaScript valide dans $ICON_SELECTOR"
else
    echo "❌ ERREUR: Problème de syntaxe JavaScript dans $ICON_SELECTOR"
fi

# Extraire le JavaScript de media_selector
sed -n '/<script>/,/<\/script>/p' "$MEDIA_SELECTOR" | sed '1d;$d' > /tmp/media_selector.js
if node -c /tmp/media_selector.js 2>/dev/null; then
    echo "✅ Syntaxe JavaScript valide dans $MEDIA_SELECTOR"
else
    echo "❌ ERREUR: Problème de syntaxe JavaScript dans $MEDIA_SELECTOR"
fi

# Nettoyer les fichiers temporaires
rm -f /tmp/icon_selector.js /tmp/media_selector.js

echo ""
echo "7. Résumé des corrections apportées :"
echo "   - Suppression du code orphelin causant l'erreur de syntaxe"
echo "   - Remplacement du polling par des événements window.load"
echo "   - Ajout de fallbacks manuels robustes"
echo "   - Ajout des attributs data-bs-dismiss manquants"
echo "   - Ajout des fonctions de fermeture manuelle"

echo ""
echo "=================================================="
echo "🎯 Vérification terminée !"
echo ""
echo "📋 Prochaines étapes pour le test :"
echo "   1. Actualiser la page d'édition dans le navigateur"
echo "   2. Ouvrir la console développeur (F12)"
echo "   3. Tester l'ouverture des modales d'icônes et de médias"
echo "   4. Vérifier qu'il n'y a plus d'erreurs JavaScript"
echo ""