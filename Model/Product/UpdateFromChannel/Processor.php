<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Product\UpdateFromChannel;

use M2E\TikTokShop\Model\Product;
use M2E\TikTokShop\Model\Product\VariantSku;

class Processor
{
    private array $instructionsData = [];
    /** @var \M2E\TikTokShop\Model\Listing\Log\Record[] */
    private array $logs = [];

    private \M2E\TikTokShop\Model\Product $product;
    private \M2E\TikTokShop\Model\Listing\InventorySync\Channel\Product $channelProduct;
    /** @var \M2E\TikTokShop\Model\Product\CalculateStatusByChannel */
    private Product\CalculateStatusByChannel $calculateStatusByChannel;
    private \M2E\TikTokShop\Model\Listing\InventorySync\Channel\ListingQuality\Converter $listingQualityConverter;
    private \M2E\TikTokShop\Model\TikTokShop\TagFactory $tagFactory;
    private \M2E\TikTokShop\Model\Tag\ListingProduct\Buffer $tagBuffer;

    public function __construct(
        \M2E\TikTokShop\Model\Product $product,
        \M2E\TikTokShop\Model\Listing\InventorySync\Channel\Product $channelProduct,
        \M2E\TikTokShop\Model\Product\CalculateStatusByChannel $calculateStatusByChannel,
        \M2E\TikTokShop\Model\Listing\InventorySync\Channel\ListingQuality\Converter $listingQualityConverter,
        \M2E\TikTokShop\Model\TikTokShop\TagFactory $tagFactory,
        \M2E\TikTokShop\Model\Tag\ListingProduct\Buffer $tagBuffer
    ) {
        $this->product = $product;
        $this->channelProduct = $channelProduct;
        $this->calculateStatusByChannel = $calculateStatusByChannel;
        $this->listingQualityConverter = $listingQualityConverter;
        $this->tagFactory = $tagFactory;
        $this->tagBuffer = $tagBuffer;
    }

    public function processChanges(): ChangeResult
    {
        $isChangedProduct = $this->processProduct();
        $isChangedSomeVariant = $this->processVariants();

        if ($isChangedSomeVariant) {
            $this->product->recalculateOnlineDataByVariants();

            $isChangedProduct = true;
        }

        return new ChangeResult(
            $this->product,
            $isChangedProduct,
            $isChangedSomeVariant,
            array_values($this->instructionsData),
            array_values($this->logs),
        );
    }

