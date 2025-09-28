<?php

namespace App\Entity;

use App\Repository\ProductVariantRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProductVariantRepository::class)]
#[ORM\Table(name: 'product_variants')]
class ProductVariant
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Product::class, inversedBy: 'variants')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Product $product = null;

    #[ORM\Column(type: 'string', length: 100, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    private ?string $sku = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    #[Assert\PositiveOrZero]
    private ?string $price = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    #[Assert\PositiveOrZero]
    private ?string $compareAtPrice = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    #[Assert\PositiveOrZero]
    private ?string $costPrice = null;

    #[ORM\Column(type: 'integer')]
    #[Assert\PositiveOrZero]
    private int $stock = 0;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Assert\PositiveOrZero]
    private ?int $lowStockThreshold = null;

    #[ORM\Column(type: 'boolean')]
    private bool $trackStock = true;

    #[ORM\Column(type: 'boolean')]
    private bool $isActive = true;

    #[ORM\Column(type: 'decimal', precision: 8, scale: 3, nullable: true)]
    #[Assert\PositiveOrZero]
    private ?string $weight = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $dimensions = null;

    #[ORM\Column(type: 'integer')]
    private int $sortOrder = 0;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $updatedAt;

    #[ORM\OneToMany(mappedBy: 'productVariant', targetEntity: ProductVariantTranslation::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $translations;

    #[ORM\ManyToMany(targetEntity: AttributeValue::class, inversedBy: 'productVariants')]
    #[ORM\JoinTable(name: 'product_variant_attribute_values')]
    private Collection $attributeValues;

    #[ORM\ManyToMany(targetEntity: Media::class)]
    #[ORM\JoinTable(name: 'product_variant_media')]
    private Collection $media;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
        $this->attributeValues = new ArrayCollection();
        $this->media = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): static
    {
        $this->product = $product;
        return $this;
    }

    public function getSku(): ?string
    {
        return $this->sku;
    }

    public function setSku(?string $sku): static
    {
        $this->sku = $sku;
        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(?string $price): static
    {
        $this->price = $price;
        return $this;
    }

    public function getCompareAtPrice(): ?string
    {
        return $this->compareAtPrice;
    }

    public function setCompareAtPrice(?string $compareAtPrice): static
    {
        $this->compareAtPrice = $compareAtPrice;
        return $this;
    }

    public function getCostPrice(): ?string
    {
        return $this->costPrice;
    }

    public function setCostPrice(?string $costPrice): static
    {
        $this->costPrice = $costPrice;
        return $this;
    }

    public function getStock(): int
    {
        return $this->stock;
    }

    public function setStock(int $stock): static
    {
        $this->stock = $stock;
        return $this;
    }

    public function getLowStockThreshold(): ?int
    {
        return $this->lowStockThreshold;
    }

    public function setLowStockThreshold(?int $lowStockThreshold): static
    {
        $this->lowStockThreshold = $lowStockThreshold;
        return $this;
    }

    public function isTrackStock(): bool
    {
        return $this->trackStock;
    }

    public function setTrackStock(bool $trackStock): static
    {
        $this->trackStock = $trackStock;
        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;
        return $this;
    }

    public function getWeight(): ?string
    {
        return $this->weight;
    }

    public function setWeight(?string $weight): static
    {
        $this->weight = $weight;
        return $this;
    }

    public function getDimensions(): ?array
    {
        return $this->dimensions;
    }

    public function setDimensions(?array $dimensions): static
    {
        $this->dimensions = $dimensions;
        return $this;
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function setSortOrder(int $sortOrder): static
    {
        $this->sortOrder = $sortOrder;
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(): static
    {
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    /**
     * @return Collection<int, ProductVariantTranslation>
     */
    public function getTranslations(): Collection
    {
        return $this->translations;
    }

    public function addTranslation(ProductVariantTranslation $translation): static
    {
        if (!$this->translations->contains($translation)) {
            $this->translations->add($translation);
            $translation->setProductVariant($this);
        }

        return $this;
    }

    public function removeTranslation(ProductVariantTranslation $translation): static
    {
        if ($this->translations->removeElement($translation)) {
            if ($translation->getProductVariant() === $this) {
                $translation->setProductVariant(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, AttributeValue>
     */
    public function getAttributeValues(): Collection
    {
        return $this->attributeValues;
    }

    public function addAttributeValue(AttributeValue $attributeValue): static
    {
        if (!$this->attributeValues->contains($attributeValue)) {
            $this->attributeValues->add($attributeValue);
        }

        return $this;
    }

    public function removeAttributeValue(AttributeValue $attributeValue): static
    {
        $this->attributeValues->removeElement($attributeValue);
        return $this;
    }

    /**
     * @return Collection<int, Media>
     */
    public function getMedia(): Collection
    {
        return $this->media;
    }

    public function addMedia(Media $media): static
    {
        if (!$this->media->contains($media)) {
            $this->media->add($media);
        }

        return $this;
    }

    public function removeMedia(Media $media): static
    {
        $this->media->removeElement($media);
        return $this;
    }

    /**
     * Get translation for a specific language
     */
    public function getTranslation(?string $languageCode = null): ?ProductVariantTranslation
    {
        if ($languageCode === null) {
            return $this->translations->first() ?: null;
        }

        foreach ($this->translations as $translation) {
            if ($translation->getLanguage()->getCode() === $languageCode) {
                return $translation;
            }
        }

        return null;
    }

    /**
     * Get translation with fallback
     */
    public function getTranslationWithFallback(string $languageCode, string $fallbackLanguageCode = 'fr'): ?ProductVariantTranslation
    {
        $translation = $this->getTranslation($languageCode);
        
        if (!$translation) {
            $translation = $this->getTranslation($fallbackLanguageCode);
        }

        return $translation;
    }

    /**
     * Get name for a specific language with fallback
     */
    public function getName(string $languageCode = 'fr', string $fallbackLanguageCode = 'fr'): string
    {
        $translation = $this->getTranslationWithFallback($languageCode, $fallbackLanguageCode);
        if ($translation && !empty($translation->getName())) {
            return $translation->getName();
        }
        
        // Fallback to product name with variant attributes
        $productName = $this->product?->getName($languageCode, $fallbackLanguageCode) ?? 'Untitled Product';
        $attributeNames = [];
        
        foreach ($this->attributeValues as $attributeValue) {
            $attributeNames[] = $attributeValue->getName($languageCode, $fallbackLanguageCode);
        }
        
        if (!empty($attributeNames)) {
            return $productName . ' - ' . implode(', ', $attributeNames);
        }
        
        return $productName;
    }

    /**
     * Get description for a specific language with fallback
     */
    public function getDescription(string $languageCode = 'fr', string $fallbackLanguageCode = 'fr'): string
    {
        $translation = $this->getTranslationWithFallback($languageCode, $fallbackLanguageCode);
        return $translation ? $translation->getDescription() : '';
    }

    /**
     * Check if variant has translation for a specific language
     */
    public function hasTranslation(string $languageCode): bool
    {
        return $this->getTranslation($languageCode) !== null;
    }

    /**
     * Get effective price (variant price or product price)
     */
    public function getEffectivePrice(): ?string
    {
        return $this->price ?: $this->product?->getPrice();
    }

    /**
     * Get effective compare at price
     */
    public function getEffectiveCompareAtPrice(): ?string
    {
        return $this->compareAtPrice ?: $this->product?->getCompareAtPrice();
    }

    /**
     * Get effective cost price
     */
    public function getEffectiveCostPrice(): ?string
    {
        return $this->costPrice ?: $this->product?->getCostPrice();
    }

    /**
     * Get effective weight
     */
    public function getEffectiveWeight(): ?string
    {
        return $this->weight ?: $this->product?->getWeight();
    }

    /**
     * Get effective dimensions
     */
    public function getEffectiveDimensions(): ?array
    {
        return $this->dimensions ?: $this->product?->getDimensions();
    }

    /**
     * Check if variant is in stock
     */
    public function isInStock(): bool
    {
        if (!$this->trackStock) {
            return true;
        }

        return $this->stock > 0;
    }

    /**
     * Check if variant is low in stock
     */
    public function isLowStock(): bool
    {
        if (!$this->trackStock) {
            return false;
        }
        
        $threshold = $this->lowStockThreshold ?: $this->product?->getLowStockThreshold() ?: 0;
        return $this->stock <= $threshold;
    }

    /**
     * Get primary media (variant media or product media)
     */
    public function getPrimaryMedia(): ?Media
    {
        return $this->media->first() ?: $this->product?->getPrimaryMedia();
    }

    /**
     * Get attribute value by attribute code
     */
    public function getAttributeValueByCode(string $attributeCode): ?AttributeValue
    {
        foreach ($this->attributeValues as $attributeValue) {
            if ($attributeValue->getAttribute()->getCode() === $attributeCode) {
                return $attributeValue;
            }
        }
        
        return null;
    }

    public function __toString(): string
    {
        return $this->getName();
    }
}