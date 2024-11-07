<?php

namespace M2E\TikTokShop\Controller\Adminhtml\TikTokShop;

abstract class AbstractTemplate extends AbstractMain
{
    protected \M2E\TikTokShop\Model\TikTokShop\Template\Manager $templateManager;

    public function __construct(
        \M2E\TikTokShop\Model\TikTokShop\Template\Manager $templateManager
    ) {
        parent::__construct();
        $this->templateManager = $templateManager;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('M2E_TikTokShop::configuration_templates');
    }
}
