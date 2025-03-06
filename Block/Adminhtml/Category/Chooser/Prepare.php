<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Block\Adminhtml\Category\Chooser;

class Prepare extends \M2E\TikTokShop\Block\Adminhtml\Magento\AbstractBlock
{
    private \M2E\TikTokShop\Model\Listing\Ui\RuntimeStorage $uiListingRuntimeStorage;

    public function __construct(
        \M2E\TikTokShop\Model\Listing\Ui\RuntimeStorage $uiListingRuntimeStorage,
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Template $context,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->uiListingRuntimeStorage = $uiListingRuntimeStorage;
    }

    public function _construct(): void
    {
        parent::_construct();
        $this->setTemplate('category/chooser/prepare.phtml');

        $this->jsPhp->addConstants(
            \M2E\TikTokShop\Helper\Data::getClassConstants(\M2E\TikTokShop\Model\TikTokShop\Template\Category::class),
        );

        $urlBuilder = $this->_urlBuilder;

        $this->jsUrl->addUrls(
            [
                'tiktokshop_category/editCategory' => $urlBuilder->getUrl(
                    '*/tiktokshop_category/editCategory'
                ),
                'tiktokshop_category/getCategoryAttributesHtml' => $urlBuilder->getUrl(
                    '*/tiktokshop_category/getCategoryAttributesHtml'
                ),
                'tiktokshop_category/getChildCategories' => $urlBuilder->getUrl(
                    '*/tiktokshop_category/getChildCategories'
                ),
                'tiktokshop_category/getChooserEditHtml' => $urlBuilder->getUrl(
                    '*/tiktokshop_category/getChooserEditHtml'
                ),
                'tiktokshop_category/getCountsOfAttributes' => $urlBuilder->getUrl(
                    '*/tiktokshop_category/getCountsOfAttributes'
                ),
                'tiktokshop_category/getEditedCategoryInfo' => $urlBuilder->getUrl(
                    '*/tiktokshop_category/getEditedCategoryInfo'
                ),
                'tiktokshop_category/getRecent' => $urlBuilder->getUrl(
                    '*/tiktokshop_category/getRecent'
                ),
                'tiktokshop_category/getSelectedCategoryDetails' => $urlBuilder->getUrl(
                    '*/tiktokshop_category/getSelectedCategoryDetails'
                ),
                'tiktokshop_category/saveCategoryAttributes' => $urlBuilder->getUrl(
                    '*/tiktokshop_category/saveCategoryAttributes'
                ),
                'tiktokshop_category/saveCategoryAttributesAjax' => $urlBuilder->getUrl(
                    '*/tiktokshop_category/saveCategoryAttributesAjax'
                ),
            ],
        );

        $this->jsTranslator->addTranslations([
            'Select' => __('Select'),
            'Reset' => __('Reset'),
            'No recently used Categories' => __('No recently used Categories'),
            'Change Category' => __('Change Category'),
            'Edit' => __('Edit'),
            'Category' => __('Category'),
            'Not Selected' => __('Not Selected'),
            'No results' => __('No results'),
            'No saved Categories' => __('No saved Categories'),
            'Category Settings' => __('Category Settings'),
            'Specifics' => __('Specifics'),
        ]);
    }

    public function getSearchUrl(): string
    {
        return $this->getUrl('*/category/search');
    }

    public function getAccountId(): int
    {
        return $this->uiListingRuntimeStorage->getListing()->getAccountId();
    }

    public function getShopId(): int
    {
        return $this->uiListingRuntimeStorage->getListing()->getShopId();
    }
}
