<?php

namespace M2E\TikTokShop\Model\Cron\Task\Order;

class Creator extends \Magento\Framework\DataObject
{
    private bool $isValidateAccountCreateDate = true;

    private \M2E\TikTokShop\Model\Synchronization\LogService $syncLogService;
    private \M2E\TikTokShop\Model\TikTokShop\Order\BuilderFactory $orderBuilderFactory;
    private \M2E\TikTokShop\Helper\Module\Exception $exceptionHelper;
    private \M2E\TikTokShop\Model\Order\Repository $orderRepository;

    public function __construct(
        \M2E\TikTokShop\Model\Synchronization\LogService $syncLogService,
        \M2E\TikTokShop\Model\TikTokShop\Order\BuilderFactory $orderBuilderFactory,
        \M2E\TikTokShop\Model\Order\Repository $orderRepository,
        \M2E\TikTokShop\Helper\Module\Exception $exceptionHelper,
        array $data = []
    ) {
        parent::__construct($data);
        $this->orderBuilderFactory = $orderBuilderFactory;
        $this->exceptionHelper = $exceptionHelper;
        $this->orderRepository = $orderRepository;
        $this->syncLogService = $syncLogService;
    }

    public function setValidateAccountCreateDate(bool $mode): void
    {
        $this->isValidateAccountCreateDate = $mode;
    }

    /**
     * @return \M2E\TikTokShop\Model\Order[]
     * @throws \M2E\TikTokShop\Model\Exception\Logic
     * @throws \Exception
     */
    public function processTikTokOrders(
        \M2E\TikTokShop\Model\Shop $shop,
        array $ordersData
    ): array {
        $account = $shop->getAccount();

        $accountCreateDate = clone $account->getCreateData();
        $boundaryCreationDate = \M2E\TikTokShop\Helper\Date::createCurrentGmt()->modify('-90 days');

        $orders = [];
        foreach ($ordersData as $tikTokOrderData) {
            try {
                $orderCreateDate = \M2E\TikTokShop\Helper\Date::createDateGmt($tikTokOrderData['create_date']);

                if (!$this->isNeedCreateOrderByCreateDate($orderCreateDate, $accountCreateDate, $boundaryCreationDate)) {
                    continue;
                }

                $orderBuilder = $this->orderBuilderFactory->create();
                $orderBuilder->initialize($shop, $tikTokOrderData);

                $order = $orderBuilder->process();

                if ($order !== null) {
                    $orders[] = $order;
                }
            } catch (\Throwable $exception) {
                $this->syncLogService->addFromException($exception);
                $this->exceptionHelper->process($exception);

                continue;
            }
        }

        return array_filter($orders);
    }

    /**
     * @param \M2E\TikTokShop\Model\Order[] $orders
     */
    public function processMagentoOrders(array $orders): void
    {
        foreach ($orders as $order) {
            if ($this->isOrderChangedInParallelProcess($order)) {
                continue;
            }

            try {
                $this->createMagentoOrder($order);
            } catch (\Throwable $exception) {
                $this->syncLogService->addFromException($exception);
                $this->exceptionHelper->process($exception);

                continue;
            }
        }
    }

    public function createMagentoOrder(\M2E\TikTokShop\Model\Order $order)
    {
        if ($order->canCreateMagentoOrder()) {
            try {
                $order->getLogService()->setInitiator(\M2E\TikTokShop\Helper\Data::INITIATOR_EXTENSION);

                $order->addInfoLog(
                    'Magento order creation rules are met. M2E TikTok Shop Connect will attempt to create Magento order.',
                    [],
                    [],
                    true
                );

                $order->createMagentoOrder();
            } catch (\Throwable $exception) {
                return;
            }
        }

        if ($order->isCanceled() && !$order->getReserve()->isCanceled()) {
            $order->getReserve()->cancel();
        }

        if ($order->getReserve()->isNotProcessed() && $order->isReservable()) {
            $order->getReserve()->place();
        }

        if ($order->canCreateInvoice()) {
            $order->createInvoice();
        }

        $order->createShipments();

        if ($order->canCreateTracks()) {
            $order->createTracks();
        }
    }

    /**
     * This is going to protect from Magento Orders duplicates.
     * (Is assuming that there may be a parallel process that has already created Magento Order)
     * But this protection is not covering cases when two parallel cron processes are isolated by mysql transactions
     */
    public function isOrderChangedInParallelProcess(\M2E\TikTokShop\Model\Order $order): bool
    {
        $dbOrder = $this->orderRepository->find((int)$order->getId());
        if ($dbOrder === null) {
            return false;
        }

        if ($dbOrder->getMagentoOrderId() !== $order->getMagentoOrderId()) {
            return true;
        }

        return false;
    }

    private function isNeedCreateOrderByCreateDate(
        \DateTime $orderCreateDate,
        \DateTime $accountCreateDate,
        \DateTime $boundaryCreationDate
    ): bool {
        if (!$this->isValidateAccountCreateDate) {
            return true;
        }

        return $orderCreateDate >= $accountCreateDate
            && $orderCreateDate >= $boundaryCreationDate;
    }
}
