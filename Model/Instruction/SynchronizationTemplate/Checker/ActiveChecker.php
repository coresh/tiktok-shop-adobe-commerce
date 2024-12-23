<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Instruction\SynchronizationTemplate\Checker;

use M2E\TikTokShop\Model\Magento\Product\ChangeAttributeTracker;
use M2E\TikTokShop\Model\Product;
use M2E\TikTokShop\Model\Template\Description as DescriptionPolicy;
use M2E\TikTokShop\Model\Template\Synchronization as SyncPolicy;
use M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Configurator;
use M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\VariantSettings;

class ActiveChecker extends \M2E\TikTokShop\Model\Instruction\SynchronizationTemplate\Checker\AbstractChecker
{
    private \M2E\TikTokShop\Model\ScheduledAction\CreateService $scheduledActionCreate;
    private \M2E\TikTokShop\Model\ScheduledAction\Repository $scheduledActionRepository;
    private \M2E\TikTokShop\Model\Product\ActionCalculator $actionCalculator;

    public function __construct(
        \M2E\TikTokShop\Model\ScheduledAction\CreateService $scheduledActionCreate,
        \M2E\TikTokShop\Model\ScheduledAction\Repository $scheduledActionRepository,
        \M2E\TikTokShop\Model\Instruction\SynchronizationTemplate\Checker\Input $input,
        \M2E\TikTokShop\Model\Product\ActionCalculator $actionCalculator
    ) {
        parent::__construct($input);
        $this->scheduledActionCreate = $scheduledActionCreate;
        $this->scheduledActionRepository = $scheduledActionRepository;
        $this->actionCalculator = $actionCalculator;
    }

    public function isAllowed(): bool
    {
        if (!parent::isAllowed()) {
            return false;
        }

        if (
            !$this->getInput()->hasInstructionWithTypes($this->getStopInstructionsTypes())
            && !$this->getInput()->hasInstructionWithTypes($this->getReviseInstructionsTypes())
        ) {
            return false;
        }

        $listingProduct = $this->getInput()->getListingProduct();

        if (
            !$listingProduct->isRevisable()
            && !$listingProduct->isStoppable()
        ) {
            return false;
        }

        if (!$listingProduct->hasCategoryTemplate()) {
            return false;
        }

        return true;
    }

    public function process(): void
    {
        $product = $this->getInput()->getListingProduct();

        $calculateResult = $this->actionCalculator->calculateToReviseOrStop(
            $product,
            $this->getInput()->hasInstructionWithTypes($this->getReviseTitleInstructionTypes()),
            $this->getInput()->hasInstructionWithTypes($this->getReviseDescriptionInstructionTypes()),
            $this->getInput()->hasInstructionWithTypes($this->getReviseImagesInstructionTypes()),
            $this->getInput()->hasInstructionWithTypes($this->getReviseCategoriesInstructionTypes()),
            $this->getInput()->hasInstructionWithTypes($this->getReviseOtherInstructionTypes()),
            $this->getInput()->hasInstructionWithTypes($this->getForceRevise()),
        );

        if (
            !$calculateResult->isActionStop()
            && !$calculateResult->isActionRevise()
        ) {
            $this->tryRemoveExistScheduledAction();

            return;
        }

        if ($calculateResult->isActionStop()) {
            $this->returnWithStopAction();

            return;
        }

        if (
            $this->getInput()->getScheduledAction() !== null
            && $this->getInput()->getScheduledAction()->isActionTypeRevise()
            && $this->getInput()->getScheduledAction()->isForce()
        ) {
            return;
        }

        $this->createReviseScheduledAction(
            $product,
            $calculateResult->getConfigurator(),
            $calculateResult->getVariantSettings(),
        );
    }

    // ----------------------------------------

    private function returnWithStopAction(): void
    {
        $scheduledAction = $this->getInput()->getScheduledAction();
        if ($scheduledAction === null) {
            $this->createStopScheduledAction($this->getInput()->getListingProduct());

            return;
        }

        if ($scheduledAction->isActionTypeStop()) {
            return;
        }

        $this->scheduledActionRepository->remove($scheduledAction);

        $this->createStopScheduledAction($this->getInput()->getListingProduct());
    }

    private function createStopScheduledAction(Product $product): void
    {
        $this->scheduledActionCreate->create(
            $product,
            \M2E\TikTokShop\Model\Product::ACTION_STOP,
            \M2E\TikTokShop\Model\Product::STATUS_CHANGER_SYNCH,
            [],
        );
    }

