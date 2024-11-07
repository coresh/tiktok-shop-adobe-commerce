<?php

namespace M2E\TikTokShop\Controller\Adminhtml\TikTokShop\Template\Description;

use M2E\TikTokShop\Controller\Adminhtml\TikTokShop\Template\AbstractDescription;

class CheckMagentoProductId extends AbstractDescription
{
    public function execute()
    {
        $productId = $this->getRequest()->getParam('product_id', -1);

        $this->setJsonContent([
            'result' => $this->isMagentoProductExists($productId),
        ]);

        return $this->getResult();
    }
}