    private function processVariants(): bool
    {
        $isChanged = false;
        foreach ($this->channelProduct->getVariantCollection()->getAll() as $channelVariant) {
            $existVariant = $this->product->findVariantBySkuId($channelVariant->getSkuId());

            if ($existVariant === null) {
                if ($this->product->isStatusListed()) {
                    $this->addInstructionData(
                        \M2E\TikTokShop\Model\Product::INSTRUCTION_TYPE_VARIANT_SKU_REMOVED,
                        80,
                    );
                }

                continue;
            }

            if ($this->isNeedUpdateIdentifier($existVariant, $channelVariant)) {
                $existVariant->setOnlineIdentifier($channelVariant->getIdentifier());
                $isChanged = true;
            }

            if ($this->isNeedUpdateVariantQty($existVariant, $channelVariant)) {
                $this->addInstructionData(
                    Product::INSTRUCTION_TYPE_CHANNEL_QTY_CHANGED,
                    80,
                );

                if ($this->product->isSimple()) {
                    $message = (string)__(
                        'Item QTY was changed from %from to %to.',
                        [
                            'from' => $existVariant->getOnlineQty(),
                            'to' => $channelVariant->getQty(),
                        ],
                    );
                } else {
                    $message = (string)__(
                        'SKU %sku: Item QTY was changed from %from to %to.',
                        [
                            'sku' => $existVariant->getSku(),
                            'from' => $existVariant->getOnlineQty(),
                            'to' => $channelVariant->getQty(),
                        ],
                    );
                }

                $this->addLog(
                    new \M2E\TikTokShop\Model\Listing\Log\Record(
                        $message,
                        \M2E\TikTokShop\Model\Log\AbstractModel::TYPE_SUCCESS,
                    ),
                );

                $existVariant->setOnlineQty($channelVariant->getQty());

                $isChanged = true;
            }

            if ($this->isNeedUpdateVariantPrice($existVariant, $channelVariant)) {
                $this->addInstructionData(
                    Product::INSTRUCTION_TYPE_CHANNEL_PRICE_CHANGED,
                    60,
                );

                if ($this->product->isSimple()) {
                    $message = (string)__(
                        'Item Price was changed from %from to %to.',
                        [
                            'from' => $existVariant->getOnlineCurrentPrice(),
                            'to' => $channelVariant->getPrice(),
                        ]
                    );
                } else {
                    $message = (string)__(
                        'SKU %sku: Item Price was changed from %from to %to.',
                        [
                            'sku' => $existVariant->getSku(),
                            'from' => $existVariant->getOnlineCurrentPrice(),
                            'to' => $channelVariant->getPrice(),
                        ],
                    );
                }

                $this->addLog(
                    new \M2E\TikTokShop\Model\Listing\Log\Record(
                        $message,
                        \M2E\TikTokShop\Model\Log\AbstractModel::TYPE_SUCCESS,
                    ),
                );

                $existVariant->setOnlineCurrentPrice($channelVariant->getPrice());

                $isChanged = true;
            }

            if ($this->isNeedForceChangeStatusByProduct()) {
                // We do not create instructions and do not write logs as this case depends on the product change
                if ($this->product->isStatusNotListed()) {
                    $existVariant->changeStatusToNoListed();
                } elseif ($this->product->isStatusBlocked()) {
                    $existVariant->changeStatusToInactive();
                } elseif ($this->product->isStatusInactive()) {
                    $existVariant->changeStatusToInactive();
                }

                $isChanged = true;
            } elseif ($this->isNeedChangeStatus($existVariant->getStatus(), $channelVariant->getStatus())) {
                $this->addInstructionData(
                    Product::INSTRUCTION_TYPE_CHANNEL_STATUS_CHANGED,
                    80,
                );

                $this->addLog(
                    new \M2E\TikTokShop\Model\Listing\Log\Record(
                        (string)__(
                            'SKU %sku: Item Status was changed from %from to %to.',
                            [
                                'sku' => $existVariant->getSku(),
                                'from' => Product::getStatusTitle($existVariant->getStatus()),
                                'to' => Product::getStatusTitle($channelVariant->getStatus()),
                            ],
                        ),
                        \M2E\TikTokShop\Model\Log\AbstractModel::TYPE_SUCCESS,
                    ),
                );

                $existVariant->setStatus($channelVariant->getStatus());

                $isChanged = true;
            }
        }

        return $isChanged;
    }

    private function isNeedUpdateVariantQty(
        VariantSku $variant,
        \M2E\TikTokShop\Model\Listing\InventorySync\Channel\ProductSku $channelVariantSku
    ): bool {
        if (!$this->product->isStatusListed()) {
            return false;
        }

        if ($variant->getOnlineQty() === $channelVariantSku->getQty()) {
            return false;
        }

        return !$this->isNeedSkipQtyChange($variant->getOnlineQty(), $channelVariantSku->getQty());
    }

    private function isNeedSkipQtyChange(int $currentQty, int $channelQty): bool
    {
        if ($channelQty > $currentQty) {
            return false;
        }

        return $currentQty < 5;
    }

    private function isNeedUpdateVariantPrice(
        VariantSku $variant,
        \M2E\TikTokShop\Model\Listing\InventorySync\Channel\ProductSku $channelVariant
    ): bool {
        if (!$this->product->isStatusListed()) {
            return false;
        }

        return $variant->getOnlineCurrentPrice() !== $channelVariant->getPrice();
    }

    private function isNeedForceChangeStatusByProduct(): bool
    {
        return !$this->product->isStatusListed();
    }

    private function isNeedChangeStatus(int $productStatus, int $channelStatus): bool
    {
        return $productStatus !== $channelStatus;
    }

    private function processProduct(): bool
    {
        if ($this->isNeedChangeStatus($this->product->getStatus(), $this->channelProduct->getStatus())) {
            $this->addInstructionData(
                Product::INSTRUCTION_TYPE_CHANNEL_STATUS_CHANGED,
                80,
            );

            $calculatedStatus = $this->calculateStatusByChannel->calculate(
                $this->product,
                $this->channelProduct->getStatus(),
            );
            if ($calculatedStatus === null) {
                throw new \M2E\TikTokShop\Model\Exception\Logic(
                    'Unable calculate status of channel product.',
                    [
                        'product' => $this->product->getId(),
                        'extension_status' => $this->product->getStatus(),
                        'channel_status' => $this->channelProduct->getStatus(),
                    ],
                );
            }

            $this->addLog($this->processNewStatus($calculatedStatus));
        }

        if ($this->isNeedUpdateManufacturerId()) {
            $this->addInstructionData(
                Product::INSTRUCTION_TYPE_CHANNEL_MANUFACTURER_CHANGED,
                20,
            );

            $this->product->setOnlineManufacturerId($this->channelProduct->getManufacturerId());
        }

        if ($this->isNeedUpdateResponsiblePersonId()) {
            $this->addInstructionData(
                Product::INSTRUCTION_TYPE_CHANNEL_RESPONSIBLE_PERSON_CHANGED,
                20,
            );

            $this->product->setOnlineResponsiblePersonIds($this->channelProduct->getResponsiblePersonIds());
        }

        $this->updateProductListingQuality();
        $this->updateAuditFailedReasons();

        return true;
    }

