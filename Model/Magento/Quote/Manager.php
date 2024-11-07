<?php

namespace M2E\TikTokShop\Model\Magento\Quote;

use M2E\TikTokShop\Model\Exception;

class Manager
{
    /** @var \Magento\Quote\Api\CartRepositoryInterface */
    protected $quoteRepository;
    /** @var \M2E\TikTokShop\Model\Magento\Backend\Model\Session\Quote */
    protected $sessionQuote;
    /** @var \Magento\Quote\Model\QuoteManagement */
    protected $quoteManagement;
    /** @var \Magento\Checkout\Model\Session */
    protected $checkoutSession;
    /** @var \Magento\Sales\Model\OrderFactory */
    protected $orderFactory;

    private \M2E\TikTokShop\Helper\Module\Exception $exceptionHelper;

    public function __construct(
        \M2E\TikTokShop\Helper\Module\Exception $exceptionHelper,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \M2E\TikTokShop\Model\Magento\Backend\Model\Session\Quote $sessionQuote,
        \Magento\Quote\Model\QuoteManagement $quoteManagement,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\OrderFactory $orderFactory
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->sessionQuote = $sessionQuote;
        $this->quoteManagement = $quoteManagement;
        $this->checkoutSession = $checkoutSession;
        $this->orderFactory = $orderFactory;
        $this->exceptionHelper = $exceptionHelper;
    }

    /**
     * @return \Magento\Quote\Model\Quote
     */
    public function getBlankQuote()
    {
        $this->clearQuoteSessionStorage();

        $quote = $this->sessionQuote->getQuote();
        $quote->setIsSuperMode(false);

        return $quote;
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     *
     * @return \Magento\Framework\Model\AbstractExtensibleModel|\Magento\Sales\Api\Data\OrderInterface|null|object
     * @throws \M2E\TikTokShop\Model\Magento\Quote\FailDuringEventProcessing
     * @throws \Exception
     */
    public function submit(\Magento\Quote\Model\Quote $quote)
    {
        try {
            $order = $this->quoteManagement->submit($quote);
            if ($order === null) {
                throw new Exception(
                    'You are trying to create an order for Parent Product or Product that has been deleted.'
                );
            }

            return $order;
        } catch (\Throwable $exception) {
            $order = $this->orderFactory
                ->create()
                ->loadByIncrementIdAndStoreId(
                    $quote->getReservedOrderId(),
                    $quote->getStoreId()
                );

            if ($order !== null && $order->getId()) {
                $this->exceptionHelper->process($exception);
                throw new \M2E\TikTokShop\Model\Magento\Quote\FailDuringEventProcessing(
                    $order,
                    $exception->getMessage()
                );
            }
            // Remove ordered items from customer cart
            $quote->setIsActive(false)->save();
            throw $exception;
        }
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     *
     * @return \Magento\Quote\Api\Data\CartInterface
     */
    public function save(\Magento\Quote\Model\Quote $quote)
    {
        $this->quoteRepository->save($quote);

        return $quote;
    }

    public function replaceCheckoutQuote(\Magento\Quote\Model\Quote $quote)
    {
        $this->checkoutSession->replaceQuote($quote);
    }

    public function clearQuoteSessionStorage()
    {
        $this->sessionQuote->clearStorage();
    }
}
