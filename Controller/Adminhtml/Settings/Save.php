<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Controller\Adminhtml\Settings;

use M2E\TikTokShop\Controller\Adminhtml\TikTokShop\AbstractSettings;

class Save extends AbstractSettings
{
    private \M2E\TikTokShop\Helper\Component\TikTokShop\Configuration $configuration;

    public function __construct(
        \M2E\TikTokShop\Helper\Component\TikTokShop\Configuration $componentConfiguration
    ) {
        parent::__construct();

        $this->configuration = $componentConfiguration;
    }

    public function execute()
    {
        $post = $this->getRequest()->getPostValue();
        if (!$post) {
            $this->setJsonContent(['success' => false]);

            return $this->getResult();
        }

        $this->configuration->setConfigValues($this->getRequest()->getParams());
        $this->setJsonContent(['success' => true]);

        return $this->getResult();
    }
}