    private function processNewStatus(
        \M2E\TikTokShop\Model\Product\CalculateStatusByChannel\Result $calculatedStatus
    ): \M2E\TikTokShop\Model\Listing\Log\Record {
        switch ($calculatedStatus->getStatus()) {
            case \M2E\TikTokShop\Model\Product::STATUS_NOT_LISTED:
                $this->product->setStatusNotListed($calculatedStatus->getStatusChanger());
                break;

            case \M2E\TikTokShop\Model\Product::STATUS_BLOCKED:
                $this->product->setStatusBlocked($calculatedStatus->getStatusChanger());
                break;

            default:
                $this->product->setStatus($calculatedStatus->getStatus(), $calculatedStatus->getStatusChanger());
        }

        return $calculatedStatus->getMessageAboutChange();
    }

    private function addInstructionData(string $type, int $priority): void
    {
        $this->instructionsData[$type] = [
            'listing_product_id' => $this->product->getId(),
            'type' => $type,
            'priority' => $priority,
            'initiator' => 'channel_changes_synchronization',
        ];
    }

    private function addLog(\M2E\TikTokShop\Model\Listing\Log\Record $record): void
    {
        $this->logs[$record->getMessage()] = $record;
    }

    private function isNeedUpdateIdentifier(
        VariantSku $existVariant,
        \M2E\TikTokShop\Model\Listing\InventorySync\Channel\ProductSku $channelVariant
    ): bool {
        $existIdentifier = $existVariant->getOnlineIdentifier();
        $channelIdentifier = $channelVariant->getIdentifier();

        if (
            $existIdentifier === null
            && $channelIdentifier === null
        ) {
            return false;
        }

        /** @psalm-suppress RedundantCondition */
        if (
            $existIdentifier === null
            && $channelIdentifier !== null
        ) {
            return true;
        }

        return $existIdentifier->getId() !== $channelIdentifier->getId()
            || $existIdentifier->getType() !== $channelIdentifier->getType();
    }

    private function isNeedUpdateManufacturerId(): bool
    {
        return $this->channelProduct->getManufacturerId() !== null
            && $this->product->getOnlineManufacturerId() !== $this->channelProduct->getManufacturerId();
    }

    private function isNeedUpdateResponsiblePersonId(): bool
    {
        $channelResponsiblePersonIds = $this->channelProduct->getResponsiblePersonIds();
        if (empty($channelResponsiblePersonIds)) {
            return false;
        }

        $productResponsiblePersonIds = $this->product->getOnlineResponsiblePersonIds();
        arsort($productResponsiblePersonIds);
        arsort($channelResponsiblePersonIds);

        return $productResponsiblePersonIds === $channelResponsiblePersonIds;
    }

    private function updateProductListingQuality(): void
    {
        $channelListingQuality = $this->channelProduct->getListingQuality();
        $productListingQuality = $this
            ->listingQualityConverter
            ->toProductListingQuality($channelListingQuality);

        $this->product->setListingQuality($productListingQuality);

        $tags = [];
        foreach ($productListingQuality->getRecommendationCollection()->getAll() as $recommendation) {
            $tags[] = $this->tagFactory->createByErrorCode($recommendation->code, $recommendation->howToSolve);
        }

        $this->tagBuffer->addTags($this->product, $tags);
        $this->tagBuffer->flush();
    }

    private function updateAuditFailedReasons()
    {
        $auditFailedReasons = $this->channelProduct->getAuditFailedReasons();
        if ($this->channelProduct->getStatus() !== \M2E\TikTokShop\Model\Product::STATUS_BLOCKED) {
            $auditFailedReasons = [];
        }

        $this->product->setAuditFailedReasons($auditFailedReasons);
    }
}
