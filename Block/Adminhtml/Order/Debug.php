<?php

namespace M2E\TikTokShop\Block\Adminhtml\Order;

use M2E\TikTokShop\Block\Adminhtml\Magento\AbstractContainer;

class Debug extends AbstractContainer
{
    protected $_template = 'order/debug.phtml';

    protected $taxCalculator;
    protected $taxModel;
    protected $storeModel;
    protected $quoteManager;
    /** @var \M2E\TikTokShop\Helper\Data\GlobalData */
    private $globalDataHelper;
    private \M2E\TikTokShop\Model\Magento\Quote\Store\ConfiguratorFactory $quoteConfiguratorFactory;

    public function __construct(
        \M2E\TikTokShop\Model\Magento\Quote\Store\ConfiguratorFactory $quoteConfiguratorFactory,
        \Magento\Tax\Model\Calculation $taxCalculator,
        \Magento\Tax\Model\ClassModel $taxModel,
        \Magento\Store\Model\Store $storeModel,
        \M2E\TikTokShop\Model\Magento\Quote\Manager $quoteManager,
        \M2E\TikTokShop\Helper\Data\GlobalData $globalDataHelper,
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Widget $context,
        array $data = []
    ) {
        $this->taxCalculator = $taxCalculator;
        $this->taxModel = $taxModel;
        $this->storeModel = $storeModel;
        $this->quoteManager = $quoteManager;
        $this->globalDataHelper = $globalDataHelper;

        parent::__construct($context, $data);
        $this->quoteConfiguratorFactory = $quoteConfiguratorFactory;
    }

    protected function _beforeToHtml()
    {
        /** @var \M2E\TikTokShop\Model\Order $order */
        $order = $this->globalDataHelper->getValue('order');
        $store = $this->storeModel->load($order->getStoreId());

        $storeConfigurator = $this->quoteConfiguratorFactory->create(
            $this->quoteManager->getBlankQuote(),
            $order->getProxy()
        );
        $this->setData(
            'product_price_includes_tax',
            $storeConfigurator->isPriceIncludesTax()
        );
        $this->setData(
            'shipping_price_includes_tax',
            $storeConfigurator->isShippingPriceIncludesTax()
        );
        $this->setData(
            'store_shipping_tax_class',
            $storeConfigurator->getShippingTaxClassId()
        );
        $this->setData(
            'store_tax_calculation_based_on',
            $storeConfigurator->getTaxCalculationBasedOn()
        );

        if ($store->getId() !== null) {
            $this->setData(
                'store_tax_calculation_algorithm',
                $store->getConfig(\Magento\Tax\Model\Config::XML_PATH_ALGORITHM)
            );

            // ---------------------------------------
            $request = new \Magento\Framework\DataObject([
                'product_class_id' => $store->getConfig(\Magento\Tax\Model\Config::CONFIG_XML_PATH_SHIPPING_TAX_CLASS),
            ]);

            $this->setData('store_shipping_tax_rate', $this->taxCalculator->getStoreRate($request, $store));
            // ---------------------------------------
        }
    }
}
