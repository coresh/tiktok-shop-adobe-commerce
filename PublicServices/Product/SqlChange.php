<?php

/*
    // $this->_objectManager instanceof \Magento\Framework\ObjectManagerInterface
    $model = $this->_objectManager->create('\M2E\TikTokShop\PublicServices\Product\SqlChange');

    // notify M2E TikTok Shop about some change of product with ID 17
    $model->markProductChanged(17);

    // make price change of product with ID 18 and then notify M2E TikTok Shop
    $model->markPriceWasChanged(18);

    // make QTY change of product with ID 19 and then notify M2E TikTok Shop
    $model->markQtyWasChanged(19);

    // make status change of product with ID 20 and then notify M2E TikTok Shop
    $model->markStatusWasChanged(20);

    $model->applyChanges();
*/

namespace M2E\TikTokShop\PublicServices\Product;

use M2E\TikTokShop\Model\ResourceModel\Instruction as InstructionResource;
use M2E\TikTokShop\Model\ResourceModel\Product as ProductResource;

class SqlChange
{
    public const VERSION = '2.0.1';

    public const INSTRUCTION_TYPE_PRODUCT_CHANGED = 'sql_change_product_changed';
    public const INSTRUCTION_TYPE_STATUS_CHANGED = 'sql_change_status_changed';
    public const INSTRUCTION_TYPE_QTY_CHANGED = 'sql_change_qty_changed';
    public const INSTRUCTION_TYPE_PRICE_CHANGED = 'sql_change_price_changed';

    public const INSTRUCTION_INITIATOR = 'public_services_sql_change_processor';

    protected bool $preventDuplicatesMode = true;
    protected array $changesData = [];

    protected \M2E\TikTokShop\Model\ActiveRecord\Factory $activeRecordFactory;
    protected \Magento\Framework\App\ResourceConnection $resource;
    private InstructionResource $listingProductInstructionResource;
    private \M2E\TikTokShop\Model\ResourceModel\Product $listingProductResource;
    private \M2E\TikTokShop\Model\InstructionService $instructionService;

    public function __construct(
        \M2E\TikTokShop\Model\InstructionService $instructionService,
        \M2E\TikTokShop\Model\ResourceModel\Product $listingProductResource,
        InstructionResource $listingProductInstructionResource,
        \M2E\TikTokShop\Model\ActiveRecord\Factory $activeRecordFactory,
        \Magento\Framework\App\ResourceConnection $resource
    ) {
        $this->activeRecordFactory = $activeRecordFactory;
        $this->resource = $resource;
        $this->listingProductInstructionResource = $listingProductInstructionResource;
        $this->listingProductResource = $listingProductResource;
        $this->instructionService = $instructionService;
    }

    public function enablePreventDuplicatesMode(): void
    {
        $this->preventDuplicatesMode = true;
    }

    public function disablePreventDuplicatesMode(): void
    {
        $this->preventDuplicatesMode = false;
    }

    /**
     * @return \M2E\TikTokShop\PublicServices\Product\SqlChange
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Db_Select_Exception
     * @throws \Zend_Db_Statement_Exception
     */
    public function applyChanges(): self
    {
        $instructionsData = $this->getInstructionsData();

        if ($this->preventDuplicatesMode) {
            $instructionsData = $this->filterExistedInstructions($instructionsData);
        }

        $this->instructionService->createBatch($instructionsData);

        $this->flushChanges();

        return $this;
    }

    public function flushChanges(): self
    {
        $this->changesData = [];

        return $this;
    }

    /**
     * Backward compatibility issue
     *
     * @param $productId
     *
     * @return $this
     */
    public function markQtyWasChanged($productId): self
    {
        return $this->markProductChanged($productId);
    }

    /**
     * Backward compatibility issue
     *
     * @param $productId
     *
     * @return $this
     */
    public function markPriceWasChanged($productId): self
    {
        return $this->markProductChanged($productId);
    }

    /**
     * Backward compatibility issue
     *
     * @param $productId
     *
     * @return $this
     */
    public function markStatusWasChanged($productId): self
    {
        return $this->markProductChanged($productId);
    }

    //----------------------------------------

