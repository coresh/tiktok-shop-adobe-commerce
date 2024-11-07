<?php

namespace M2E\TikTokShop\Block\Adminhtml\Log\Order;

abstract class AbstractContainer extends \M2E\TikTokShop\Block\Adminhtml\Magento\Grid\AbstractContainer
{
    private \M2E\TikTokShop\Model\OrderFactory $orderFactory;
    private \M2E\TikTokShop\Model\ResourceModel\Order $orderResource;

    public function __construct(
        \M2E\TikTokShop\Model\OrderFactory $orderFactory,
        \M2E\TikTokShop\Model\ResourceModel\Order $orderResource,
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Widget $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->orderFactory = $orderFactory;
        $this->orderResource = $orderResource;
    }

    public function _construct()
    {
        parent::_construct();

        $this->_controller = 'adminhtml_tikTokShop_log_order';

        $this->setId('tikTokShopOrderLog');

        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');
    }

    //########################################

    protected function _toHtml()
    {
        $filtersHtml = $this->getFiltersHtml();

        if (empty($filtersHtml)) {
            return parent::_toHtml();
        }

        $filtersHtml = <<<HTML
<div class="page-main-actions">
    <div class="filter_block">
        {$filtersHtml}
    </div>
</div>
HTML;

        return $filtersHtml . parent::_toHtml();
    }

    protected function getFiltersHtml()
    {
        $accountSwitcherBlock = $this->createAccountSwitcherBlock();
        $switcherBlock = $this->createShopSwitcherBlock();
        $uniqueMessageFilterBlock = $this->createUniqueMessageFilterBlock();

        $orderId = $this->getRequest()->getParam('id', false);
        $order = $this->orderFactory->create();
        $this->orderResource->load($order, $orderId);

        if ($orderId && $order->isObjectNew() === false) {
            $accountTitle = $this->filterManager->truncate(
                $order->getAccount()->getTitle(),
                ['length' => 15]
            );

            return
                $this->getStaticFilterHtml(
                    $accountSwitcherBlock->getLabel(),
                    $accountTitle
                )
                . $this->getStaticFilterHtml(
                    $switcherBlock->getLabel(),
                    $order->getShop()->getShopName()
                );
        }

        if ($switcherBlock->isEmpty() && $accountSwitcherBlock->isEmpty()) {
            return $uniqueMessageFilterBlock->toHtml();
        }

        return $accountSwitcherBlock->toHtml()
            . $switcherBlock->toHtml()
            . $uniqueMessageFilterBlock->toHtml();
    }

    protected function getStaticFilterHtml(string $label, string $value): string
    {
        return <<<HTML
<p class="static-switcher">
    <span>$label:</span>
    <span>$value</span>
</p>
HTML;
    }

    protected function createAccountSwitcherBlock()
    {
        return $this->getLayout()->createBlock(\M2E\TikTokShop\Block\Adminhtml\Account\Switcher::class);
    }

    protected function createShopSwitcherBlock()
    {
        return $this->getLayout()->createBlock(\M2E\TikTokShop\Block\Adminhtml\Shop\Switcher::class);
    }

    protected function createUniqueMessageFilterBlock()
    {
        return $this->getLayout()->createBlock(\M2E\TikTokShop\Block\Adminhtml\Log\UniqueMessageFilter::class)->setData(
            [
                'route' => "*/tikTokShop_log_order/",
                'title' => __('Only messages with a unique Order ID'),
            ]
        );
    }

    //########################################
}
