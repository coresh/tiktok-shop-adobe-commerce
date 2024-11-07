<?php

namespace M2E\TikTokShop\Controller\Adminhtml\Order;

class GetDebugInformation extends \M2E\TikTokShop\Controller\Adminhtml\AbstractOrder
{
    /** @var \M2E\TikTokShop\Helper\Data\GlobalData */
    private $globalData;
    private \M2E\TikTokShop\Model\Order\Repository $orderRepository;

    public function __construct(
        \M2E\TikTokShop\Model\Order\Repository $orderRepository,
        \M2E\TikTokShop\Helper\Data\GlobalData $globalData,
        \M2E\TikTokShop\Controller\Adminhtml\Context $context
    ) {
        parent::__construct($context);

        $this->globalData = $globalData;
        $this->orderRepository = $orderRepository;
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('id');

        if ($id === null) {
            $this->setAjaxContent('', false);

            return $this->getResult();
        }

        try {
            $order = $this->orderRepository->get((int)$id);
        } catch (\Exception $e) {
            $this->setAjaxContent('', false);

            return $this->getResult();
        }

        $this->globalData->setValue('order', $order);

        $debugBlock = $this->getLayout()->createBlock(\M2E\TikTokShop\Block\Adminhtml\Order\Debug::class);

        $this->setAjaxContent($debugBlock->toHtml());

        return $this->getResult();
    }
}
