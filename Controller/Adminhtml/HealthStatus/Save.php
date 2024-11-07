<?php

namespace M2E\TikTokShop\Controller\Adminhtml\HealthStatus;

use M2E\TikTokShop\Controller\Adminhtml\AbstractHealthStatus;
use M2E\TikTokShop\Block\Adminhtml\HealthStatus\Tabs;

class Save extends AbstractHealthStatus
{
    /** @var \M2E\TikTokShop\Model\Config\Manager */
    private $config;

    public function __construct(
        \M2E\TikTokShop\Model\Config\Manager $config,
        \M2E\TikTokShop\Controller\Adminhtml\Context $context
    ) {
        parent::__construct($context);

        $this->config = $config;
    }

    public function execute()
    {
        $referrer = $this->getRequest()->getParam('referrer', false);
        $postData = $this->getRequest()->getPost()->toArray();

        if (isset($postData['notification_mode'])) {
            $this->config->setGroupValue(
                '/health_status/notification/',
                'mode',
                (int)$postData['notification_mode']
            );
        }

        if (isset($postData['notification_email'])) {
            $this->config->setGroupValue(
                '/health_status/notification/',
                'email',
                $postData['notification_email']
            );
        }

        if (isset($postData['notification_level'])) {
            $this->config->setGroupValue(
                '/health_status/notification/',
                'level',
                (int)$postData['notification_level']
            );
        }

        $this->getMessageManager()->addSuccessMessage(__('Settings are saved.'));

        $params = [];
        $params['tab'] = Tabs::TAB_ID_NOTIFICATIONS;
        $referrer && $params['referrer'] = $referrer;

        $this->_redirect('*/*/index', $params);
    }
}
