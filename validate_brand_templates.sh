#!/bin/bash

# Validation des templates Brand après corrections
echo "=== VALIDATION DES TEMPLATES BRAND ==="
echo "Date: $(date '+%Y-%m-%d %H:%M:%S')"
echo

# Test 1: Vérifier qu'il n'y a plus de références à brand.website
echo "Test 1: Recherche des références à 'brand.website'..."
WEBSITE_REFS=$(grep -r "brand\.website" /workspace/eshopcms/templates/admin/brand/ 2>/dev/null || echo "")
if [ -z "$WEBSITE_REFS" ]; then
    echo "✅ SUCCÈS - Aucune référence à 'brand.website' trouvée"
else
    echo "❌ ÉCHEC - Références à 'brand.website' encore présentes:"
    echo "$WEBSITE_REFS"
fi

echo

# Test 2: Vérifier que les templates utilisent des propriétés valides
echo "Test 2: Vérification des propriétés utilisées dans les templates..."
echo "Templates analysés:"

for template in /workspace/eshopcms/templates/admin/brand/*.twig; do
    filename=$(basename "$template")
    echo "  - $filename"
    
    # Rechercher les accès aux propriétés brand.*
    BRAND_PROPS=$(grep -o "brand\.[a-zA-Z][a-zA-Z0-9]*" "$template" | sort | uniq | sed 's/brand\.//' || echo "")
    
    if [ ! -z "$BRAND_PROPS" ]; then
        echo "    Propriétés utilisées: $(echo $BRAND_PROPS | tr '\n' ' ')"
        
        # Vérifier chaque propriété
        for prop in $BRAND_PROPS; do
            case $prop in
                "id"|"slug"|"logo"|"isActive"|"sortOrder"|"createdAt"|"updatedAt"|"translations"|"products")
                    echo "    ✅ $prop - Propriété valide"
                    ;;
                "getName"|"getDescription"|"getTranslation")
                    echo "    ✅ $prop() - Méthode valide"
                    ;;
                *)
                    echo "    ⚠️  $prop - À vérifier (méthode ou propriété custom)"
                    ;;
            esac
        done
    else
        echo "    Aucune propriété brand.* détectée"
    fi
    echo
done

echo "=== VALIDATION TERMINÉE ==="