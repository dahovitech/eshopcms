<?php

namespace App\Entity;

use App\Repository\AttributeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AttributeRepository::class)]
#[ORM\Table(name: 'attributes')]
class Attribute
{
    public const TYPE_TEXT = 'text';
    public const TYPE_NUMBER = 'number';
    public const TYPE_SELECT = 'select';
    public const TYPE_COLOR = 'color';
    public const TYPE_BOOLEAN = 'boolean';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 100, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    #[Assert\Regex(pattern: '/^[a-z0-9_]+$/', message: 'Code must contain only lowercase letters, numbers and underscores')]
    private ?string $code = null;

    #[ORM\Column(type: 'string', length: 50)]
    #[Assert\NotBlank]
    #[Assert\Choice(choices: [self::TYPE_TEXT, self::TYPE_NUMBER, self::TYPE_SELECT, self::TYPE_COLOR, self::TYPE_BOOLEAN])]
    private ?string $type = self::TYPE_TEXT;

    #[ORM\Column(type: 'boolean')]
    private bool $isRequired = false;

    #[ORM\Column(type: 'boolean')]
    private bool $isVariant = false;

    #[ORM\Column(type: 'boolean')]
    private bool $isFilterable = true;

    #[ORM\Column(type: 'boolean')]
    private bool $isActive = true;

    #[ORM\Column(type: 'integer')]
    private int $sortOrder = 0;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $configuration = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $updatedAt;

    #[ORM\OneToMany(mappedBy: 'attribute', targetEntity: AttributeTranslation::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $translations;

    #[ORM\OneToMany(mappedBy: 'attribute', targetEntity: AttributeValue::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $values;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
        $this->values = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): static
    {
        $this->code = $code;
        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function isRequired(): bool
    {
        return $this->isRequired;
    }

    public function setIsRequired(bool $isRequired): static
    {
        $this->isRequired = $isRequired;
        return $this;
    }

    public function isVariant(): bool
    {
        return $this->isVariant;
    }

    public function setIsVariant(bool $isVariant): static
    {
        $this->isVariant = $isVariant;
        return $this;
    }

    public function isFilterable(): bool
    {
        return $this->isFilterable;
    }

    public function setIsFilterable(bool $isFilterable): static
    {
        $this->isFilterable = $isFilterable;
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

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function setSortOrder(int $sortOrder): static
    {
        $this->sortOrder = $sortOrder;
        return $this;
    }

    public function getConfiguration(): ?array
    {
        return $this->configuration;
    }

    public function setConfiguration(?array $configuration): static
    {
        $this->configuration = $configuration;
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
     * @return Collection<int, AttributeTranslation>
     */
    public function getTranslations(): Collection
    {
        return $this->translations;
    }

    public function addTranslation(AttributeTranslation $translation): static
    {
        if (!$this->translations->contains($translation)) {
            $this->translations->add($translation);
            $translation->setAttribute($this);
        }

        return $this;
    }

    public function removeTranslation(AttributeTranslation $translation): static
    {
        if ($this->translations->removeElement($translation)) {
            if ($translation->getAttribute() === $this) {
                $translation->setAttribute(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, AttributeValue>
     */
    public function getValues(): Collection
    {
        return $this->values;
    }

    public function addValue(AttributeValue $value): static
    {
        if (!$this->values->contains($value)) {
            $this->values->add($value);
            $value->setAttribute($this);
        }

        return $this;
    }

    public function removeValue(AttributeValue $value): static
    {
        if ($this->values->removeElement($value)) {
            if ($value->getAttribute() === $this) {
                $value->setAttribute(null);
            }
        }

        return $this;
    }

    /**
     * Get translation for a specific language
     */
    public function getTranslation(?string $languageCode = null): ?AttributeTranslation
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
    public function getTranslationWithFallback(string $languageCode, string $fallbackLanguageCode = 'fr'): ?AttributeTranslation
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
        return $translation ? $translation->getName() : ucfirst($this->code ?? 'Untitled Attribute');
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
     * Get placeholder for a specific language with fallback
     */
    public function getPlaceholder(string $languageCode = 'fr', string $fallbackLanguageCode = 'fr'): string
    {
        $translation = $this->getTranslationWithFallback($languageCode, $fallbackLanguageCode);
        return $translation ? $translation->getPlaceholder() : '';
    }

    /**
     * Check if attribute has translation for a specific language
     */
    public function hasTranslation(string $languageCode): bool
    {
        return $this->getTranslation($languageCode) !== null;
    }

    /**
     * Get active values
     */
    public function getActiveValues(): Collection
    {
        return $this->values->filter(function(AttributeValue $value) {
            return $value->isActive();
        });
    }

    /**
     * Get available types
     */
    public static function getAvailableTypes(): array
    {
        return [
            self::TYPE_TEXT => 'Text',
            self::TYPE_NUMBER => 'Number',
            self::TYPE_SELECT => 'Select',
            self::TYPE_COLOR => 'Color',
            self::TYPE_BOOLEAN => 'Boolean'
        ];
    }

    public function __toString(): string
    {
        return $this->getName();
    }
}