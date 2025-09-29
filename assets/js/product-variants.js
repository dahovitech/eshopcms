/**
 * Product Variants Management JavaScript
 * Handles dynamic product variant creation, editing, and deletion
 * 
 * @author Prudence ASSOGBA
 * @version 1.0
 */

class ProductVariantManager {
    constructor() {
        this.variantIndex = 0;
        this.init();
    }

    init() {
        this.bindEvents();
        this.initializeExistingVariants();
        this.toggleVariantsSection();
    }

    bindEvents() {
        // Toggle variants section when isVariable checkbox changes
        const isVariableCheckbox = document.getElementById('isVariable');
        if (isVariableCheckbox) {
            isVariableCheckbox.addEventListener('change', () => {
                this.toggleVariantsSection();
            });
        }

        // Handle form submission to validate variants
        const productForm = document.getElementById('productForm');
        if (productForm) {
            productForm.addEventListener('submit', (e) => {
                if (!this.validateVariants()) {
                    e.preventDefault();
                }
            });
        }
    }

    initializeExistingVariants() {
        const variantCards = document.querySelectorAll('.variant-card');
        this.variantIndex = variantCards.length;
    }

    toggleVariantsSection() {
        const isVariableCheckbox = document.getElementById('isVariable');
        const variantsSection = document.querySelector('.card:has(#productVariants)');
        
        if (isVariableCheckbox && variantsSection) {
            if (isVariableCheckbox.checked) {
                variantsSection.style.display = 'block';
                this.showVariantsHelp();
            } else {
                variantsSection.style.display = 'none';
                this.clearVariants();
            }
        }
    }

    showVariantsHelp() {
        const container = document.getElementById('productVariants');
        if (container && container.children.length === 0) {
            this.showEmptyVariantsMessage();
        }
    }

    showEmptyVariantsMessage() {
        const container = document.getElementById('productVariants');
        if (container.querySelector('.alert-info')) {
            return; // Message already exists
        }

        const messageHtml = `
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i>
                <strong>Produit Variable Activé :</strong> 
                Ce produit peut maintenant avoir des variations (taille, couleur, etc.). 
                Cliquez sur "Ajouter une variation" pour commencer.
            </div>
        `;
        container.insertAdjacentHTML('afterbegin', messageHtml);
    }

    addVariant() {
        const container = document.getElementById('productVariants');
        
        // Remove empty message if it exists
        const emptyMessage = container.querySelector('.alert-info');
        if (emptyMessage) {
            emptyMessage.remove();
        }

        const newVariantHtml = this.generateVariantHtml(this.variantIndex);
        container.insertAdjacentHTML('beforeend', newVariantHtml);
        
        this.variantIndex++;
        this.scrollToVariant(this.variantIndex - 1);
    }

