<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Setup;

interface InstallHandlerInterface
{
    public function installSchema(\Magento\Framework\Setup\SetupInterface $setup): void;

    public function installData(\Magento\Framework\Setup\SetupInterface $setup): void;
}
