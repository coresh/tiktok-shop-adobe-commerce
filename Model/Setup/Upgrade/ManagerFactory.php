<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Setup\Upgrade;

class ManagerFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;
    /** @var \M2E\TikTokShop\Model\Setup\Upgrade\Entity\Factory */
    private Entity\Factory $upgradeFactory;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \M2E\TikTokShop\Model\Setup\Upgrade\Entity\Factory $upgradeFactory
    ) {
        $this->objectManager = $objectManager;
        $this->upgradeFactory = $upgradeFactory;
    }

    public function create(
        string $upgradeClassName
    ): Manager {
        return $this->objectManager->create(
            Manager::class,
            [
                'configObject' => $this->upgradeFactory->getConfigObject($upgradeClassName),
            ],
        );
    }
}
