<?php

namespace M2E\TikTokShop\Model\Template;

use M2E\TikTokShop\Model\ResourceModel\Template\Description as DescriptionResource;

class Description extends \M2E\TikTokShop\Model\ActiveRecord\AbstractModel
{
    public const TITLE_MODE_PRODUCT = 0;
    public const TITLE_MODE_CUSTOM = 1;

    public const DESCRIPTION_MODE_PRODUCT = 0;
    public const DESCRIPTION_MODE_SHORT = 1;
    public const DESCRIPTION_MODE_CUSTOM = 2;

    public const IMAGE_MAIN_MODE_PRODUCT = 1;
    public const IMAGE_MAIN_MODE_ATTRIBUTE = 2;

    public const GALLERY_IMAGES_MODE_NONE = 0;
    public const GALLERY_IMAGES_MODE_PRODUCT = 1;
    public const GALLERY_IMAGES_MODE_ATTRIBUTE = 2;

    public const RESIZE_IMAGE_NO = 0;
    public const RESIZE_IMAGE_YES = 1;

    public const INSTRUCTION_TYPE_MAGENTO_STATIC_BLOCK_IN_DESCRIPTION_CHANGED
        = 'magento_static_block_in_description_changed';

    /** @var \M2E\TikTokShop\Model\Template\Description\Source[] */
    private array $descriptionSourceModels = [];
    private \M2E\TikTokShop\Model\ResourceModel\Listing\CollectionFactory $listingCollectionFactory;
    private Description\SourceFactory $sourceFactory;

    public function __construct(
        Description\SourceFactory $sourceFactory,
        \M2E\TikTokShop\Model\ResourceModel\Listing\CollectionFactory $listingCollectionFactory,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry
    ) {
        parent::__construct(
            $context,
            $registry,
        );
        $this->listingCollectionFactory = $listingCollectionFactory;
        $this->sourceFactory = $sourceFactory;
    }

    public function _construct()
    {
        parent::_construct();
        $this->_init(\M2E\TikTokShop\Model\ResourceModel\Template\Description::class);
    }

    /**
     * @return string
     */
    public function getNick(): string
    {
        return \M2E\TikTokShop\Model\TikTokShop\Template\Manager::TEMPLATE_DESCRIPTION;
    }

    /**
     * @return bool
     */
    public function isLocked()
    {
        return (bool)$this
            ->listingCollectionFactory
            ->create()
            ->addFieldToFilter(
                \M2E\TikTokShop\Model\ResourceModel\Listing::COLUMN_TEMPLATE_DESCRIPTION_ID,
                $this->getId()
            )
            ->getSize();
    }

    public function getTitle(): string
    {
        return (string)$this->getData(DescriptionResource::COLUMN_TITLE);
    }

    public function getCreateDate()
    {
        return $this->getData('create_date');
    }

    public function getUpdateDate()
    {
        return $this->getData('update_date');
    }

    //########################################

    /**
     * @param \M2E\TikTokShop\Model\Magento\Product $magentoProduct
     *
     * @return \M2E\TikTokShop\Model\Template\Description\Source
     */
    public function getSource(
        \M2E\TikTokShop\Model\Magento\Product $magentoProduct
    ): Description\Source {
        $productId = $magentoProduct->getProductId();

        if (!empty($this->descriptionSourceModels[$productId])) {
            return $this->descriptionSourceModels[$productId];
        }

        $this->descriptionSourceModels[$productId] = $this->sourceFactory->create();
        $this->descriptionSourceModels[$productId]->setMagentoProduct($magentoProduct);
        $this->descriptionSourceModels[$productId]->setDescriptionTemplate($this);

        return $this->descriptionSourceModels[$productId];
    }

    //########################################

    public function getTitleMode(): int
    {
        return (int)$this->getData(DescriptionResource::COLUMN_TITLE_MODE);
    }

    public function getTitleSource(): array
    {
        return [
            'mode' => $this->getTitleMode(),
            'template' => $this->getData(DescriptionResource::COLUMN_TITLE_TEMPLATE),
        ];
    }

