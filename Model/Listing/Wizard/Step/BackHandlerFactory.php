<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Listing\Wizard\Step;

class BackHandlerFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(\M2E\TikTokShop\Model\Listing\Wizard\StepDeclaration $step): BackHandlerInterface
    {
        $handler = $this->objectManager->create($step->getBackHandlerClass());
        if (!$handler instanceof BackHandlerInterface) {
            throw new \LogicException('Back handler is not valid.');
        }

        return $handler;
    }
}
