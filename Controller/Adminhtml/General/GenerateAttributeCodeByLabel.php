<?php

namespace M2E\TikTokShop\Controller\Adminhtml\General;

use M2E\TikTokShop\Controller\Adminhtml\AbstractGeneral;

class GenerateAttributeCodeByLabel extends AbstractGeneral
{
    public function execute()
    {
        $label = $this->getRequest()->getParam('store_label');
        $this->setAjaxContent(\M2E\Core\Model\Magento\Attribute\Builder::generateCodeByLabel($label), false);

        return $this->getResult();
    }
}
