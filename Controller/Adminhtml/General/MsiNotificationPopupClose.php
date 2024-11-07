<?php

namespace M2E\TikTokShop\Controller\Adminhtml\General;

class MsiNotificationPopupClose extends \M2E\TikTokShop\Controller\Adminhtml\AbstractBase
{
    private \M2E\TikTokShop\Model\Registry\Manager $registry;

    public function __construct(
        \M2E\TikTokShop\Model\Registry\Manager $registry,
        \M2E\TikTokShop\Controller\Adminhtml\Context $context
    ) {
        parent::__construct($context);

        $this->registry = $registry;
    }

    public function execute()
    {
        $this->registry->setValue('/view/msi/popup/shown/', 1);
        $this->setJsonContent(['status' => true]);

        return $this->getResult();
    }
}