    private function createReviseScheduledAction(
        Product $product,
        Configurator $configurator,
        VariantSettings $variantSettings
    ): void {
        $this->scheduledActionCreate->create(
            $product,
            \M2E\TikTokShop\Model\Product::ACTION_REVISE,
            \M2E\TikTokShop\Model\Product::STATUS_CHANGER_SYNCH,
            [],
            $configurator->getEnabledDataTypes(),
            false,
            $configurator,
            $variantSettings,
        );
    }

    private function tryRemoveExistScheduledAction(): void
    {
        if ($this->getInput()->getScheduledAction() === null) {
            return;
        }

        $this->scheduledActionRepository->remove($this->getInput()->getScheduledAction());
    }

    // ----------------------------------------

    /**
     * @return string[]
     */
    private function getReviseInstructionsTypes(): array
    {
        return array_unique(
            array_merge(
                $this->getForceRevise(),
                $this->getReviseQtyInstructionTypes(),
                $this->getRevisePriceInstructionTypes(),
                $this->getReviseTitleInstructionTypes(),
                $this->getReviseDescriptionInstructionTypes(),
                $this->getReviseImagesInstructionTypes(),
                $this->getReviseCategoriesInstructionTypes(),
                $this->getReviseOtherInstructionTypes(),
            ),
        );
    }

    protected function getForceRevise(): array
    {
        return [
            \M2E\TikTokShop\Model\Product::INSTRUCTION_TYPE_VARIANT_SKU_REMOVED
        ];
    }

    protected function getReviseQtyInstructionTypes(): array
    {
        return [
            ChangeAttributeTracker::INSTRUCTION_TYPE_PRODUCT_DATA_POTENTIALLY_CHANGED,
            \M2E\TikTokShop\Model\Template\ChangeProcessorAbstract::INSTRUCTION_TYPE_QTY_DATA_CHANGED,
            SyncPolicy\ChangeProcessor::INSTRUCTION_TYPE_REVISE_QTY_ENABLED,
            SyncPolicy\ChangeProcessor::INSTRUCTION_TYPE_REVISE_QTY_DISABLED,
            SyncPolicy\ChangeProcessor::INSTRUCTION_TYPE_REVISE_QTY_SETTINGS_CHANGED,
            Product::INSTRUCTION_TYPE_CHANNEL_QTY_CHANGED,
            \M2E\TikTokShop\Model\Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_OTHER,
            \M2E\TikTokShop\Model\Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_LISTING,
            \M2E\TikTokShop\Model\Listing::INSTRUCTION_TYPE_PRODUCT_REMAP_FROM_LISTING,
            \M2E\TikTokShop\Model\Listing::INSTRUCTION_TYPE_CHANGE_LISTING_STORE_VIEW,
            \M2E\TikTokShop\PublicServices\Product\SqlChange::INSTRUCTION_TYPE_PRODUCT_CHANGED,
            \M2E\TikTokShop\PublicServices\Product\SqlChange::INSTRUCTION_TYPE_STATUS_CHANGED,
            \M2E\TikTokShop\PublicServices\Product\SqlChange::INSTRUCTION_TYPE_QTY_CHANGED,
            \M2E\TikTokShop\Model\Cron\Task\Product\InspectDirectChanges::INSTRUCTION_TYPE,
        ];
    }

    protected function getRevisePriceInstructionTypes(): array
    {
        return [
            ChangeAttributeTracker::INSTRUCTION_TYPE_PRODUCT_DATA_POTENTIALLY_CHANGED,
            \M2E\TikTokShop\Model\Template\ChangeProcessorAbstract::INSTRUCTION_TYPE_PRICE_DATA_CHANGED,
            SyncPolicy\ChangeProcessor::INSTRUCTION_TYPE_REVISE_PRICE_ENABLED,
            SyncPolicy\ChangeProcessor::INSTRUCTION_TYPE_REVISE_PRICE_DISABLED,
            Product::INSTRUCTION_TYPE_CHANNEL_PRICE_CHANGED,
            \M2E\TikTokShop\Model\Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_OTHER,
            \M2E\TikTokShop\Model\Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_LISTING,
            \M2E\TikTokShop\Model\Listing::INSTRUCTION_TYPE_PRODUCT_REMAP_FROM_LISTING,
            \M2E\TikTokShop\Model\Listing::INSTRUCTION_TYPE_CHANGE_LISTING_STORE_VIEW,
            \M2E\TikTokShop\PublicServices\Product\SqlChange::INSTRUCTION_TYPE_PRODUCT_CHANGED,
            \M2E\TikTokShop\PublicServices\Product\SqlChange::INSTRUCTION_TYPE_PRICE_CHANGED,
            \M2E\TikTokShop\Model\Cron\Task\Product\InspectDirectChanges::INSTRUCTION_TYPE,
        ];
    }

