<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Block\Adminhtml\Listing\Product;

use M2E\TikTokShop\Block\Adminhtml\Magento\AbstractContainer;

class ShowOthersListingsProductsFilter extends AbstractContainer
{
    protected $_template = 'listing/product/show_products_others_listings_filter.phtml';

    public function getParamName()
    {
        return 'show_products_others_listings';
    }

    public function getFilterUrl()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $params = [];
        } else {
            $params = $this->getRequest()->getParams();
        }

        if ($this->isChecked()) {
            unset($params[$this->getParamName()]);
        } else {
            $params[$this->getParamName()] = true;
        }

        return $this->getUrl('*/listing_wizard_product/*', $params);
    }

    public function isChecked()
    {
        return $this->getRequest()->getParam($this->getParamName());
    }
}
