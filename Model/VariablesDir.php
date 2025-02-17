<?php

namespace M2E\TikTokShop\Model;

class VariablesDir
{
    private \M2E\Core\Model\VariablesDir\Adapter $adapter;

    public function __construct(
        \M2E\Core\Model\VariablesDir\AdapterFactory $adapterFactory
    ) {
        $this->adapter = $adapterFactory->create(\M2E\TikTokShop\Helper\Module::IDENTIFIER);
    }

    public function getBasePath(): string
    {
        return $this->adapter->getBasePath();
    }

    public function getPath(): string
    {
        return $this->adapter->getPath();
    }

    public function isBaseExist(): bool
    {
        return $this->adapter->isBaseExist();
    }

    public function isExist(): bool
    {
        return $this->adapter->isExist();
    }

    public function createBase(): void
    {
        $this->adapter->createBase();
    }

    public function create()
    {
        $this->adapter->create();
    }

    public function removeBase()
    {
        $this->adapter->removeBase();
    }

    public function removeBaseForce()
    {
        $this->adapter->removeBaseForce();
    }

    public function remove(): void
    {
        $this->adapter->remove();
    }

    public function getAdapter(): \M2E\Core\Model\VariablesDir\Adapter
    {
        return $this->adapter;
    }
}
