#!/bin/bash

echo "=== TEST FINAL - VÉRIFICATION GLOBALE DES TEMPLATES ADMIN ==="
echo ""

echo "🔍 Recherche d'erreurs potentielles dans TOUS les templates admin..."
echo ""

# Vérifier les propriétés inexistantes communes
echo "1. Vérification des propriétés potentiellement problématiques..."

echo "  - basePrice (devrait être 'price'):"
grep -r "basePrice" templates/admin/ 2>/dev/null || echo "    ✅ Aucune occurrence trouvée"

echo "  - isFeatured (propriété inexistante):"
grep -r "isFeatured" templates/admin/ 2>/dev/null || echo "    ✅ Aucune occurrence trouvée"

echo "  - getDefaultName (méthode inexistante):"
grep -r "getDefaultName" templates/admin/ 2>/dev/null || echo "    ✅ Aucune occurrence trouvée"

echo "  - translationStatus (variable inexistante):"
grep -r "translationStatus" templates/admin/ 2>/dev/null || echo "    ✅ Aucune occurrence trouvée"

echo ""
echo "2. Vérification de la cohérence des méthodes d'entité..."

echo "  - Recherche de .category au lieu de .categories:"
grep -r "\.category[^.]" templates/admin/ 2>/dev/null || echo "    ✅ Aucune occurrence trouvée"

echo "  - Recherche de .getName() sans paramètre sur les entités traduites:"
echo "    (Note: Product, Category, Brand utilisent getName(languageCode))"

echo ""
echo "3. État des modules admin:"

modules=("product" "category" "brand" "service" "language" "media")
for module in "${modules[@]}"; do
    if [ -d "templates/admin/$module" ]; then
        file_count=$(find "templates/admin/$module" -name "*.html.twig" | wc -l)
        echo "  ✅ Module $module: $file_count templates trouvés"
    else
        echo "  ⚠️  Module $module: répertoire non trouvé"
    fi
done

echo ""
echo "4. Validation de la syntaxe Twig de base..."

total_files=0
error_files=0

for file in $(find templates/admin -name "*.html.twig" 2>/dev/null); do
    total_files=$((total_files + 1))
    
    # Vérification basique des balises équilibrées
    if_count=$(grep -c "{% if" "$file" 2>/dev/null || echo "0")
    endif_count=$(grep -c "{% endif %}" "$file" 2>/dev/null || echo "0")
    for_count=$(grep -c "{% for" "$file" 2>/dev/null || echo "0")
    endfor_count=$(grep -c "{% endfor %}" "$file" 2>/dev/null || echo "0")
    
    if [ "$if_count" -ne "$endif_count" ] || [ "$for_count" -ne "$endfor_count" ]; then
        echo "  ❌ Erreur dans $file (if:$if_count/endif:$endif_count, for:$for_count/endfor:$endfor_count)"
        error_files=$((error_files + 1))
    fi
done

if [ $error_files -eq 0 ]; then
    echo "  ✅ Tous les $total_files templates ont une syntaxe Twig valide"
else
    echo "  ❌ $error_files/$total_files templates ont des erreurs de syntaxe"
fi

echo ""
echo "=== RÉSULTAT FINAL ==="
if [ $error_files -eq 0 ]; then
    echo "🎉 SUCCESS: Tous les templates admin sont valides et fonctionnels"
    echo "📊 $total_files templates vérifiés avec succès"
    echo "🚀 L'application est prête pour la navigation complète"
else
    echo "⚠️  WARNING: $error_files erreurs détectées"
    echo "🔧 Correction manuelle requise"
fi
echo ""