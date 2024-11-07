<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Helper;

class Factory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param string $helperName
     *
     * @return object
     */
    public function getObject(string $helperName): object
    {
        // fix for Magento2 sniffs that forcing to use ::class
        $helperName = str_replace('_', '\\', $helperName);

        return $this->objectManager->get('\M2E\TikTokShop\Helper\\' . $helperName);
    }
}
