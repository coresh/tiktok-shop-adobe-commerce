<?php

namespace M2E\TikTokShop\Block\Adminhtml\Grid\Column\Renderer\ViewLogIcon;

use M2E\TikTokShop\Block\Adminhtml\Traits;

class Order extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Text
{
    use Traits\BlockTrait;

    private \M2E\TikTokShop\Model\ResourceModel\Order\Log\CollectionFactory $orderLogCollectionFactory;

    public function __construct(
        \M2E\TikTokShop\Model\ResourceModel\Order\Log\CollectionFactory $orderLogCollectionFactory,
        \Magento\Backend\Block\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->orderLogCollectionFactory = $orderLogCollectionFactory;
    }

    public function render(\Magento\Framework\DataObject $row)
    {
        $orderId = (int)$row->getId();

        // Prepare collection
        // ---------------------------------------
        $orderLogsCollection = $this->orderLogCollectionFactory->create();
        $orderLogsCollection->addFieldToFilter('order_id', $orderId);
        $orderLogsCollection->setOrder('id', 'DESC');
        $orderLogsCollection->getSelect()
                            ->limit(\M2E\TikTokShop\Block\Adminhtml\Log\Grid\LastActions::ACTIONS_COUNT);

        if ($orderLogsCollection->getSize() === 0) {
            return '';
        }

        // ---------------------------------------

        $summary = $this->getLayout()->createBlock(\M2E\TikTokShop\Block\Adminhtml\Order\Log\Grid\LastActions::class)
                        ->setData([
                            'entity_id' => $orderId,
                            'logs' => $orderLogsCollection->getItems(),
                            'view_help_handler' => 'OrderObj.viewOrderHelp',
                            'hide_help_handler' => 'OrderObj.hideOrderHelp',
                        ]);

        return $summary->toHtml();
    }
}
