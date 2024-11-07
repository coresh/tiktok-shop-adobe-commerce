<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\ControlPanel\Inspection;

class DefinitionFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(
        string $nick,
        string $title,
        string $description,
        string $group,
        string $executionSpeedGroup,
        string $handler
    ): Definition {
        return $this->objectManager->create(
            Definition::class,
            [
                'nick' => $nick,
                'title' => $title,
                'description' => $description,
                'group' => $group,
                'executionSpeedGroup' => $executionSpeedGroup,
                'handler' => $handler,
            ],
        );
    }
}
