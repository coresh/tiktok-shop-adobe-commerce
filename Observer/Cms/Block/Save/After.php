<?php

namespace M2E\TikTokShop\Observer\Cms\Block\Save;

use M2E\TikTokShop\Model\Template\Description as Description;

class After extends \M2E\TikTokShop\Observer\AbstractObserver
{
    public const INSTRUCTION_INITIATOR = 'magento_static_block_observer';

    private \M2E\TikTokShop\Model\InstructionService $instructionService;
    private \M2E\TikTokShop\Model\ResourceModel\Template\Description\CollectionFactory $descriptionCollectionFactory;
    /** @var \M2E\TikTokShop\Model\Template\Description\AffectedListingsProductsFactory */
    private Description\AffectedListingsProductsFactory $affectedListingsProductsFactory;

    public function __construct(
        \M2E\TikTokShop\Model\Template\Description\AffectedListingsProductsFactory $affectedListingsProductsFactory,
        \M2E\TikTokShop\Model\ResourceModel\Template\Description\CollectionFactory $descriptionCollectionFactory,
        \M2E\TikTokShop\Model\InstructionService $instructionService,
        \M2E\TikTokShop\Helper\Factory $helperFactory
    ) {
        parent::__construct($helperFactory);

        $this->instructionService = $instructionService;
        $this->descriptionCollectionFactory = $descriptionCollectionFactory;
        $this->affectedListingsProductsFactory = $affectedListingsProductsFactory;
    }

    protected function process(): void
    {
        /** @var \Magento\Cms\Model\Block $block */
        $block = $this->getEvent()->getData('object');
        if ($block->getOrigData('content') == $block->getData('content')) {
            return;
        }

        $templates = $this->descriptionCollectionFactory->create();
        $conditions = [
            $templates->getConnection()->quoteInto(
                'description_template LIKE ?',
                '%id="' . $block->getIdentifier() . '"%',
            ),
            $templates->getConnection()->quoteInto(
                'description_template LIKE ?',
                '%id="' . $block->getId() . '"%',
            ),
        ];
        $templates->getSelect()->where(implode(' OR ', $conditions));

        foreach ($templates as $template) {
            /** @var \M2E\TikTokShop\Model\Template\Description $template */

            $affectedListingsProducts = $this->affectedListingsProductsFactory->create();
            $affectedListingsProducts->setModel($template);

            $listingsProductsInstructionsData = [];

            foreach ($affectedListingsProducts->getIds() as $listingProductId) {
                $listingsProductsInstructionsData[] = [
                    'listing_product_id' => (int)$listingProductId,
                    'type' => Description::INSTRUCTION_TYPE_MAGENTO_STATIC_BLOCK_IN_DESCRIPTION_CHANGED,
                    'initiator' => self::INSTRUCTION_INITIATOR,
                    'priority' => 30,
                ];
            }

            $this->instructionService->createBatch($listingsProductsInstructionsData);
        }
    }
}
