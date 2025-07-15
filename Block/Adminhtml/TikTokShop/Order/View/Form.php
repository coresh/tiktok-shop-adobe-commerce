<?php

namespace M2E\TikTokShop\Block\Adminhtml\TikTokShop\Order\View;

use M2E\TikTokShop\Block\Adminhtml\Magento\AbstractContainer;

class Form extends AbstractContainer
{
    protected $_template = 'tiktokshop/order.phtml';

    public ?string $realMagentoOrderId = null;

    public array $shippingAddress = [];

    public \M2E\TikTokShop\Model\Order $order;

    // ----------------------------------------

    private \Magento\Backend\Model\UrlInterface $urlBuilder;
    private \Magento\Tax\Model\Calculation $taxCalculator;
    private \Magento\Store\Model\StoreManager $storeManager;
    private \M2E\TikTokShop\Helper\Data\GlobalData $globalDataHelper;
    private \M2E\TikTokShop\Block\Adminhtml\TikTokShop\Order\StatusHelper $orderStatusHelper;
    private \M2E\TikTokShop\Model\Currency $currency;

    public function __construct(
        \M2E\TikTokShop\Block\Adminhtml\TikTokShop\Order\StatusHelper $orderStatusHelper,
        \Magento\Backend\Model\UrlInterface $urlBuilder,
        \Magento\Tax\Model\Calculation $taxCalculator,
        \Magento\Store\Model\StoreManager $storeManager,
        \M2E\TikTokShop\Helper\Data\GlobalData $globalDataHelper,
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Widget $context,
        \M2E\TikTokShop\Model\Currency $currency,
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->taxCalculator = $taxCalculator;
        $this->storeManager = $storeManager;
        $this->globalDataHelper = $globalDataHelper;
        $this->orderStatusHelper = $orderStatusHelper;
        $this->currency = $currency;

        parent::__construct($context, $data);
    }

    public function _construct()
    {
        parent::_construct();

        $this->setId('ttsOrderViewForm');

        $this->order = $this->globalDataHelper->getValue('order');
    }

    protected function _beforeToHtml()
    {
        // Magento order data
        // ---------------------------------------
        $this->realMagentoOrderId = null;

        $magentoOrder = $this->order->getMagentoOrder();
        if ($magentoOrder !== null) {
            $this->realMagentoOrderId = (string)$magentoOrder->getRealOrderId();
        }
        // ---------------------------------------

        $data = [
            'class' => 'primary',
            'label' => __('Edit'),
            'onclick' => "OrderEditItemObj.openEditShippingAddressPopup({$this->order->getId()});",
        ];
        $buttonBlock = $this->getLayout()->createBlock(\M2E\TikTokShop\Block\Adminhtml\Magento\Button::class)
                            ->setData($data);
        $this->setChild('edit_shipping_info', $buttonBlock);

        // ---------------------------------------
        if ($magentoOrder !== null && $magentoOrder->hasShipments() && $this->order->canUpdateShippingStatus()) {
            $url = $this->getUrl('*/order/resubmitShippingInfo', ['id' => $this->order->getId()]);
            $data = [
                'class' => 'primary',
                'label' => __('Resend Shipping Information'),
                'onclick' => 'setLocation(\'' . $url . '\');',
            ];
            $buttonBlock = $this->getLayout()->createBlock(\M2E\TikTokShop\Block\Adminhtml\Magento\Button::class)
                                ->setData($data);
            $this->setChild('resubmit_shipping_info', $buttonBlock);
        }
        // ---------------------------------------

        // Shipping data
        // ---------------------------------------
        /** @var \M2E\TikTokShop\Model\TikTokShop\Order\ShippingAddress $shippingAddress */
        $shippingAddress = $this->order->getShippingAddress();

        $this->shippingAddress = $shippingAddress->getData();
        $this->shippingAddress['country_name'] = $shippingAddress->getCountryName();
        // ---------------------------------------

        // ---------------------------------------
        $buttonAddNoteBlock = $this->getLayout()->createBlock(\M2E\TikTokShop\Block\Adminhtml\Magento\Button::class)
                                   ->setData(
                                       [
                                           'label' => __('Add Note'),
                                           'onclick' => "OrderNoteObj.openAddNotePopup({$this->order->getId()})",
                                           'class' => 'order_note_btn',
                                       ]
                                   );

        $shippingAddressBlock = $this
            ->getLayout()
            ->createBlock(\M2E\TikTokShop\Block\Adminhtml\Order\Edit\ShippingAddress::class, '', [
                'order' => $this->order,
            ]);
        $this->setChild('shipping_address', $shippingAddressBlock);

        $orderItemsBlock = $this
            ->getLayout()
            ->createBlock(\M2E\TikTokShop\Block\Adminhtml\TikTokShop\Order\View\Item::class, '', [
                'order' => $this->order,
            ]);
        $this->setChild('item', $orderItemsBlock);

        $this->setChild(
            'item_edit',
            $this->getLayout()->createBlock(\M2E\TikTokShop\Block\Adminhtml\Order\Item\Edit::class)
        );
        $this->setChild(
            'log',
            $this->getLayout()->createBlock(\M2E\TikTokShop\Block\Adminhtml\Order\View\Log\Grid::class)
        );
        $this->setChild(
            'order_note_grid',
            $this->getLayout()->createBlock(\M2E\TikTokShop\Block\Adminhtml\Order\Note\Grid::class)
        );
        $this->setChild('add_note_button', $buttonAddNoteBlock);

        $this->jsUrl->addUrls([
            'order/getDebugInformation' => $this->getUrl(
                '*/order/getDebugInformation/',
                ['id' => $this->getRequest()->getParam('id')]
            ),
            'getEditShippingAddressForm' => $this->getUrl(
                '*/tiktokshop_order_shippingAddress/edit',
                ['id' => $this->getRequest()->getParam('id')]
            ),
            'saveShippingAddress' => $this->getUrl(
                '*/tiktokshop_order_shippingAddress/save',
                ['id' => $this->getRequest()->getParam('id')]
            ),
            'tiktokshop_account/edit' => $this->getUrl(
                '*/tiktokshop_account/edit',
                [
                    'close_on_save' => true,
                ]
            ),
        ]);

        $this->jsPhp->addConstants(
            \M2E\TikTokShop\Helper\Data::getClassConstants(
                \M2E\TikTokShop\Controller\Adminhtml\Order\AssignToMagentoProduct::class
            )
        );

        return parent::_beforeToHtml();
    }

