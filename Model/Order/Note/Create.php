<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Order\Note;

class Create
{
    use MagentoOrderUpdateTrait;

    private \M2E\TikTokShop\Model\Order\Note\Repository $repository;
    private \M2E\TikTokShop\Model\Order\NoteFactory $noteFactory;

    public function __construct(
        \M2E\TikTokShop\Model\Order\Note\Repository $repository,
        \M2E\TikTokShop\Model\Order\NoteFactory $noteFactory,
        \M2E\TikTokShop\Model\Magento\Order\Updater $magentoOrderUpdater
    ) {
        $this->repository = $repository;
        $this->noteFactory = $noteFactory;
        $this->magentoOrderUpdater = $magentoOrderUpdater;
    }

    public function process(\M2E\TikTokShop\Model\Order $order, string $note): \M2E\TikTokShop\Model\Order\Note
    {
        $obj = $this->noteFactory->create();
        $obj->init($order->getId(), $note);

        $this->repository->create($obj);

        $comment = (string)__(
            'Custom Note was added to the corresponding TikTok Shop order: %note.',
            ['note' => $obj->getNote()],
        );
        $this->updateMagentoOrderComment($order, $comment);

        return $obj;
    }
}
