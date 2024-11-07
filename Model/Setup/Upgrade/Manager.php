<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Setup\Upgrade;

use M2E\TikTokShop\Model\Setup\Upgrade\Entity\ConfigInterface;
use M2E\TikTokShop\Model\Setup\Upgrade\Entity\Factory;

class Manager
{
    /** @var \M2E\TikTokShop\Model\Setup\Upgrade\Entity\ConfigInterface */
    private ConfigInterface $configObject;
    /** @var \M2E\TikTokShop\Model\Setup\Upgrade\Entity\Factory */
    private Factory $upgradeFactory;

    public function __construct(
        \M2E\TikTokShop\Model\Setup\Upgrade\Entity\ConfigInterface $configObject,
        Factory $upgradeFactory
    ) {
        $this->upgradeFactory = $upgradeFactory;
        $this->configObject = $configObject;
    }

    public function process(): void
    {
        foreach ($this->configObject->getFeaturesList() as $featureName) {
            $featureObject = $this->upgradeFactory->getFeatureObject($featureName);

            $featureObject->execute();
        }
    }
}