    /**
     * @param $productId
     * @param $attributeCode
     * @param $storeId
     * @param $valueOld
     * @param $valueNew
     *
     * @return mixed
     * @throws \M2E\TikTokShop\Model\Exception\Logic
     */
    public function markProductAttributeChanged(
        $productId,
        $attributeCode,
        $storeId,
        $valueOld = null,
        $valueNew = null
    ) {
        throw new \M2E\TikTokShop\Model\Exception\Logic('Method is not supported.');
    }

    public function markProductChanged($productId): self
    {
        $this->changesData[] = [
            'product_id' => (int)$productId,
            'instruction_type' => self::INSTRUCTION_TYPE_PRODUCT_CHANGED,
        ];

        return $this;
    }

    public function markStatusChanged($productId): self
    {
        $this->changesData[] = [
            'product_id' => (int)$productId,
            'instruction_type' => self::INSTRUCTION_TYPE_STATUS_CHANGED,
        ];

        return $this;
    }

    public function markQtyChanged($productId): self
    {
        $this->changesData[] = [
            'product_id' => (int)$productId,
            'instruction_type' => self::INSTRUCTION_TYPE_QTY_CHANGED,
        ];

        return $this;
    }

    public function markPriceChanged($productId): self
    {
        $this->changesData[] = [
            'product_id' => (int)$productId,
            'instruction_type' => self::INSTRUCTION_TYPE_PRICE_CHANGED,
        ];

        return $this;
    }

    /**
     * @throws \Zend_Db_Select_Exception
     * @throws \Zend_Db_Statement_Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getInstructionsData(): array
    {
        if (empty($this->changesData)) {
            return [];
        }

        $productInstructionTypes = [];

        foreach ($this->changesData as $changeData) {
            $productId = (int)$changeData['product_id'];

            $productInstructionTypes[$productId][] = $changeData['instruction_type'];
            $productInstructionTypes[$productId] = array_unique($productInstructionTypes[$productId]);
        }

        $connection = $this->resource->getConnection();

        $instructionsData = [];

        foreach (array_chunk($productInstructionTypes, 1000, true) as $productInstructionTypesPart) {
            $simpleProductsSelect = $connection
                ->select()
                ->from(
                    $this->listingProductResource->getMainTable(),
                    [
                        'magento_product_id' => ProductResource::COLUMN_MAGENTO_PRODUCT_ID,
                        'listing_product_id' => ProductResource::COLUMN_ID,
                    ],
                )
                ->where(
                    sprintf('%s IN (?)', ProductResource::COLUMN_MAGENTO_PRODUCT_ID),
                    array_keys($productInstructionTypesPart),
                );

            $stmtQuery = $connection
                ->select()
                ->union([$simpleProductsSelect])
                ->query();

            while ($row = $stmtQuery->fetch()) {
                $magentoProductId = (int)$row['magento_product_id'];
                $listingProductId = (int)$row['listing_product_id'];

                foreach ($productInstructionTypesPart[$magentoProductId] as $instructionType) {
                    $instructionsData[] = [
                        'listing_product_id' => $listingProductId,
                        'type' => $instructionType,
                        'initiator' => self::INSTRUCTION_INITIATOR,
                        'priority' => 50,
                    ];
                }
            }
        }

        return $instructionsData;
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Db_Statement_Exception
     */
    protected function filterExistedInstructions(array $instructionsData): array
    {
        $indexedInstructionsData = [];

        foreach ($instructionsData as $instructionData) {
            $key = $instructionData['listing_product_id'] . '##' . $instructionData['type'];
            $indexedInstructionsData[$key] = $instructionData;
        }

        $connection = $this->resource->getConnection();

        $stmt = $connection
            ->select()
            ->from(
                $this->listingProductInstructionResource->getMainTable(),
                [
                    'listing_product_id' => InstructionResource::COLUMN_LISTING_PRODUCT_ID,
                    'type' => InstructionResource::COLUMN_TYPE
                ]
            )
            ->query();

        while ($row = $stmt->fetch()) {
            $listingProductId = (int)$row['listing_product_id'];
            $type = $row['type'];

            if (isset($indexedInstructionsData[$listingProductId . '##' . $type])) {
                unset($indexedInstructionsData[$listingProductId . '##' . $type]);
            }
        }

        return array_values($indexedInstructionsData);
    }
}
