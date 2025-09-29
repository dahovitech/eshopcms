<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[ORM\Table(name: 'products')]
#[ORM\HasLifecycleCallbacks]
class Product
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_ARCHIVED = 'archived';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 100, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    private ?string $sku = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private ?string $slug = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Assert\NotBlank]
    #[Assert\PositiveOrZero]
    private ?string $price = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    #[Assert\PositiveOrZero]
    private ?string $compareAtPrice = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
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
    private bool $isVariable = false;

    #[ORM\Column(type: 'string', length: 20)]
    #[Assert\Choice(choices: [self::STATUS_DRAFT, self::STATUS_ACTIVE, self::STATUS_INACTIVE, self::STATUS_ARCHIVED])]
    private string $status = self::STATUS_DRAFT;

    #[ORM\Column(type: 'boolean')]
    private bool $isDigital = false;

    #[ORM\Column(type: Types::DECIMAL, precision: 8, scale: 3, nullable: true)]
    #[Assert\PositiveOrZero]
    private ?string $weight = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $dimensions = null;

    #[ORM\ManyToOne(targetEntity: Brand::class, inversedBy: 'products')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Brand $brand = null;

    #[ORM\ManyToMany(targetEntity: Category::class, inversedBy: 'products')]
    #[ORM\JoinTable(name: 'product_categories')]
    private Collection $categories;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $updatedAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $publishedAt = null;

    #[ORM\OneToMany(mappedBy: 'product', targetEntity: ProductTranslation::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $translations;

    #[ORM\OneToMany(mappedBy: 'product', targetEntity: ProductVariant::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $variants;

    #[ORM\ManyToMany(targetEntity: Media::class)]
    #[ORM\JoinTable(name: 'product_media')]
    private Collection $media;

    #[ORM\ManyToOne(targetEntity: Media::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Media $primaryImage = null;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
        $this->translations = new ArrayCollection();
        $this->variants = new ArrayCollection();
        $this->media = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): static
    {
        $this->slug = $slug;
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

    public function isVariable(): bool
    {
        return $this->isVariable;
    }

    public function setIsVariable(bool $isVariable): static
    {
        $this->isVariable = $isVariable;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function isDigital(): bool
    {
        return $this->isDigital;
    }

    public function setIsDigital(bool $isDigital): static
    {
        $this->isDigital = $isDigital;
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

    public function getBrand(): ?Brand
    {
        return $this->brand;
    }

    public function setBrand(?Brand $brand): static
    {
        $this->brand = $brand;
        return $this;
    }

    /**
     * @return Collection<int, Category>
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Category $category): static
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
        }

        return $this;
    }

    public function removeCategory(Category $category): static
    {
        $this->categories->removeElement($category);
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

    #[ORM\PreUpdate]
    public function setUpdatedAt(): static
    {
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    public function getPublishedAt(): ?\DateTimeImmutable
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(?\DateTimeImmutable $publishedAt): static
    {
        $this->publishedAt = $publishedAt;
        return $this;
    }

    /**
     * @return Collection<int, ProductTranslation>
     */
    public function getTranslations(): Collection
    {
        return $this->translations;
    }

    public function addTranslation(ProductTranslation $translation): static
    {
        if (!$this->translations->contains($translation)) {
            $this->translations->add($translation);
            $translation->setProduct($this);
        }

        return $this;
    }

    public function removeTranslation(ProductTranslation $translation): static
    {
        if ($this->translations->removeElement($translation)) {
            if ($translation->getProduct() === $this) {
                $translation->setProduct(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ProductVariant>
     */
    public function getVariants(): Collection
    {
        return $this->variants;
    }

    public function addVariant(ProductVariant $variant): static
    {
        if (!$this->variants->contains($variant)) {
            $this->variants->add($variant);
            $variant->setProduct($this);
        }

        return $this;
    }

    public function removeVariant(ProductVariant $variant): static
    {
        if ($this->variants->removeElement($variant)) {
            if ($variant->getProduct() === $this) {
                $variant->setProduct(null);
            }
        }

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
    public function getTranslation(?string $languageCode = null): ?ProductTranslation
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
    public function getTranslationWithFallback(string $languageCode, ?string $fallbackLanguageCode = null): ?ProductTranslation
    {
        $translation = $this->getTranslation($languageCode);
        
        if (!$translation && $fallbackLanguageCode) {
            $translation = $this->getTranslation($fallbackLanguageCode);
        }
        
        // If still no translation, get the first available one
        if (!$translation) {
            $translation = $this->translations->first() ?: null;
        }

        return $translation;
    }

    /**
     * Get name for a specific language with fallback
     */
    public function getName(string $languageCode, ?string $fallbackLanguageCode = null): string
    {
        $translation = $this->getTranslationWithFallback($languageCode, $fallbackLanguageCode);
        return $translation ? $translation->getName() : 'Untitled Product';
    }

    /**
     * Get description for a specific language with fallback
     */
    public function getDescription(string $languageCode, ?string $fallbackLanguageCode = null): ?string
    {
        $translation = $this->getTranslationWithFallback($languageCode, $fallbackLanguageCode);
        return $translation ? $translation->getDescription() : null;
    }

    /**
     * Get short description for a specific language with fallback
     */
    public function getShortDescription(string $languageCode, ?string $fallbackLanguageCode = null): ?string
    {
        $translation = $this->getTranslationWithFallback($languageCode, $fallbackLanguageCode);
        return $translation ? $translation->getShortDescription() : null;
    }

    /**
     * Check if product has translation for a specific language
     */
    public function hasTranslation(string $languageCode): bool
    {
        return $this->getTranslation($languageCode) !== null;
    }

    /**
     * Check if product is published
     */
    public function isPublished(): bool
    {
        return $this->status === self::STATUS_ACTIVE && $this->publishedAt !== null && $this->publishedAt <= new \DateTimeImmutable();
    }

    /**
     * Check if product is active
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Check if product is in stock
     */
    public function isInStock(): bool
    {
        if (!$this->trackStock) {
            return true;
        }

        if ($this->isVariable) {
            return $this->variants->exists(function($key, ProductVariant $variant) {
                return $variant->isInStock();
            });
        }

        return $this->stock > 0;
    }

    /**
     * Check if product is low in stock
     */
    public function isLowStock(): bool
    {
        if (!$this->trackStock || $this->lowStockThreshold === null) {
            return false;
        }

        if ($this->isVariable) {
            return $this->variants->exists(function($key, ProductVariant $variant) {
                return $variant->isLowStock();
            });
        }

        return $this->stock <= $this->lowStockThreshold;
    }

    /**
     * Get primary media (fallback to first media if no specific primary image is set)
     */
    public function getPrimaryMedia(): ?Media
    {
        return $this->primaryImage ?: ($this->media->first() ?: null);
    }

    public function getPrimaryImage(): ?Media
    {
        return $this->primaryImage;
    }

    public function setPrimaryImage(?Media $primaryImage): static
    {
        $this->primaryImage = $primaryImage;
        return $this;
    }

    /**
     * Get active variants
     */
    public function getActiveVariants(): Collection
    {
        return $this->variants->filter(function(ProductVariant $variant) {
            return $variant->isActive();
        });
    }

    /**
     * Get available statuses
     */
    public static function getAvailableStatuses(): array
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_INACTIVE => 'Inactive',
            self::STATUS_ARCHIVED => 'Archived'
        ];
    }

    /**
     * Validate prices coherence
     */
    public function isValidPriceStructure(): bool
    {
        if ($this->price === null) {
            return false;
        }
        
        $priceFloat = (float) $this->price;
        
        if ($this->compareAtPrice !== null) {
            $compareAtPriceFloat = (float) $this->compareAtPrice;
            if ($compareAtPriceFloat <= $priceFloat) {
                return false; // Compare at price should be higher than regular price
            }
        }
        
        if ($this->costPrice !== null) {
            $costPriceFloat = (float) $this->costPrice;
            if ($costPriceFloat >= $priceFloat) {
                return false; // Cost price should be lower than selling price
            }
        }
        
        return true;
    }

    /**
     * Get discount percentage if compare at price is set
     */
    public function getDiscountPercentage(): ?float
    {
        if ($this->compareAtPrice === null || $this->price === null) {
            return null;
        }
        
        $priceFloat = (float) $this->price;
        $compareAtPriceFloat = (float) $this->compareAtPrice;
        
        if ($compareAtPriceFloat <= $priceFloat) {
            return null;
        }
        
        return round((($compareAtPriceFloat - $priceFloat) / $compareAtPriceFloat) * 100, 2);
    }

    /**
     * Get profit margin percentage if cost price is set
     */
    public function getProfitMarginPercentage(): ?float
    {
        if ($this->costPrice === null || $this->price === null) {
            return null;
        }
        
        $priceFloat = (float) $this->price;
        $costPriceFloat = (float) $this->costPrice;
        
        if ($costPriceFloat >= $priceFloat) {
            return null;
        }
        
        return round((($priceFloat - $costPriceFloat) / $priceFloat) * 100, 2);
    }

    /**
     * Get price as float for calculations
     */
    public function getPriceAsFloat(): ?float
    {
        return $this->price ? (float) $this->price : null;
    }

    /**
     * Get compare at price as float for calculations
     */
    public function getCompareAtPriceAsFloat(): ?float
    {
        return $this->compareAtPrice ? (float) $this->compareAtPrice : null;
    }

    /**
     * Get cost price as float for calculations
     */
    public function getCostPriceAsFloat(): ?float
    {
        return $this->costPrice ? (float) $this->costPrice : null;
    }

    /**
     * Get weight as float for calculations
     */
    public function getWeightAsFloat(): ?float
    {
        return $this->weight ? (float) $this->weight : null;
    }

    public function __toString(): string
    {
        return $this->getName('en') ?: $this->getName('fr') ?: 'Product #' . $this->id;
    }
}