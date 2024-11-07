<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Instruction\SynchronizationTemplate\Checker;

abstract class AbstractChecker
{
    private Input $input;

    public function __construct(
        \M2E\TikTokShop\Model\Instruction\SynchronizationTemplate\Checker\Input $input
    ) {
        $this->input = $input;
    }

    // ----------------------------------------

    public function isAllowed(): bool
    {
        $listingProduct = $this->getInput()->getListingProduct();

        if (!$listingProduct->getMagentoProduct()->exists()) {
            return false;
        }

        if ($listingProduct->hasBlockingByError()) {
            return false;
        }

        return true;
    }

    abstract public function process(): void;

    // ----------------------------------------

    protected function getInput(): \M2E\TikTokShop\Model\Instruction\SynchronizationTemplate\Checker\Input
    {
        return $this->input;
    }
}
