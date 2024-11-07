<?php

namespace M2E\TikTokShop\Block\Adminhtml\TikTokShop\Template\Synchronization\Edit\Form;

use M2E\TikTokShop\Block\Adminhtml\Magento\AbstractBlock;

class Data extends AbstractBlock
{
    protected $_template = 'tiktokshop/template/synchronization/form/data.phtml';
    private \M2E\TikTokShop\Helper\Data\GlobalData $globalDataHelper;

    public function __construct(
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Template $context,
        \M2E\TikTokShop\Helper\Data\GlobalData $globalDataHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->globalDataHelper = $globalDataHelper;
    }

    protected function _prepareLayout()
    {
        $this->globalDataHelper->setValue('synchronization_form_data', $this->getFormData());

        $this->globalDataHelper->setValue('is_custom', $this->getData('is_custom'));
        $this->globalDataHelper->setValue('custom_title', $this->getData('custom_title'));

        $this->setChild(
            'tabs',
            $this->getLayout()
                 ->createBlock(
                     \M2E\TikTokShop\Block\Adminhtml\TikTokShop\Template\Synchronization\Edit\Form\Tabs::class
                 )
        );

        $this->jsPhp->addConstants(
            \M2E\TikTokShop\Helper\Data::getClassConstants(\M2E\TikTokShop\Model\Template\Synchronization::class)
        );
        $this->jsPhp->addConstants(
            \M2E\TikTokShop\Helper\Data::getClassConstants(\M2E\TikTokShop\Model\Template\Synchronization::class)
        );

        $this->jsTranslator->addTranslations([
            'Wrong value. Only integer numbers.' => __('Wrong value. Only integer numbers.'),

            'Must be greater than "Min".' => __('Must be greater than "Min".'),
            'Inconsistent Settings in Relist and Stop Rules.' => __(
                'Inconsistent Settings in Relist and Stop Rules.'
            ),

            'You need to choose at set at least one time for the schedule to run.' => __(
                'You need to choose at least one Time for the schedule to run.'
            ),
            'You should specify time.' => __('You should specify time.'),

            'Wrong value.' => __('Wrong value.'),
            'Must be greater than "Active From" Date.' => __('Must be greater than "Active From" Date.'),
            'Must be greater than "From Time".' => __('Must be greater than "From Time".'),
        ]);

        $this->css->add(
            <<<CSS
.field-advanced_filter ul.rule-param-children {
    margin-top: 1em;
}
.field-advanced_filter .rule-param .label {
    font-size: 14px;
    font-weight: 600;
}
CSS
        );

        $this->js->add(
            <<<JS
    require([
        'TikTokShop/TikTokShop/Template/Synchronization'
    ], function(){
        window.TikTokShopTemplateSynchronizationObj = new TikTokShopTemplateSynchronization();
        TikTokShopTemplateSynchronizationObj.initObservers();
    });
JS
        );

        return parent::_prepareLayout();
    }
}
