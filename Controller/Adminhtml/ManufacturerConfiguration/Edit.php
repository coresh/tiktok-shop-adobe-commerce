<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Controller\Adminhtml\ManufacturerConfiguration;

class Edit extends AbstractManufacturerConfiguration
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

        $block = $this
            ->getLayout()
            ->createBlock(
                \M2E\TikTokShop\Block\Adminhtml\ManufacturerConfiguration\CreateOrEdit::class,
                '',
                ['manufacturerConfiguration' => $this->repository->get($id)]
            );

        $this->addContent($block);

        $this->getResult()->getConfig()->getTitle()->prepend(__('Edit'));

        return $this->getResult();
    }
}
