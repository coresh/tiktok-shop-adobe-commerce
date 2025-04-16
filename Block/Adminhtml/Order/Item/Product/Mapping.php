<?php

namespace M2E\TikTokShop\Block\Adminhtml\Order\Item\Product;

use M2E\TikTokShop\Block\Adminhtml\Magento\AbstractContainer;

class Mapping extends AbstractContainer
{
    protected $_template = 'order/item/product/mapping.phtml';

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _beforeToHtml()
    {
        $mappingGrid = $this
            ->getLayout()
            ->createBlock(\M2E\TikTokShop\Block\Adminhtml\Order\Item\Product\Mapping\Grid::class);

        $this->setChild('product_mapping_grid', $mappingGrid);

        $helpBlockHtml = __(
            'As %extension_title was not able to find appropriate Product ' .
            'in Magento Catalog, you are supposed to find and map it manually.<br/><br/>' .
            '<b>Note:</b> Magento Order can be only created when all Products of Order ' .
            'are found in Magento Catalog.',
            [
                'extension_title' => \M2E\TikTokShop\Helper\Module::getExtensionTitle()
            ]
        );

        $helpBlock = $this
            ->getLayout()
            ->createBlock(\M2E\TikTokShop\Block\Adminhtml\HelpBlock::class)
            ->setData(['content' => $helpBlockHtml]);

        $this->setChild('product_mapping_help_block', $helpBlock);

        return parent::_beforeToHtml();
    }
}
