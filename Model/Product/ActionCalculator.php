<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Product;

class ActionCalculator
{
    /** @var \M2E\TikTokShop\Model\Product\VariantSku\ActionCalculator */
    private VariantSku\ActionCalculator $variantActionCalculator;
    private \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Type\Revise\Checker $reviseChecker;

    public function __construct(
        \M2E\TikTokShop\Model\Product\VariantSku\ActionCalculator $variantActionCalculator,
        \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Type\Revise\Checker $reviseChecker
    ) {
        $this->variantActionCalculator = $variantActionCalculator;
        $this->reviseChecker = $reviseChecker;
    }

    public function calculate(\M2E\TikTokShop\Model\Product $product, bool $force, int $change): Action
    {
        if ($product->isStatusNotListed()) {
            return $this->calculateToList($product);
        }

        if ($product->isStatusListed()) {
            return $this->calculateToReviseOrStop($product, $force, $force, $force, $force, $force, false);
        }

        if ($product->isStatusInactive()) {
            return $this->calculateToRelist($product, $change);
        }

        return Action::createNothing($product);
    }

    public function calculateToList(\M2E\TikTokShop\Model\Product $product): Action
    {
        if (
            !$product->isListable()
            || !$product->isStatusNotListed()
        ) {
            return Action::createNothing($product);
        }

        if (!$this->isNeedListProduct($product)) {
            return Action::createNothing($product);
        }

        $variantSettings = $this->calculateVariants($product, false);
        if (!$variantSettings->hasAddAction()) {
            return Action::createNothing($product);
        }

        $configurator = new \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Configurator();
        $configurator->enableAll();

        return Action::createList($product, $configurator, $variantSettings);
    }

    private function isNeedListProduct(\M2E\TikTokShop\Model\Product $product): bool
    {
        $syncPolicy = $product->getSynchronizationTemplate();

        if (!$syncPolicy->isListMode()) {
            return false;
        }

        if (
            $syncPolicy->isListStatusEnabled()
            && !$product->getMagentoProduct()->isStatusEnabled()
        ) {
            return false;
        }

        if (
            $syncPolicy->isListIsInStock()
            && !$product->getMagentoProduct()->isStockAvailability()
        ) {
            return false;
        }

        return true;
    }

    // ----------------------------------------

    public function calculateToReviseOrStop(
        \M2E\TikTokShop\Model\Product $product,
        bool $isDetectChangeTitle,
        bool $isDetectChangeDescription,
        bool $isDetectChangeImages,
        bool $isDetectChangeCategories,
        bool $isDetectChangeOther,
        bool $needForceRevise
    ): Action {
        if (
            !$product->isRevisable()
            && !$product->isStoppable()
        ) {
            return Action::createNothing($product);
        }

        if ($this->isNeedStopProduct($product)) {
            return Action::createStop($product);
        }

        $variantSettings = $this->calculateVariants($product, $needForceRevise);
        if ($variantSettings->isAllStopAction()) {
            return Action::createStop($product);
        }

        $configurator = new \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Configurator();
        $configurator->disableAll();

        $this->updateConfiguratorAddVariants($configurator, $variantSettings);

        $this->updateConfiguratorAddTitle(
            $configurator,
            $product,
            $isDetectChangeTitle,
        );
        $this->updateConfiguratorAddDescription(
            $configurator,
            $product,
            $isDetectChangeDescription,
        );
        $this->updateConfiguratorAddImages(
            $configurator,
            $product,
            $isDetectChangeImages,
        );
        $this->updateConfiguratorAddCategories(
            $configurator,
            $product,
            $isDetectChangeCategories,
        );
        $this->updateConfiguratorAddOther(
            $configurator,
            $product,
            $isDetectChangeOther,
        );

        if (empty($configurator->getEnabledDataTypes())) {
            return Action::createNothing($product);
        }

        return Action::createRevise($product, $configurator, $variantSettings);
    }

    private function isNeedStopProduct(\M2E\TikTokShop\Model\Product $product): bool
    {
        $syncPolicy = $product->getSynchronizationTemplate();

        if (!$syncPolicy->isStopMode()) {
            return false;
        }

        if (
            $syncPolicy->isStopStatusDisabled()
            && !$product->getMagentoProduct()->isStatusEnabled()
        ) {
            return true;
        }

        if (
            $syncPolicy->isStopOutOfStock()
            && !$product->getMagentoProduct()->isStockAvailability()
        ) {
            return true;
        }

        return false;
    }

    private function updateConfiguratorAddVariants(
        \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Configurator $configurator,
        \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\VariantSettings $variantSettings
    ): void {
        if (
            $variantSettings->hasAddAction()
            || $variantSettings->hasReviseAction()
        ) {
            $configurator->allowVariants();

            return;
        }

        $configurator->disallowVariants();
    }

