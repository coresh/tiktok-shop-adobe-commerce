<?php

namespace M2E\TikTokShop\Block\Adminhtml\TikTokShop\Listing\Product\Template;

class Edit extends \M2E\TikTokShop\Block\Adminhtml\Magento\Form\AbstractContainer
{
    /**
     * @return void
     */
    public function _construct()
    {
        parent::_construct();

        $this->setId('ttsListingProductTemplatePolicy');
        $this->_controller = 'adminhtml_tikTokShop_listing_product_template';
        $this->_mode = 'Edit';

        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');
    }

    /**
     * @return string
     * @throws \M2E\TikTokShop\Model\Exception\Logic
     */
    protected function _toHtml()
    {
        $helpBlock = $this->getLayout()->createBlock(\M2E\TikTokShop\Block\Adminhtml\HelpBlock::class);
        $helpBlock->addData(
            [
                'content' => __(
                    '<p>You may edit Policies assigned to your Listing or create new ones. ' .
                    'The changes you make are automatically applied to all M2E TikTok Shop Connect Listings that ' .
                    'use this Policy.</p><p>Find more details on configuring Policies in ' .
                    'the <a href="%url" target="_blank">documentation</a>.</p>',
                    ['url' => 'https://docs-m2.m2epro.com/m2e-tiktok-shop-policies'],
                ),
                'style' => 'margin-top: 30px',
            ]
        );

        return $helpBlock->toHtml() . '<div id="content_container">' . parent::_toHtml() . '</div>';
    }
}
