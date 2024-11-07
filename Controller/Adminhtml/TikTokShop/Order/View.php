<?php

namespace M2E\TikTokShop\Controller\Adminhtml\TikTokShop\Order;

class View extends \M2E\TikTokShop\Controller\Adminhtml\TikTokShop\AbstractOrder
{
    /** @var \M2E\TikTokShop\Helper\Data\GlobalData */
    private $globalData;
    private \M2E\TikTokShop\Model\Order\Repository $repository;

    public function __construct(
        \M2E\TikTokShop\Helper\Data\GlobalData $globalData,
        \M2E\TikTokShop\Model\Order\Repository $repository
    ) {
        parent::__construct();

        $this->globalData = $globalData;
        $this->repository = $repository;
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $order = $this->repository->get((int)$id);

        $this->globalData->setValue('order', $order);

        $this->addContent(
            $this->getLayout()->createBlock(
                \M2E\TikTokShop\Block\Adminhtml\TikTokShop\Order\View::class
            )
        );

        $this->init();
        $this->getResultPage()->getConfig()->getTitle()->prepend(__('View Order Details'));

        return $this->getResult();
    }
}
