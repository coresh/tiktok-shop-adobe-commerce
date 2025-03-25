<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Controller\Adminhtml\ManufacturerConfiguration;

class Delete extends AbstractManufacturerConfiguration
{
    private \M2E\TikTokShop\Model\ManufacturerConfiguration\Repository $repository;

    public function __construct(
        \M2E\TikTokShop\Model\ManufacturerConfiguration\Repository $repository,
        $context = null
    ) {
        parent::__construct($context);
        $this->repository = $repository;
    }

    public function execute()
    {
        $id = (int)$this->getRequest()->getParam('id');
        $config = $this->repository->get($id);

        $this->repository->delete($config);

        return $this->getResult();
    }
}
