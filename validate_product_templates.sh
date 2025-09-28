#!/bin/bash

echo "=== VALIDATION DES TEMPLATES PRODUIT ==="
echo ""

# Recherche des propriétés potentiellement problématiques
echo "1. Vérification des références à 'basePrice'..."
grep -r "basePrice" templates/admin/product/ || echo "✅ Aucune référence à 'basePrice' trouvée"
echo ""

echo "2. Vérification des références à 'isFeatured'..."
grep -r "isFeatured" templates/admin/product/ || echo "✅ Aucune référence à 'isFeatured' trouvée"
echo ""

echo "3. Vérification des références à 'product.category' (sans 's')..."
grep -r "product\.category[^.]" templates/admin/product/ || echo "✅ Aucune référence à 'product.category' trouvée"
echo ""

echo "4. Vérification de la syntaxe Twig..."
# Cette vérification nécessiterait l'installation de Twig CLI, 
# mais nous pouvons au moins vérifier les balises de base
echo "Vérification des balises fermantes..."
for file in templates/admin/product/*.html.twig; do
    if [ -f "$file" ]; then
        filename=$(basename "$file")
        echo "  Vérification de $filename..."
        
        # Vérifier si toutes les balises {% if %} ont leur {% endif %}
        if_count=$(grep -c "{% if" "$file")
        endif_count=$(grep -c "{% endif %}" "$file")
        
        if [ "$if_count" -eq "$endif_count" ]; then
            echo "    ✅ $filename: Balises {% if %}/{% endif %} équilibrées ($if_count/$endif_count)"
        else
            echo "    ❌ $filename: Balises {% if %}/{% endif %} déséquilibrées ($if_count/$endif_count)"
        fi
        
        # Vérifier si toutes les balises {% for %} ont leur {% endfor %}
        for_count=$(grep -c "{% for" "$file")
        endfor_count=$(grep -c "{% endfor %}" "$file")
        
        if [ "$for_count" -eq "$endfor_count" ]; then
            echo "    ✅ $filename: Balises {% for %}/{% endfor %} équilibrées ($for_count/$endfor_count)"
        else
            echo "    ❌ $filename: Balises {% for %}/{% endfor %} déséquilibrées ($for_count/$endfor_count)"
        fi
    fi
done
echo ""

echo "5. Vérification du contrôleur ProductController..."
echo "Vérification des références à 'isFeatured' dans le contrôleur..."
grep -n "isFeatured" src/Controller/Admin/ProductController.php || echo "✅ Aucune référence à 'isFeatured' dans le contrôleur"
echo ""

echo "=== VALIDATION TERMINÉE ==="