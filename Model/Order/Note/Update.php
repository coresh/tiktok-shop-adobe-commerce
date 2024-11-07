<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Order\Note;

class Update
{
    use MagentoOrderUpdateTrait;

    private \M2E\TikTokShop\Model\Order\Note\Repository $repository;
    private \M2E\TikTokShop\Model\Order\Repository $orderRepository;

    public function __construct(
        \M2E\TikTokShop\Model\Order\Note\Repository $repository,
        \M2E\TikTokShop\Model\Order\Repository $orderRepository,
        \M2E\TikTokShop\Model\Magento\Order\Updater $magentoOrderUpdater
    ) {
        $this->repository = $repository;
        $this->magentoOrderUpdater = $magentoOrderUpdater;
        $this->orderRepository = $orderRepository;
    }

    public function process(int $noteId, string $note): \M2E\TikTokShop\Model\Order\Note
    {
        $obj = $this->repository->get($noteId);
        $obj->setNote($note);

        $this->repository->save($obj);

        $comment = (string)__(
            'Custom Note for the corresponding TikTok Shop order was updated: %note.',
            ['note' => $obj->getNote()],
        );

        $order = $this->orderRepository->get($obj->getOrderId());

        $this->updateMagentoOrderComment($order, $comment);

        return $obj;
    }
}
