<?php

namespace App\Entity;

use App\Repository\ProductTranslationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProductTranslationRepository::class)]
#[ORM\Table(name: 'product_translations')]
#[ORM\UniqueConstraint(name: 'UNIQ_PRODUCT_LANGUAGE', columns: ['product_id', 'language_id'])]
#[ORM\UniqueConstraint(name: 'UNIQ_SLUG_TRANSLATION_LANGUAGE', columns: ['slug_translation', 'language_id'])]
#[ORM\HasLifecycleCallbacks]
class ProductTranslation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Product::class, inversedBy: 'translations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Product $product = null;

    #[ORM\ManyToOne(targetEntity: Language::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Language $language = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private string $name = '';

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $shortDescription = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $slugTranslation = null;

    #[ORM\Column(type: 'string', length: 500, nullable: true)]
    #[Assert\Length(max: 500)]
    private ?string $metaTitle = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(max: 1000)]
    private ?string $metaDescription = null;

    #[ORM\Column(type: 'array', nullable: true)]
    private ?array $metaKeywords = null;

    #[ORM\Column(type: 'array', nullable: true)]
    private ?array $tags = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $specifications = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $features = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $updatedAt;

    public function __construct()
    {
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

    public function getLanguage(): ?Language
    {
        return $this->language;
    }

    public function setLanguage(?Language $language): static
    {
        $this->language = $language;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getShortDescription(): ?string
    {
        return $this->shortDescription;
    }

    public function setShortDescription(?string $shortDescription): static
    {
        $this->shortDescription = $shortDescription;
        return $this;
    }

    public function getSlugTranslation(): ?string
    {
        return $this->slugTranslation;
    }

    public function setSlugTranslation(?string $slugTranslation): static
    {
        $this->slugTranslation = $slugTranslation;
        return $this;
    }

    public function getMetaTitle(): ?string
    {
        return $this->metaTitle;
    }

    public function setMetaTitle(?string $metaTitle): static
    {
        $this->metaTitle = $metaTitle;
        return $this;
    }

    public function getMetaDescription(): ?string
    {
        return $this->metaDescription;
    }

    public function setMetaDescription(?string $metaDescription): static
    {
        $this->metaDescription = $metaDescription;
        return $this;
    }

    public function getMetaKeywords(): ?array
    {
        return $this->metaKeywords;
    }

    public function setMetaKeywords(?array $metaKeywords): static
    {
        $this->metaKeywords = $metaKeywords;
        return $this;
    }

    public function getTags(): ?array
    {
        return $this->tags;
    }

    public function setTags(?array $tags): static
    {
        $this->tags = $tags;
        return $this;
    }

    public function getSpecifications(): ?string
    {
        return $this->specifications;
    }

    public function setSpecifications(?string $specifications): static
    {
        $this->specifications = $specifications;
        return $this;
    }

    public function getFeatures(): ?string
    {
        return $this->features;
    }

    public function setFeatures(?string $features): static
    {
        $this->features = $features;
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

    /**
     * Check if translation is complete
     */
    public function isComplete(): bool
    {
        return !empty($this->name) && !empty($this->description) && !empty($this->shortDescription);
    }

    /**
     * Check if translation is partial (has some content)
     */
    public function isPartial(): bool
    {
        return !empty($this->name) || !empty($this->description) || !empty($this->shortDescription);
    }

    /**
     * Get completion percentage
     */
    public function getCompletionPercentage(): int
    {
        $fields = [
            !empty($this->name),
            !empty($this->description),
            !empty($this->shortDescription),
            !empty($this->metaTitle),
            !empty($this->metaDescription),
            !empty($this->slugTranslation),
            !empty($this->metaKeywords),
            !empty($this->tags),
            !empty($this->specifications),
            !empty($this->features)
        ];
        
        $completed = array_sum($fields);
        return (int) round(($completed / count($fields)) * 100);
    }

    /**
     * Get effective slug for URL generation
     */
    public function getEffectiveSlug(): string
    {
        if (!empty($this->slugTranslation)) {
            return $this->slugTranslation;
        }
        
        if ($this->product && !empty($this->product->getSlug())) {
            return $this->product->getSlug() . '-' . $this->language?->getCode();
        }
        
        return $this->name ? strtolower(str_replace(' ', '-', $this->name)) : '';
    }

    /**
     * Auto-generate slug from name if not set
     */
    public function generateSlugFromName(): static
    {
        if (empty($this->slugTranslation) && !empty($this->name)) {
            $this->slugTranslation = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $this->name), '-'));
        }
        return $this;
    }

    public function __toString(): string
    {
        return $this->name . ' (' . $this->language?->getCode() . ')';
    }
}