    protected function getReviseTitleInstructionTypes(): array
    {
        return [
            ChangeAttributeTracker::INSTRUCTION_TYPE_TITLE_DATA_CHANGED,
            \M2E\TikTokShop\Model\Template\ChangeProcessorAbstract::INSTRUCTION_TYPE_TITLE_DATA_CHANGED,
            SyncPolicy\ChangeProcessor::INSTRUCTION_TYPE_REVISE_TITLE_ENABLED,
            SyncPolicy\ChangeProcessor::INSTRUCTION_TYPE_REVISE_TITLE_DISABLED,
            \M2E\TikTokShop\Model\Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_OTHER,
            \M2E\TikTokShop\Model\Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_LISTING,
            \M2E\TikTokShop\Model\Listing::INSTRUCTION_TYPE_PRODUCT_REMAP_FROM_LISTING,
            \M2E\TikTokShop\Model\Listing::INSTRUCTION_TYPE_CHANGE_LISTING_STORE_VIEW,
            \M2E\TikTokShop\PublicServices\Product\SqlChange::INSTRUCTION_TYPE_PRODUCT_CHANGED,
            \M2E\TikTokShop\Model\Cron\Task\Product\InspectDirectChanges::INSTRUCTION_TYPE,
        ];
    }

    protected function getReviseDescriptionInstructionTypes(): array
    {
        return [
            ChangeAttributeTracker::INSTRUCTION_TYPE_DESCRIPTION_DATA_CHANGED,
            \M2E\TikTokShop\Model\Template\ChangeProcessorAbstract::INSTRUCTION_TYPE_DESCRIPTION_DATA_CHANGED,
            SyncPolicy\ChangeProcessor::INSTRUCTION_TYPE_REVISE_DESCRIPTION_ENABLED,
            SyncPolicy\ChangeProcessor::INSTRUCTION_TYPE_REVISE_DESCRIPTION_DISABLED,
            \M2E\TikTokShop\Model\Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_OTHER,
            \M2E\TikTokShop\Model\Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_LISTING,
            \M2E\TikTokShop\Model\Listing::INSTRUCTION_TYPE_PRODUCT_REMAP_FROM_LISTING,
            \M2E\TikTokShop\Model\Listing::INSTRUCTION_TYPE_CHANGE_LISTING_STORE_VIEW,
            \M2E\TikTokShop\PublicServices\Product\SqlChange::INSTRUCTION_TYPE_PRODUCT_CHANGED,
            \M2E\TikTokShop\Model\Cron\Task\Product\InspectDirectChanges::INSTRUCTION_TYPE,
            DescriptionPolicy::INSTRUCTION_TYPE_MAGENTO_STATIC_BLOCK_IN_DESCRIPTION_CHANGED,
        ];
    }

    protected function getReviseImagesInstructionTypes(): array
    {
        return [
            ChangeAttributeTracker::INSTRUCTION_TYPE_IMAGES_DATA_CHANGED,
            \M2E\TikTokShop\Model\Template\ChangeProcessorAbstract::INSTRUCTION_TYPE_IMAGES_DATA_CHANGED,
            SyncPolicy\ChangeProcessor::INSTRUCTION_TYPE_REVISE_IMAGES_ENABLED,
            SyncPolicy\ChangeProcessor::INSTRUCTION_TYPE_REVISE_IMAGES_DISABLED,
            \M2E\TikTokShop\Model\Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_OTHER,
            \M2E\TikTokShop\Model\Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_LISTING,
            \M2E\TikTokShop\Model\Listing::INSTRUCTION_TYPE_PRODUCT_REMAP_FROM_LISTING,
            \M2E\TikTokShop\Model\Listing::INSTRUCTION_TYPE_CHANGE_LISTING_STORE_VIEW,
            \M2E\TikTokShop\PublicServices\Product\SqlChange::INSTRUCTION_TYPE_PRODUCT_CHANGED,
            \M2E\TikTokShop\Model\Cron\Task\Product\InspectDirectChanges::INSTRUCTION_TYPE,
        ];
    }

