<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Processing;

class ResultHandlerFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;
    private ResultHandlerCollection $resultHandlerCollection;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        ResultHandlerCollection $resultHandlerCollection
    ) {
        $this->objectManager = $objectManager;
        $this->resultHandlerCollection = $resultHandlerCollection;
    }

    public function create(string $nick): \M2E\TikTokShop\Model\Processing\SingleResultHandlerInterface
    {
        if (!$this->resultHandlerCollection->has($nick)) {
            throw new \M2E\TikTokShop\Model\Exception\Logic("Processing handler '$nick' not found.");
        }

        $class = $this->resultHandlerCollection->get($nick);

        return $this->objectManager->create($class);
    }
}
