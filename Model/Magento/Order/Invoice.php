<?php

namespace M2E\TikTokShop\Model\Magento\Order;

class Invoice
{
    /** @var \Magento\Framework\DB\TransactionFactory */
    private $transactionFactory = null;

    /** @var \Magento\Sales\Model\Order $magentoOrder */
    private $magentoOrder = null;

    /** @var \Magento\Sales\Model\Order\Invoice $invoice */
    private $invoice = null;

    private \M2E\TikTokShop\Helper\Data\GlobalData $globalDataHelper;

    public function __construct(
        \Magento\Framework\DB\TransactionFactory $transactionFactory,
        \M2E\TikTokShop\Helper\Data\GlobalData $globalDataHelper
    ) {
        $this->transactionFactory = $transactionFactory;
        $this->globalDataHelper = $globalDataHelper;
    }

    /**
     * @param \Magento\Sales\Model\Order $magentoOrder
     *
     * @return $this
     */
    public function setMagentoOrder(\Magento\Sales\Model\Order $magentoOrder)
    {
        $this->magentoOrder = $magentoOrder;

        return $this;
    }

    public function getInvoice()
    {
        return $this->invoice;
    }

    public function buildInvoice()
    {
        $this->prepareInvoice();
    }

    private function prepareInvoice()
    {
        // Skip invoice observer
        // ---------------------------------------
        $this->globalDataHelper->unsetValue('skip_invoice_observer');
        $this->globalDataHelper->setValue('skip_invoice_observer', true);
        // ---------------------------------------

        $qtys = [];
        foreach ($this->magentoOrder->getAllItems() as $item) {
            $qtyToInvoice = $item->getQtyToInvoice();

            if ($qtyToInvoice == 0) {
                continue;
            }

            $qtys[$item->getId()] = $qtyToInvoice;
        }

        // Create invoice
        // ---------------------------------------
        $this->invoice = $this->magentoOrder->prepareInvoice($qtys);
        $this->invoice->register();
        // it is necessary for updating qty_invoiced field in sales_flat_order_item table
        $this->invoice->getOrder()->setIsInProcess(true);

        $this->transactionFactory
            ->create()
            ->addObject($this->invoice)
            ->addObject($this->invoice->getOrder())
            ->save();
        // ---------------------------------------
    }
}
