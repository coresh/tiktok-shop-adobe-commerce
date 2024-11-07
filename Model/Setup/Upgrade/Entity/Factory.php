<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Setup\Upgrade\Entity;

class Factory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function getFeatureObject(string $featureClassName): AbstractFeature
    {
        $object = $this->objectManager->create($featureClassName);

        if (!$object instanceof \M2E\TikTokShop\Model\Setup\Upgrade\Entity\AbstractFeature) {
            throw new \M2E\TikTokShop\Model\Exception\Logic(
                (string)__('%1 doesn\'t extends AbstractFeature', $featureClassName),
            );
        }

        return $object;
    }

    public function getConfigObject(string $upgradeClassName): ConfigInterface
    {
        $object = $this->objectManager->create($upgradeClassName);

        if (!$object instanceof \M2E\TikTokShop\Model\Setup\Upgrade\Entity\ConfigInterface) {
            throw new \M2E\TikTokShop\Model\Exception\Logic(
                (string)__(
                    sprintf(
                        '%%class_name doesn\'t extends %s',
                        \M2E\TikTokShop\Model\Setup\Upgrade\Entity\ConfigInterface::class,
                    ),
                    ['class_name' => $upgradeClassName],
                ),
            );
        }

        return $object;
    }
}
