<?php

namespace M2E\TikTokShop\Model\MSI\Magento\Product;

use M2E\TikTokShop\Model\Magento\Product\Inventory\AbstractModel;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Api\GetProductSalableQtyInterface;
use Magento\InventorySalesApi\Api\StockResolverInterface;
use Magento\InventoryIndexer\Model\ResourceModel\GetStockItemData;
use Magento\InventoryReservations\Model\ResourceModel\GetReservationsQuantity;

class Inventory extends AbstractModel
{
    /** @var GetStockItemData */
    private $getStockItemData;
    /** @var GetProductSalableQtyInterface */
    private $salableQtyResolver;
    /** @var StockResolverInterface */
    private $stockResolver;
    private \M2E\Core\Helper\Magento\Store $magentoStoreHelper;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \M2E\Core\Helper\Magento\Store $magentoStoreHelper,
        \M2E\TikTokShop\Helper\Magento\Product $magentoProductHelper
    ) {
        parent::__construct($magentoProductHelper, $stockRegistry);
        $this->getStockItemData = $objectManager->get(GetStockItemData::class);
        $this->salableQtyResolver = $objectManager->create(
            GetProductSalableQtyInterface::class,
            [
                'getStockItemData' => $this->getStockItemData,
                'getReservationsQuantity' => $objectManager->get(GetReservationsQuantity::class),
            ]
        );
        $this->stockResolver = $objectManager->get(StockResolverInterface::class);
        $this->magentoStoreHelper = $magentoStoreHelper;
    }

    /**
     * @return int|mixed
     * @throws \M2E\TikTokShop\Model\Exception
     * @throws \M2E\TikTokShop\Model\Exception\Logic
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isInStock()
    {
        $stockItemData = $this->getStockItemData->execute(
            $this->getProduct()->getSku(),
            $this->getStock()->getStockId()
        );

        return $stockItemData === null ? 0 : $stockItemData[GetStockItemData::IS_SALABLE];
    }

    /**
     * @return float|int|mixed
     * @throws \M2E\TikTokShop\Model\Exception
     * @throws \M2E\TikTokShop\Model\Exception\Logic
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getQty()
    {
        try {
            $qty = $this->salableQtyResolver->execute($this->getProduct()->getSku(), $this->getStock()->getId());
        } catch (\Magento\InventoryConfigurationApi\Exception\SkuIsNotAssignedToStockException $exception) {
            $qty = 0;
        }

        return $qty;
    }

    /**
     * @return \Magento\InventoryApi\Api\Data\StockInterface
     * @throws \M2E\TikTokShop\Model\Exception
     * @throws \M2E\TikTokShop\Model\Exception\Logic
     * @throws \Magento\Framework\Exception\NoSuchEntityException|\Magento\Framework\Exception\LocalizedException
     */
    private function getStock()
    {
        $website = $this->getProduct()->getStoreId() === 0 ?
            $this->magentoStoreHelper->getDefaultWebsite() :
            $this->getProduct()->getStore()->getWebsite();

        return $this->stockResolver->execute(SalesChannelInterface::TYPE_WEBSITE, $website->getCode());
    }
}