    generateVariantHtml(index) {
        return `
            <div class="card border-secondary mb-3 variant-card" data-variant-index="${index}">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <span class="badge bg-primary me-2">${index + 1}</span>
                        Variation ${index + 1} - <span class="variant-sku-display">Nouveau</span>
                    </h6>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="productVariantManager.removeVariant(this)">
                        <i class="bi bi-trash"></i> Supprimer
                    </button>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="variant_${index}_sku" class="form-label">SKU *</label>
                                <input type="text" class="form-control variant-sku-input" 
                                       id="variant_${index}_sku" 
                                       name="variants[${index}][sku]" 
                                       value="" 
                                       required
                                       onkeyup="productVariantManager.updateSkuDisplay(this)">
                                <div class="form-text">Code unique pour cette variation</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="variant_${index}_price" class="form-label">Prix (€)</label>
                                <input type="number" step="0.01" class="form-control" 
                                       id="variant_${index}_price" 
                                       name="variants[${index}][price]" 
                                       value=""
                                       min="0">
                                <div class="form-text">Laissez vide pour utiliser le prix du produit</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="variant_${index}_stock" class="form-label">Stock</label>
                                <input type="number" class="form-control" 
                                       id="variant_${index}_stock" 
                                       name="variants[${index}][stock]" 
                                       value="0" 
                                       min="0">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="variant_${index}_compareAtPrice" class="form-label">Prix de comparaison (€)</label>
                                <input type="number" step="0.01" class="form-control" 
                                       id="variant_${index}_compareAtPrice" 
                                       name="variants[${index}][compareAtPrice]" 
                                       value=""
                                       min="0">
                                <div class="form-text">Prix barré</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="variant_${index}_costPrice" class="form-label">Prix de revient (€)</label>
                                <input type="number" step="0.01" class="form-control" 
                                       id="variant_${index}_costPrice" 
                                       name="variants[${index}][costPrice]" 
                                       value=""
                                       min="0">
                                <div class="form-text">Pour calcul de marge</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="variant_${index}_lowStockThreshold" class="form-label">Seuil de stock faible</label>
                                <input type="number" class="form-control" 
                                       id="variant_${index}_lowStockThreshold" 
                                       name="variants[${index}][lowStockThreshold]" 
                                       value=""
                                       min="0">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="variant_${index}_weight" class="form-label">Poids (kg)</label>
                                <input type="number" step="0.001" class="form-control" 
                                       id="variant_${index}_weight" 
                                       name="variants[${index}][weight]" 
                                       value=""
                                       min="0">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="variant_${index}_sortOrder" class="form-label">Ordre d'affichage</label>
                                <input type="number" class="form-control" 
                                       id="variant_${index}_sortOrder" 
                                       name="variants[${index}][sortOrder]" 
                                       value="${index}" 
                                       min="0">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mt-4">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" 
                                           id="variant_${index}_trackStock" 
                                           name="variants[${index}][trackStock]" 
                                           checked>
                                    <label class="form-check-label" for="variant_${index}_trackStock">
                                        Suivre le stock
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" 
                                           id="variant_${index}_isActive" 
                                           name="variants[${index}][isActive]" 
                                           checked>
                                    <label class="form-check-label" for="variant_${index}_isActive">
                                        Variation active
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    removeVariant(button) {
        const variantCard = button.closest('.variant-card');
        const container = document.getElementById('productVariants');
        
        // Show confirmation dialog
        if (confirm('Êtes-vous sûr de vouloir supprimer cette variation ?')) {
            variantCard.remove();
            
            // Show empty message if no variants left
            if (container.children.length === 0) {
                this.showEmptyVariantsMessage();
            }
            
            this.renumberVariants();
        }
    }

    clearVariants() {
        const container = document.getElementById('productVariants');
        if (confirm('Désactiver les variations supprimera toutes les variations existantes. Continuer ?')) {
            container.innerHTML = '';
            this.variantIndex = 0;
        } else {
            // Re-check the isVariable checkbox
            document.getElementById('isVariable').checked = true;
        }
    }

    updateSkuDisplay(input) {
        const variantCard = input.closest('.variant-card');
        const skuDisplay = variantCard.querySelector('.variant-sku-display');
        if (skuDisplay) {
            skuDisplay.textContent = input.value || 'Nouveau';
        }
    }

    renumberVariants() {
        const variantCards = document.querySelectorAll('.variant-card');
        variantCards.forEach((card, index) => {
            // Update badge number
            const badge = card.querySelector('.badge');
            if (badge) {
                badge.textContent = index + 1;
            }
            
            // Update header text
            const headerText = card.querySelector('h6');
            if (headerText) {
                const skuDisplay = headerText.querySelector('.variant-sku-display');
                const skuText = skuDisplay ? skuDisplay.textContent : 'Nouveau';
                headerText.innerHTML = `
                    <span class="badge bg-primary me-2">${index + 1}</span>
                    Variation ${index + 1} - <span class="variant-sku-display">${skuText}</span>
                `;
            }
        });
    }

    validateVariants() {
        const isVariableCheckbox = document.getElementById('isVariable');
        if (!isVariableCheckbox || !isVariableCheckbox.checked) {
            return true; // No validation needed for non-variable products
        }

        const variantCards = document.querySelectorAll('.variant-card');
        if (variantCards.length === 0) {
            alert('Un produit variable doit avoir au moins une variation.');
            return false;
        }

        // Validate SKU uniqueness
        const skus = [];
        let hasInvalidSku = false;

        variantCards.forEach((card, index) => {
            const skuInput = card.querySelector('.variant-sku-input');
            if (!skuInput.value.trim()) {
                alert(`La variation ${index + 1} doit avoir un SKU.`);
                skuInput.focus();
                hasInvalidSku = true;
                return;
            }

            if (skus.includes(skuInput.value.trim())) {
                alert(`Le SKU "${skuInput.value}" est déjà utilisé par une autre variation.`);
                skuInput.focus();
                hasInvalidSku = true;
                return;
            }

            skus.push(skuInput.value.trim());
        });

        return !hasInvalidSku;
    }

    scrollToVariant(index) {
        setTimeout(() => {
            const variantCard = document.querySelector(`[data-variant-index="${index}"]`);
            if (variantCard) {
                variantCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }, 100);
    }
}

// Initialize the manager when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.productVariantManager = new ProductVariantManager();
});

// Global functions for backward compatibility
function addVariant() {
    if (window.productVariantManager) {
        window.productVariantManager.addVariant();
    }
}

function removeVariant(button) {
    if (window.productVariantManager) {
        window.productVariantManager.removeVariant(button);
    }
}