    protected function getReviseCategoriesInstructionTypes(): array
    {
        return [
            ChangeAttributeTracker::INSTRUCTION_TYPE_CATEGORIES_DATA_CHANGED,
            \M2E\TikTokShop\Model\Template\ChangeProcessorAbstract::INSTRUCTION_TYPE_CATEGORIES_DATA_CHANGED,
            SyncPolicy\ChangeProcessor::INSTRUCTION_TYPE_REVISE_CATEGORIES_ENABLED,
            SyncPolicy\ChangeProcessor::INSTRUCTION_TYPE_REVISE_CATEGORIES_DISABLED,
            \M2E\TikTokShop\Model\Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_OTHER,
            \M2E\TikTokShop\Model\Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_LISTING,
            \M2E\TikTokShop\Model\Listing::INSTRUCTION_TYPE_PRODUCT_REMAP_FROM_LISTING,
            \M2E\TikTokShop\Model\Listing::INSTRUCTION_TYPE_CHANGE_LISTING_STORE_VIEW,
            \M2E\TikTokShop\PublicServices\Product\SqlChange::INSTRUCTION_TYPE_PRODUCT_CHANGED,
            \M2E\TikTokShop\Model\Cron\Task\Product\InspectDirectChanges::INSTRUCTION_TYPE,
        ];
    }

    protected function getReviseOtherInstructionTypes(): array
    {
        return [
            \M2E\TikTokShop\Model\Template\ChangeProcessorAbstract::INSTRUCTION_TYPE_OTHER_DATA_CHANGED,
            SyncPolicy\ChangeProcessor::INSTRUCTION_TYPE_REVISE_OTHER_ENABLED,
            SyncPolicy\ChangeProcessor::INSTRUCTION_TYPE_REVISE_OTHER_DISABLED,
            SyncPolicy\ChangeProcessor::INSTRUCTION_TYPE_REVISE_COMPLIANCE_ENABLED,
            SyncPolicy\ChangeProcessor::INSTRUCTION_TYPE_REVISE_COMPLIANCE_DISABLED,
            \M2E\TikTokShop\Model\Template\ChangeProcessorAbstract::INSTRUCTION_TYPE_COMPLIANCE_DATA_CHANGED,
            Product::INSTRUCTION_TYPE_CHANNEL_MANUFACTURER_CHANGED,
            Product::INSTRUCTION_TYPE_CHANNEL_RESPONSIBLE_PERSON_CHANGED,
            \M2E\TikTokShop\Model\Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_OTHER,
            \M2E\TikTokShop\Model\Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_LISTING,
            \M2E\TikTokShop\Model\Listing::INSTRUCTION_TYPE_PRODUCT_REMAP_FROM_LISTING,
            \M2E\TikTokShop\Model\Listing::INSTRUCTION_TYPE_CHANGE_LISTING_STORE_VIEW,
            \M2E\TikTokShop\PublicServices\Product\SqlChange::INSTRUCTION_TYPE_PRODUCT_CHANGED,
            \M2E\TikTokShop\Model\Cron\Task\Product\InspectDirectChanges::INSTRUCTION_TYPE,
        ];
    }

    /**
     * @return string[]
     */
    private function getStopInstructionsTypes(): array
    {
        return [
            ChangeAttributeTracker::INSTRUCTION_TYPE_PRODUCT_DATA_POTENTIALLY_CHANGED,
            SyncPolicy\ChangeProcessorAbstract::INSTRUCTION_TYPE_STOP_MODE_ENABLED,
            SyncPolicy\ChangeProcessorAbstract::INSTRUCTION_TYPE_STOP_MODE_DISABLED,
            SyncPolicy\ChangeProcessorAbstract::INSTRUCTION_TYPE_STOP_SETTINGS_CHANGED,
            \M2E\TikTokShop\Model\Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_OTHER,
            \M2E\TikTokShop\Model\Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_LISTING,
            \M2E\TikTokShop\Model\Listing::INSTRUCTION_TYPE_PRODUCT_REMAP_FROM_LISTING,
            \M2E\TikTokShop\Model\Listing::INSTRUCTION_TYPE_CHANGE_LISTING_STORE_VIEW,
            Product::INSTRUCTION_TYPE_CHANNEL_QTY_CHANGED,
            Product::INSTRUCTION_TYPE_CHANNEL_STATUS_CHANGED,
            \M2E\TikTokShop\Model\Template\ChangeProcessorAbstract::INSTRUCTION_TYPE_QTY_DATA_CHANGED,
            \M2E\TikTokShop\PublicServices\Product\SqlChange::INSTRUCTION_TYPE_PRODUCT_CHANGED,
            \M2E\TikTokShop\PublicServices\Product\SqlChange::INSTRUCTION_TYPE_STATUS_CHANGED,
            \M2E\TikTokShop\PublicServices\Product\SqlChange::INSTRUCTION_TYPE_QTY_CHANGED,
            \M2E\TikTokShop\Model\Cron\Task\Product\InspectDirectChanges::INSTRUCTION_TYPE,
        ];
    }
}
