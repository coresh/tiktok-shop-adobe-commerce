<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Block\Adminhtml\Listing\View;

class Header extends \M2E\TikTokShop\Block\Adminhtml\Magento\AbstractBlock
{
    protected $_template = 'listing/view/header.phtml';
    private \M2E\Core\Helper\Magento\Store $magentoStoreHelper;
    private \M2E\TikTokShop\Model\Listing\Ui\RuntimeStorage $uiListingRuntimeStorage;

    public function __construct(
        \M2E\TikTokShop\Model\Listing\Ui\RuntimeStorage $uiListingRuntimeStorage,
        \M2E\Core\Helper\Magento\Store $magentoStoreHelper,
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Template $context,
        array $data = []
    ) {
        $this->magentoStoreHelper = $magentoStoreHelper;
        $this->uiListingRuntimeStorage = $uiListingRuntimeStorage;
        parent::__construct($context, $data);
    }

    public function isListingViewMode(): bool
    {
        return (bool)$this->getData('listing_view_mode');
    }

    public function getProfileTitle(): string
    {
        return $this->cutLongLines($this->getListing()->getTitle());
    }

    public function getAccountTitle(): string
    {
        return $this->cutLongLines($this->getListing()->getAccount()->getTitle());
    }

    public function getShopName(): string
    {
        return $this->cutLongLines($this->getListing()->getShop()->getShopNameWithRegion());
    }

    public function getStoreViewBreadcrumb(bool $cutLongValues = true): string
    {
        $breadcrumb = $this->magentoStoreHelper->getStorePath($this->getListing()->getStoreId());

        return $cutLongValues ? $this->cutLongLines($breadcrumb) : $breadcrumb;
    }

    private function cutLongLines($line): string
    {
        if (strlen($line) < 50) {
            return $line;
        }

        return substr($line, 0, 50) . '...';
    }

    private function getListing(): \M2E\TikTokShop\Model\Listing
    {
        if (!$this->uiListingRuntimeStorage->hasListing()) {
            throw new \LogicException('Listing was not initialized.');
        }

        return $this->uiListingRuntimeStorage->getListing();
    }
}