    //########################################

    private function getStore()
    {
        /** @psalm-suppress TypeDoesNotContainNull */
        if ($this->order->getStoreId() === null) {
            return null;
        }

        try {
            $store = $this->storeManager->getStore($this->order->getStoreId());
        } catch (\Exception $e) {
            return null;
        }

        return $store;
    }

    public function isCurrencyAllowed()
    {
        $store = $this->getStore();

        if ($store === null) {
            return true;
        }

        return $this->currency->isAllowed($this->order->getCurrency(), $store);
    }

    public function hasCurrencyConversionRate()
    {
        $store = $this->getStore();

        if ($store === null) {
            return true;
        }

        return $this->currency->getConvertRateFromBase($this->order->getCurrency(), $store) != 0;
    }

    //########################################

    public function getSubtotalPrice()
    {
        return $this->order->getSubtotalPrice();
    }

    public function getShippingPrice()
    {
        $shippingPrice = $this->order->getShippingPrice();
        if (!$this->order->isVatTax()) {
            return $shippingPrice;
        }

        $shippingPrice -= $this->taxCalculator->calcTaxAmount(
            $shippingPrice,
            $this->order->getTaxRate(),
            true,
            false
        );

        return $shippingPrice;
    }

    public function getTaxAmount()
    {
        $taxAmount = $this->order->getTaxAmount();
        if (!$this->order->isVatTax()) {
            return $taxAmount;
        }

        $shippingPrice = $this->order->getShippingPrice();
        $shippingTaxAmount = $this->taxCalculator->calcTaxAmount(
            $shippingPrice,
            $this->order->getTaxRate(),
            true,
            false
        );

        return $taxAmount + $shippingTaxAmount;
    }

    public function formatPrice($currencyName, $priceValue)
    {
        return $this->currency->formatPrice($currencyName, $priceValue);
    }

    //########################################

    public function getOrderStatusLabel(): string
    {
        return $this->orderStatusHelper->getStatusLabel($this->order->getOrderStatus());
    }

    public function getOrderStatusColor(): string
    {
        return $this->orderStatusHelper->getStatusColor($this->order->getOrderStatus());
    }

    protected function _toHtml()
    {
        $orderNoteGridId = $this->getChildBlock('order_note_grid')->getId();
        $this->jsTranslator
            ->add('Custom Note', __('Custom Note'));

        $this->js->add(
            <<<JS
    require([
        'TikTokShop/Order/Note'
    ], function(){
        window.OrderNoteObj = new OrderNote('$orderNoteGridId');
    });
JS
        );

        return parent::_toHtml();
    }

    public function getUrlBuilder(): \Magento\Backend\Model\UrlInterface
    {
        return $this->urlBuilder;
    }

    public function renderCancellationRequestBlock(): string
    {
        if (!$this->order->isBuyerCancellationRequest()) {
            return '';
        }

        return $this
            ->getLayout()
            ->createBlock(CancellationRequest::class, '', ['order' => $this->order])
            ->toHtml();
    }

    public function renderReturnRequestBlock(): string
    {
        if (!$this->order->hasOrderReturnRequestedItemInPendingStatus()) {
            return '';
        }

        return $this
            ->getLayout()
            ->createBlock(ReturnRequest::class, '', ['order' => $this->order])
            ->toHtml();
    }
}
