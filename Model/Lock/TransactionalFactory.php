<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Lock;

class TransactionalFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function createEmpty(): Transactional
    {
        return $this->objectManager->create(Transactional::class);
    }

    public function create(string $nick): Transactional
    {
        $object = $this->createEmpty();

        $object->create($nick);

        return $object;
    }
}