    public function getTitleTrackedAttributes(): array
    {
        $attributes = [];
        $src = $this->getTitleSource();

        if ($src['mode'] == self::TITLE_MODE_PRODUCT) {
            $attributes[] = 'name';
        } else {
            $match = [];
            preg_match_all('/#([a-zA-Z_0-9]+?)#/', $src['template'], $match);
            $match && $attributes = $match[1];
        }

        return $attributes;
    }

    public function getDescriptionMode(): int
    {
        return (int)$this->getData(DescriptionResource::COLUMN_DESCRIPTION_MODE);
    }

    public function getDescriptionSource(): array
    {
        return [
            'mode' => $this->getDescriptionMode(),
            'template' => $this->getData(DescriptionResource::COLUMN_DESCRIPTION_TEMPLATE),
        ];
    }

    public function getDescriptionTrackedAttributes(): array
    {
        $attributes = [];
        $src = $this->getDescriptionSource();

        if ($src['mode'] == self::DESCRIPTION_MODE_PRODUCT) {
            $attributes[] = 'description';
        } elseif ($src['mode'] == self::DESCRIPTION_MODE_SHORT) {
            $attributes[] = 'short_description';
        } else {
            preg_match_all('/#([a-zA-Z_0-9]+?)#|#(image|media_gallery)\[.*\]#+?/', $src['template'], $match);
            !empty($match[0]) && $attributes = array_filter(array_merge($match[1], $match[2]));
        }

        return $attributes;
    }

    // ---------------------------------------

    public function getImageMainMode(): int
    {
        return (int)$this->getData(DescriptionResource::COLUMN_IMAGE_MAIN_MODE);
    }

    public function isImageMainModeProduct(): bool
    {
        return $this->getImageMainMode() === self::IMAGE_MAIN_MODE_PRODUCT;
    }

    public function isImageMainModeAttribute(): bool
    {
        return $this->getImageMainMode() === self::IMAGE_MAIN_MODE_ATTRIBUTE;
    }

    public function getImageMainSource(): array
    {
        return [
            'mode' => $this->getImageMainMode(),
            'attribute' => $this->getData(DescriptionResource::COLUMN_IMAGE_MAIN_ATTRIBUTE),
        ];
    }

    public function getImageMainTrackedAttributes(): array
    {
        $attributes = [];
        $src = $this->getImageMainSource();

        if ($src['mode'] == self::IMAGE_MAIN_MODE_PRODUCT) {
            $attributes[] = 'image';
        } elseif ($src['mode'] == self::IMAGE_MAIN_MODE_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    // ---------------------------------------

    public function getGalleryImagesMode(): int
    {
        return (int)$this->getData(DescriptionResource::COLUMN_GALLERY_IMAGES_MODE);
    }

    public function isGalleryImagesModeNone(): bool
    {
        return $this->getGalleryImagesMode() === self::GALLERY_IMAGES_MODE_NONE;
    }

    public function isGalleryImagesModeProduct(): bool
    {
        return $this->getGalleryImagesMode() === self::GALLERY_IMAGES_MODE_PRODUCT;
    }

    public function isGalleryImagesModeAttribute(): bool
    {
        return $this->getGalleryImagesMode() === self::GALLERY_IMAGES_MODE_ATTRIBUTE;
    }

    public function getGalleryImagesSource(): array
    {
        return [
            'mode' => $this->getGalleryImagesMode(),
            'attribute' => $this->getData(DescriptionResource::COLUMN_GALLERY_IMAGES_ATTRIBUTE),
            'limit' => $this->getData(DescriptionResource::COLUMN_GALLERY_IMAGES_LIMIT),
        ];
    }

    public function getGalleryImagesTrackedAttributes(): array
    {
        $attributes = [];
        $src = $this->getGalleryImagesSource();

        if ($src['mode'] == self::GALLERY_IMAGES_MODE_PRODUCT) {
            $attributes[] = 'media_gallery';
        } elseif ($src['mode'] == self::GALLERY_IMAGES_MODE_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    public function isUseImageResize(): bool
    {
        return (int)$this->getData(DescriptionResource::COLUMN_RESIZE_IMAGE) === self::RESIZE_IMAGE_YES;
    }
}
