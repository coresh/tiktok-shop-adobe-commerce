<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Controller\Adminhtml\Order;

class SaveNote extends \M2E\TikTokShop\Controller\Adminhtml\AbstractOrder
{
    private \M2E\TikTokShop\Model\Order\Note\Create $createService;
    private \M2E\TikTokShop\Model\Order\Note\Update $updateService;
    private \M2E\TikTokShop\Model\Order\Repository $orderRepository;

    public function __construct(
        \M2E\TikTokShop\Model\Order\Note\Create $createService,
        \M2E\TikTokShop\Model\Order\Note\Update $updateService,
        \M2E\TikTokShop\Model\Order\Repository $orderRepository
    ) {
        parent::__construct();
        $this->createService = $createService;
        $this->updateService = $updateService;
        $this->orderRepository = $orderRepository;
    }

    public function execute()
    {
        $noteText = $this->getRequest()->getParam('note');
        if ($noteText === null) {
            $this->setJsonContent(['result' => false]);

            return $this->getResult();
        }

        if ($noteId = $this->getRequest()->getParam('note_id')) {
            $this->updateService->process((int)$noteId, $noteText);
        } else {
            $orderId = (int)$this->getRequest()->getParam('order_id');
            $order = $this->orderRepository->get($orderId);

            $this->createService->process(
                $order,
                $noteText
            );
        }

        $this->setJsonContent(['result' => true]);

        return $this->getResult();
    }
}