    private function updateConfiguratorAddTitle(
        \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Configurator $configurator,
        \M2E\TikTokShop\Model\Product $product,
        bool $hasInstructionsForUpdateTitle
    ): void {
        if (!$hasInstructionsForUpdateTitle) {
            return;
        }

        if ($this->reviseChecker->isNeedReviseForTitle($product)) {
            $configurator->allowTitle();

            return;
        }

        $configurator->disallowTitle();
    }

    private function updateConfiguratorAddDescription(
        \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Configurator $configurator,
        \M2E\TikTokShop\Model\Product $product,
        bool $hasInstructionsForUpdateDescription
    ): void {
        if (!$hasInstructionsForUpdateDescription) {
            return;
        }

        if ($this->reviseChecker->isNeedReviseForDescription($product)) {
            $configurator->allowDescription();

            return;
        }

        $configurator->disallowDescription();
    }

    private function updateConfiguratorAddImages(
        \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Configurator $configurator,
        \M2E\TikTokShop\Model\Product $product,
        bool $hasInstructionsForUpdateImages
    ): void {
        if (!$hasInstructionsForUpdateImages) {
            return;
        }

        if ($this->reviseChecker->isNeedReviseForImages($product)) {
            $configurator->allowImages();

            return;
        }

        $configurator->disallowImages();
    }

    private function updateConfiguratorAddCategories(
        \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Configurator $configurator,
        \M2E\TikTokShop\Model\Product $product,
        bool $hasInstructionsForUpdateCategories
    ): void {
        if (!$hasInstructionsForUpdateCategories) {
            return;
        }

        if (!$product->hasCategoryTemplate()) {
            return;
        }

        if (
            $this->reviseChecker->isNeedReviseForCategories($product)
            || $this->reviseChecker->isNeedReviseForBrand($product)
            || $this->reviseChecker->isNeedReviseForSizeChart($product)
            || $this->reviseChecker->isNeedReviseForCertificates($product)
        ) {
            $configurator->allowCategories();

            return;
        }

        $configurator->disallowCategories();
    }

    private function updateConfiguratorAddOther(
        \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Configurator $configurator,
        \M2E\TikTokShop\Model\Product $product,
        bool $hasInstructionsForUpdateOther
    ): void {
        if (!$hasInstructionsForUpdateOther) {
            return;
        }

        if ($this->reviseChecker->isNeedReviseForOther($product)) {
            $configurator->allowOther();

            return;
        }

        $configurator->disallowOther();
    }

    // ----------------------------------------

    public function calculateToRelist(\M2E\TikTokShop\Model\Product $product, int $changer): Action
    {
        if (!$product->isRelistable()) {
            return Action::createNothing($product);
        }

        if (!$this->isNeedRelistProduct($product, $changer)) {
            return Action::createNothing($product);
        }

        $variantSettings = $this->calculateVariants($product, false);
        if (!$variantSettings->hasAddAction()) {
            return Action::createNothing($product);
        }

        $configurator = new \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Configurator();
        $configurator->enableAll();

        return Action::createRelist($product, $configurator, $variantSettings);
    }

    private function isNeedRelistProduct(\M2E\TikTokShop\Model\Product $product, int $changer): bool
    {
        $syncPolicy = $product->getSynchronizationTemplate();

        if (!$syncPolicy->isRelistMode()) {
            return false;
        }

        if (
            $product->isStatusInactive()
            && $syncPolicy->isRelistFilterUserLock()
            && $product->isStatusChangerUser()
            && $changer !== \M2E\TikTokShop\Model\Product::STATUS_CHANGER_USER
        ) {
            return false;
        }

        if (
            $syncPolicy->isRelistStatusEnabled()
            && !$product->getMagentoProduct()->isStatusEnabled()
        ) {
            return false;
        }

        if (
            $syncPolicy->isRelistIsInStock()
            && !$product->getMagentoProduct()->isStockAvailability()
        ) {
            return false;
        }

        return true;
    }

    // ----------------------------------------

    private function calculateVariants(
        \M2E\TikTokShop\Model\Product $product,
        bool $needForceRevise
    ): \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\VariantSettings {
        $variantSettingsBuilder = new \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\VariantSettingsBuilder(
            $needForceRevise
        );
        foreach ($product->getVariants() as $variant) {
            $action = $this->variantActionCalculator->process($variant);

            $variantSettingsBuilder->add($variant->getId(), $action, $variant->getStatus());
        }

        return $variantSettingsBuilder->build();
    }
}
