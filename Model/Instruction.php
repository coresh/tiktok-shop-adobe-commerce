<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model;

use M2E\TikTokShop\Model\ResourceModel\Instruction as InstructionResource;

class Instruction extends \M2E\TikTokShop\Model\ActiveRecord\AbstractModel
{
    private Product $listingProduct;
    /** @var \M2E\TikTokShop\Model\Product\Repository */
    private Product\Repository $productRepository;

    public function __construct(
        \M2E\TikTokShop\Model\Product\Repository $productRepository,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry
    ) {
        parent::__construct($context, $registry);
        $this->productRepository = $productRepository;
    }

    public function _construct(): void
    {
        parent::_construct();
        $this->_init(ResourceModel\Instruction::class);
    }

    public function create(
        int $listingProductId,
        string $type,
        string $initiator,
        int $priority,
        ?\DateTime $skipUntil
    ): self {
        $this
            ->setData(InstructionResource::COLUMN_LISTING_PRODUCT_ID, $listingProductId)
            ->setData(InstructionResource::COLUMN_TYPE, $type)
            ->setData(InstructionResource::COLUMN_INITIATOR, $initiator)
            ->setData(InstructionResource::COLUMN_PRIORITY, $priority)
            ->setData(
                InstructionResource::COLUMN_SKIP_UNTIL,
                $skipUntil === null ? null : $skipUntil->format('Y-m-d H:i:s'),
            );

        return $this;
    }

    public function initListingProduct(Product $listingProduct): void
    {
        $this->listingProduct = $listingProduct;
    }

    public function getListingProduct(): Product
    {
        if ($this->getId() === null) {
            throw new \M2E\TikTokShop\Model\Exception\Logic('Model must be loaded.');
        }

        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (isset($this->listingProduct)) {
            return $this->listingProduct;
        }

        return $this->listingProduct = $this->productRepository->get($this->getListingProductId());
    }

    public function getListingProductId(): int
    {
        return (int)$this->getData(InstructionResource::COLUMN_LISTING_PRODUCT_ID);
    }

    public function getType()
    {
        return $this->getData(InstructionResource::COLUMN_TYPE);
    }

    public function getInitiator(): string
    {
        return (string)$this->getData(InstructionResource::COLUMN_INITIATOR);
    }

    public function getPriority(): int
    {
        return (int)$this->getData(InstructionResource::COLUMN_PRIORITY);
    }

    public function getSkipUntil(): ?\DateTime
    {
        $value = $this->getData(InstructionResource::COLUMN_SKIP_UNTIL);
        if (empty($value)) {
            return null;
        }

        return \M2E\Core\Helper\Date::createDateGmt($value);
    }
}
