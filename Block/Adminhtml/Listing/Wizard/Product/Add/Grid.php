<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Block\Adminhtml\Listing\Wizard\Product\Add;

class Grid extends \M2E\TikTokShop\Block\Adminhtml\Listing\Wizard\AbstractGrid
{
    private \Magento\Store\Model\WebsiteFactory $websiteFactory;

    public function __construct(
        \M2E\TikTokShop\Model\ResourceModel\Listing $listingResource,
        \M2E\TikTokShop\Model\ResourceModel\Product $productResource,
        \M2E\TikTokShop\Model\Listing\Wizard\Ui\RuntimeStorage $uiWizardRuntimeStorage,
        \M2E\TikTokShop\Model\Listing\Ui\RuntimeStorage $uiListingRuntimeStorage,
        \M2E\TikTokShop\Model\ResourceModel\Listing\Wizard $wizardResource,
        \M2E\TikTokShop\Model\ResourceModel\Listing\Wizard\Product $listingWizardProductResource,
        \M2E\TikTokShop\Model\ResourceModel\Magento\Product\CollectionFactory $magentoProductCollectionFactory,
        \Magento\Catalog\Model\Product\Type $type,
        \M2E\TikTokShop\Helper\Magento\Product $magentoProductHelper,
        \Magento\Store\Model\WebsiteFactory $websiteFactory,
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \M2E\TikTokShop\Helper\Data $dataHelper,
        \M2E\TikTokShop\Helper\Data\GlobalData $globalDataHelper,
        \M2E\TikTokShop\Helper\Data\Session $sessionHelper,
        array $data = []
    ) {
        $this->websiteFactory = $websiteFactory;
        parent::__construct(
            $listingResource,
            $productResource,
            $uiWizardRuntimeStorage,
            $uiListingRuntimeStorage,
            $wizardResource,
            $listingWizardProductResource,
            $magentoProductCollectionFactory,
            $type,
            $magentoProductHelper,
            $context,
            $backendHelper,
            $dataHelper,
            $globalDataHelper,
            $sessionHelper,
            $data,
        );
    }

    protected function _prepareColumns()
    {
        $this->addColumnAfter('visibility', [
            'header' => __('Visibility'),
            'align' => 'left',
            'width' => '90px',
            'type' => 'options',
            'sortable' => false,
            'index' => 'visibility',
            'filter_index' => 'visibility',
            'options' => \Magento\Catalog\Model\Product\Visibility::getOptionArray(),
        ], 'qty');

        $this->addColumnAfter('status', [
            'header' => __('Status'),
            'align' => 'left',
            'width' => '90px',
            'type' => 'options',
            'sortable' => false,
            'index' => 'status',
            'filter_index' => 'status',
            'options' => \Magento\Catalog\Model\Product\Attribute\Source\Status::getOptionArray(),
            'frame_callback' => [$this, 'callbackColumnStatus'],
        ], 'visibility');

        if (!$this->_storeManager->isSingleStoreMode()) {
            $this->addColumnAfter('websites', [
                'header' => __('Websites'),
                'align' => 'left',
                'width' => '90px',
                'type' => 'options',
                'sortable' => false,
                'index' => 'websites',
                'filter_index' => 'websites',
                'options' => $this->websiteFactory->create()->getCollection()->toOptionHash(),
                'frame_callback' => [$this, 'callbackColumnWebsites'],
            ], 'status');
        }

        return parent::_prepareColumns();
    }

    protected function getSelectedProductsCallback()
    {
        return <<<JS
(function() {
    return function(callback) {
        return callback && callback({$this->getId()}_massactionJsObject.checkedString)
    }
})()
JS;
    }

    public function callbackColumnWebsites($value, $row)
    {
        if ($value === null) {
            $websites = [];
            foreach ($row->getWebsiteIds() as $websiteId) {
                $websites[] = $this->_storeManager->getWebsite($websiteId)->getName();
            }

            return implode(', ', $websites);
        }

        return $value;
    }
}
