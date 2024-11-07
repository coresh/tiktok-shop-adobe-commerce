<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Order;

class CreditMemo
{
    private \Magento\Sales\Model\Order\CreditmemoFactory $creditmemoFactory;
    private \Magento\Sales\Model\Service\CreditmemoService $creditmemoService;
    private \M2E\TikTokShop\Helper\Module\Exception $helperModuleException;

    public function __construct(
        \Magento\Sales\Model\Order\CreditmemoFactory $creditmemoFactory,
        \Magento\Sales\Model\Service\CreditmemoService $creditmemoService,
        \M2E\TikTokShop\Helper\Module\Exception $helperModuleException
    ) {
        $this->creditmemoFactory = $creditmemoFactory;
        $this->creditmemoService = $creditmemoService;
        $this->helperModuleException = $helperModuleException;
    }

    public function process(\M2E\TikTokShop\Model\Order $order): ?\Magento\Sales\Model\Order\Creditmemo
    {
        if (!$this->canCreateCreditMemo($order)) {
            return null;
        }

        try {
            $creditMemo = $this->creditmemoFactory->createByOrder($order->getMagentoOrder());
            foreach ($creditMemo->getAllItems() as $creditMemoItem) {
                $creditMemoItem->setBackToStock(true);
            }
            $this->creditmemoService->refund($creditMemo);
        } catch (\Throwable $exception) {
            $this->helperModuleException->process($exception);
            $order->addErrorLog(
                'CreditMemo was not created. Reason: %msg%',
                ['msg' => $exception->getMessage()]
            );

            return null;
        }

        $order->addSuccessLog('Credit Memo #%creditMemo_id% was created.', [
            '!creditMemo_id' => $creditMemo->getIncrementId(),
        ]);

        return $creditMemo;
    }

    private function canCreateCreditMemo(\M2E\TikTokShop\Model\Order $order): bool
    {
        $magentoOrder = $order->getMagentoOrder();
        if ($magentoOrder === null) {
            return false;
        }

        if (
            $magentoOrder->hasCreditmemos()
            || !$magentoOrder->canCreditmemo()
        ) {
            return false;
        }

        return true;
    }
}
