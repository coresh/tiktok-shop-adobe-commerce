<?php

namespace M2E\TikTokShop\Block\Adminhtml\Listing\Moving;

/**
 * Class \M2E\TikTokShop\Block\Adminhtml\Listing\Moving\FailedProducts
 */
class FailedProducts extends \M2E\TikTokShop\Block\Adminhtml\Magento\AbstractContainer
{
    protected $_template = 'listing/moving/failedProducts.phtml';

    //########################################

    protected function _beforeToHtml()
    {
        // ---------------------------------------

        $this->setChild(
            'failedProducts_grid',
            $this->getLayout()->createBlock(
                \M2E\TikTokShop\Block\Adminhtml\Listing\Moving\FailedProducts\Grid::class,
                '',
                ['data' => ['grid_url' => $this->getData('grid_url')]]
            )
        );
        // ---------------------------------------

        parent::_beforeToHtml();
    }

    //########################################
}
