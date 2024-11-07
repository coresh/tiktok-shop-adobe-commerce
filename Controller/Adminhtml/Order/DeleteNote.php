<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Controller\Adminhtml\Order;

use M2E\TikTokShop\Controller\Adminhtml\AbstractOrder;

class DeleteNote extends AbstractOrder
{
    private \M2E\TikTokShop\Model\Order\Note\Delete $deleteService;
    private \M2E\TikTokShop\Model\Order\Note\Repository $repository;

    public function __construct(
        \M2E\TikTokShop\Model\Order\Note\Repository $repository,
        \M2E\TikTokShop\Model\Order\Note\Delete $deleteService
    ) {
        parent::__construct();
        $this->deleteService = $deleteService;
        $this->repository = $repository;
    }

    public function execute()
    {
        $noteId = $this->getRequest()->getParam('note_id');
        if ($noteId === null) {
            $this->setJsonContent(['result' => false]);

            return $this->getResult();
        }

        $note = $this->repository->get((int)$noteId);
        $this->deleteService->process($note);

        $this->setJsonContent(['result' => true]);

        return $this->getResult();
    }
}
