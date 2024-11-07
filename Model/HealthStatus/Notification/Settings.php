<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\HealthStatus\Notification;

class Settings
{
    public const MODE_DISABLED = 0;
    public const MODE_EXTENSION_PAGES = 1;
    public const MODE_MAGENTO_PAGES = 2;
    public const MODE_MAGENTO_SYSTEM_NOTIFICATION = 3;
    public const MODE_EMAIL = 4;

    private \M2E\TikTokShop\Model\Config\Manager $config;

    public function __construct(\M2E\TikTokShop\Model\Config\Manager $config)
    {
        $this->config = $config;
    }

    public function getMode()
    {
        return (int)$this->config->getGroupValue('/health_status/notification/', 'mode');
    }

    public function isModeDisabled()
    {
        return $this->getMode() == self::MODE_DISABLED;
    }

    public function isModeExtensionPages()
    {
        return $this->getMode() == self::MODE_EXTENSION_PAGES;
    }

    public function isModeMagentoPages()
    {
        return $this->getMode() == self::MODE_MAGENTO_PAGES;
    }

    public function isModeMagentoSystemNotification()
    {
        return $this->getMode() == self::MODE_MAGENTO_SYSTEM_NOTIFICATION;
    }

    public function isModeEmail()
    {
        return $this->getMode() == self::MODE_EMAIL;
    }

    //----------------------------------------

    public function getEmail()
    {
        return $this->config->getGroupValue('/health_status/notification/', 'email');
    }

    //----------------------------------------

    public function getLevel()
    {
        return $this->config->getGroupValue('/health_status/notification/', 'level');
    }
}
