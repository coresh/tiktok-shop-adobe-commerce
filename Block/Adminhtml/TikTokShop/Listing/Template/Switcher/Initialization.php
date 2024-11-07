<?php

namespace M2E\TikTokShop\Block\Adminhtml\TikTokShop\Listing\Template\Switcher;

class Initialization extends \M2E\TikTokShop\Block\Adminhtml\Magento\AbstractBlock
{
    /** @var \M2E\TikTokShop\Helper\Data */
    private $dataHelper;
    /** @var \M2E\TikTokShop\Helper\Data\GlobalData */
    private $globalDataHelper;

    public function __construct(
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Template $context,
        \M2E\TikTokShop\Helper\Data $dataHelper,
        \M2E\TikTokShop\Helper\Data\GlobalData $globalDataHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->dataHelper = $dataHelper;
        $this->globalDataHelper = $globalDataHelper;
    }

    public function _construct()
    {
        parent::_construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('TtsListingTemplateSwitcherInitialization');
        // ---------------------------------------
    }

    protected function _toHtml()
    {
        // ---------------------------------------
        $urls = [];

        // initiate account param
        // ---------------------------------------
        $account = $this->globalDataHelper->getValue('tiktokshop_account');
        $params['account_id'] = $account->getId();
        // ---------------------------------------

        // initiate attribute sets param
        // ---------------------------------------
        if (
            $this->getMode(
            ) == \M2E\TikTokShop\Block\Adminhtml\TikTokShop\Listing\Template\Switcher::MODE_LISTING_PRODUCT
        ) {
            $attributeSets = $this->globalDataHelper->getValue('tiktokshop_attribute_sets');
            $params['attribute_sets'] = implode(',', $attributeSets);
        }
        // ---------------------------------------

        // initiate display use default option param
        // ---------------------------------------
        $displayUseDefaultOption = $this->globalDataHelper->getValue('tiktokshop_display_use_default_option');
        $params['display_use_default_option'] = (int)(bool)$displayUseDefaultOption;
        // ---------------------------------------

        $path = 'tiktokshop_template/getTemplateHtml';
        $urls[$path] = $this->getUrl('*/' . $path, $params);
        //------------------------------

        //------------------------------
        $path = 'tiktokshop_template/isTitleUnique';
        $urls[$path] = $this->getUrl('*/' . $path);

        $path = 'tiktokshop_template/newTemplateHtml';
        $urls[$path] = $this->getUrl('*/' . $path);

        $path = 'tiktokshop_template/edit';
        $urls[$path] = $this->getUrl(
            '*/tiktokshop_template/edit',
            ['wizard' => (bool)$this->getRequest()->getParam('wizard', false)]
        );
        //------------------------------

        $this->jsUrl->addUrls($urls);
        $this->jsUrl->add(
            $this->getUrl('*/template/checkMessages'),
            'templateCheckMessages'
        );

        $this->jsPhp->addConstants(
            \M2E\TikTokShop\Helper\Data::getClassConstants(\M2E\TikTokShop\Model\TikTokShop\Template\Manager::class)
        );

        $this->jsTranslator->addTranslations([
            'Customized' => __('Customized'),
            'Policies' => __('Policies'),
            'Policy with the same Title already exists.' => __('Policy with the same Title already exists.'),
            'Please specify Policy Title' => __('Please specify Policy Title'),
            'Save New Policy' => __('Save New Policy'),
            'Save as New Policy' => __('Save as New Policy'),
        ]);

        $store = $this->globalDataHelper->getValue('tiktokshop_store');

        $this->js->add(
            <<<JS
    define('Switcher/Initialization',[
        'TikTokShop/TikTokShop/Listing/Template/Switcher',
        'TikTokShop/TemplateManager'
    ], function(){
        window.TemplateManagerObj = new TemplateManager();

        window.TikTokShopListingTemplateSwitcherObj = new TikTokShopListingTemplateSwitcher();
        TikTokShopListingTemplateSwitcherObj.storeId = {$store->getId()};
        TikTokShopListingTemplateSwitcherObj.listingProductIds = '{$this->getRequest()->getParam('ids')}';

    });
JS
        );

        return parent::_toHtml();
    }
}
