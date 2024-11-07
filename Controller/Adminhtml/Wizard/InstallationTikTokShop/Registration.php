<?php

namespace M2E\TikTokShop\Controller\Adminhtml\Wizard\InstallationTikTokShop;

use M2E\TikTokShop\Controller\Adminhtml\Context;

class Registration extends Installation
{
    /** @var \M2E\TikTokShop\Model\Registration\UserInfo\Repository */
    private $manager;

    public function __construct(
        \M2E\TikTokShop\Model\Registration\UserInfo\Repository $manager,
        \M2E\TikTokShop\Helper\Magento $magentoHelper,
        \M2E\TikTokShop\Helper\Module\Wizard $wizardHelper,
        \Magento\Framework\Code\NameBuilder $nameBuilder,
        \M2E\TikTokShop\Helper\Module\License $licenseHelper
    ) {
        parent::__construct(
            $magentoHelper,
            $wizardHelper,
            $nameBuilder,
            $licenseHelper,
        );
        $this->manager = $manager;
    }

    public function execute()
    {
        $this->init();

        return $this->registrationAction($this->manager);
    }
}
