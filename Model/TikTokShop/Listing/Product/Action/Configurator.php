<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action;

class Configurator
{
    public const DATA_TYPE_GENERAL = 'general';
    public const DATA_TYPE_VARIANTS = 'variants';
    public const DATA_TYPE_TITLE = 'title';
    public const DATA_TYPE_DESCRIPTION = 'description';
    public const DATA_TYPE_IMAGES = 'images';
    public const DATA_TYPE_CATEGORIES = 'categories';
    public const DATA_TYPE_OTHER = 'other';

    private static array $allTypes = [
        self::DATA_TYPE_GENERAL,
        self::DATA_TYPE_VARIANTS,
        self::DATA_TYPE_TITLE,
        self::DATA_TYPE_DESCRIPTION,
        self::DATA_TYPE_IMAGES,
        self::DATA_TYPE_CATEGORIES,
        self::DATA_TYPE_OTHER
    ];

    private array $enabledTypes;

    public static function createWithTypes(array $types): self
    {
        $enabledTypes = [];
        foreach ($types as $type) {
            if (!in_array($type, self::$allTypes)) {
                continue;
            }

            $enabledTypes[] = $type;
        }

        $configurator = new self();
        $configurator->disableAll();

        $configurator->enabledTypes = $enabledTypes;

        return $configurator;
    }

    public function __construct()
    {
        $this->enabledTypes = self::$allTypes;
    }

    public function enableAll(): self
    {
        $this->enabledTypes = self::$allTypes;

        return $this;
    }

    public function disableAll(): self
    {
        $this->enabledTypes = [];

        return $this;
    }

    public function getEnabledDataTypes(): array
    {
        return $this->enabledTypes;
    }

    // ---------------------------------------

    public function isVariantsAllowed(): bool
    {
        return $this->isAllowed(self::DATA_TYPE_VARIANTS);
    }

    public function allowVariants(): self
    {
        $this->allow(self::DATA_TYPE_VARIANTS);

        return $this;
    }

    public function disallowVariants(): self
    {
        $this->disallow(self::DATA_TYPE_VARIANTS);

        return $this;
    }

    // ---------------------------------------

    public function isTitleAllowed(): bool
    {
        return $this->isAllowed(self::DATA_TYPE_TITLE);
    }

    public function allowTitle(): self
    {
        $this->allow(self::DATA_TYPE_TITLE);

        return $this;
    }

    public function disallowTitle(): self
    {
        $this->disallow(self::DATA_TYPE_TITLE);

        return $this;
    }

    // ---------------------------------------

    public function isDescriptionAllowed(): bool
    {
        return $this->isAllowed(self::DATA_TYPE_DESCRIPTION);
    }

    public function allowDescription(): self
    {
        $this->allow(self::DATA_TYPE_DESCRIPTION);

        return $this;
    }

    public function disallowDescription(): self
    {
        $this->disallow(self::DATA_TYPE_DESCRIPTION);

        return $this;
    }

    // ---------------------------------------

    public function isImagesAllowed(): bool
    {
        return $this->isAllowed(self::DATA_TYPE_IMAGES);
    }

    public function allowImages(): self
    {
        $this->allow(self::DATA_TYPE_IMAGES);

        return $this;
    }

    public function disallowImages(): self
    {
        $this->disallow(self::DATA_TYPE_IMAGES);

        return $this;
    }

    // ---------------------------------------

    public function isCategoriesAllowed(): bool
    {
        return $this->isAllowed(self::DATA_TYPE_CATEGORIES);
    }

    public function allowCategories(): self
    {
        $this->allow(self::DATA_TYPE_CATEGORIES);

        return $this;
    }

    public function disallowCategories(): self
    {
        $this->disallow(self::DATA_TYPE_CATEGORIES);

        return $this;
    }

    // ---------------------------------------

    public function isOtherAllowed(): bool
    {
        return $this->isAllowed(self::DATA_TYPE_OTHER);
    }

    public function allowOther(): self
    {
        $this->allow(self::DATA_TYPE_OTHER);

        return $this;
    }

    public function disallowOther(): self
    {
        $this->disallow(self::DATA_TYPE_OTHER);

        return $this;
    }

    // ----------------------------------------

    private function isAllowed(string $dataType): bool
    {
        return in_array($dataType, $this->enabledTypes);
    }

    private function allow(string $dataType): void
    {
        if (!in_array($dataType, self::$allTypes)) {
            return;
        }

        if (in_array($dataType, $this->enabledTypes)) {
            return;
        }

        $this->enabledTypes[] = $dataType;
    }

    private function disallow($dataType): void
    {
        if (!in_array($dataType, $this->enabledTypes)) {
            return;
        }

        $this->enabledTypes = array_diff($this->enabledTypes, [$dataType]);
    }
}
