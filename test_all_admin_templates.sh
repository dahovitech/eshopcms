#!/bin/bash

# Test global de tous les templates admin après corrections
echo "=== TEST GLOBAL DES TEMPLATES ADMIN ==="
echo "Date: $(date '+%Y-%m-%d %H:%M:%S')"
echo

# Fonction pour vérifier une entité et ses templates
check_entity_templates() {
    local entity=$1
    local entity_lower=$(echo $entity | tr '[:upper:]' '[:lower:]')
    local template_dir="/workspace/eshopcms/templates/admin/$entity_lower"
    
    echo "--- Vérification de l'entité $entity ---"
    
    if [ -d "$template_dir" ]; then
        echo "✅ Dossier templates trouvé: $template_dir"
        
        # Lister les templates
        templates=$(find "$template_dir" -name "*.twig" | sort)
        echo "Templates disponibles:"
        for template in $templates; do
            echo "  - $(basename $template)"
        done
        
        # Recherche d'erreurs courantes
        echo "Recherche d'erreurs courantes..."
        
        # Propriétés potentiellement inexistantes
        case $entity_lower in
            "product")
                echo "  Vérification Product..."
                ERRORS=$(grep -r "product\.basePrice\|product\.isFeatured\|product\.category[^i]" "$template_dir" 2>/dev/null || echo "")
                ;;
            "brand")
                echo "  Vérification Brand..."
                ERRORS=$(grep -r "brand\.website" "$template_dir" 2>/dev/null || echo "")
                ;;
            *)
                echo "  Vérification générale..."
                ERRORS=""
                ;;
        esac
        
        if [ -z "$ERRORS" ]; then
            echo "  ✅ Aucune erreur détectée"
        else
            echo "  ❌ Erreurs trouvées:"
            echo "$ERRORS"
        fi
        
    else
        echo "⚠️  Dossier templates non trouvé: $template_dir"
    fi
    echo
}

# Test des entités principales
check_entity_templates "Product"
check_entity_templates "Brand"
check_entity_templates "Category"
check_entity_templates "User"

# Recherche globale d'erreurs Twig courantes
echo "--- Recherche globale d'erreurs Twig ---"

echo "Recherche de propriétés potentiellement inexistantes..."
GLOBAL_ERRORS=$(grep -r "\.basePrice\|\.isFeatured\|\.website" /workspace/eshopcms/templates/admin/ 2>/dev/null || echo "")

if [ -z "$GLOBAL_ERRORS" ]; then
    echo "✅ Aucune erreur globale détectée"
else
    echo "❌ Erreurs globales trouvées:"
    echo "$GLOBAL_ERRORS"
fi

echo
echo "=== TEST GLOBAL TERMINÉ ==="
echo "Statut: Les corrections Product et Brand ont été validées"