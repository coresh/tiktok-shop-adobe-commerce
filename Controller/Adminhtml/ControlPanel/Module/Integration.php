<?php

namespace M2E\TikTokShop\Controller\Adminhtml\ControlPanel\Module;

use M2E\TikTokShop\Controller\Adminhtml\Context;
use M2E\TikTokShop\Controller\Adminhtml\ControlPanel\AbstractCommand;
use M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Type;

class Integration extends AbstractCommand
{
    private \Magento\Framework\Data\Form\FormKey $formKey;
    private \M2E\TikTokShop\Model\Account\Create $accountCreate;
    /** @var \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Type\ListAction\RequestFactory */
    private Type\ListAction\RequestFactory $listRequestFactory;
    /** @var \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Type\Revise\RequestFactory */
    private Type\Revise\RequestFactory $reviseRequestFactory;
    /** @var \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Type\Relist\RequestFactory */
    private Type\Relist\RequestFactory $relistRequestFactory;
    private \M2E\TikTokShop\Model\Product\Repository $productRepository;
    private \M2E\TikTokShop\Model\Product\ActionCalculator $actionCalculator;
    /** @var \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Type\Stop\RequestFactory */
    private Type\Stop\RequestFactory $stopRequestFactory;

    public function __construct(
        \Magento\Framework\Data\Form\FormKey $formKey,
        \M2E\TikTokShop\Helper\View\ControlPanel $controlPanelHelper,
        \M2E\TikTokShop\Model\Account\Create $accountCreate,
        \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Type\ListAction\RequestFactory $listRequestFactory,
        \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Type\Revise\RequestFactory $reviseRequestFactory,
        \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Type\Relist\RequestFactory $relistRequestFactory,
        \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Type\Stop\RequestFactory $stopRequestFactory,
        \M2E\TikTokShop\Model\Product\Repository $productRepository,
        \M2E\TikTokShop\Model\Product\ActionCalculator $actionCalculator,
        Context $context
    ) {
        parent::__construct($controlPanelHelper, $context);
        $this->formKey = $formKey;
        $this->accountCreate = $accountCreate;
        $this->listRequestFactory = $listRequestFactory;
        $this->reviseRequestFactory = $reviseRequestFactory;
        $this->relistRequestFactory = $relistRequestFactory;
        $this->productRepository = $productRepository;
        $this->actionCalculator = $actionCalculator;
        $this->stopRequestFactory = $stopRequestFactory;
    }

    /**
     * @title "Print Request Data"
     * @description "Calculate Allowed Action for Listing Product"
     */
    public function getRequestDataAction()
    {
        $httpRequest = $this->getRequest();

        $listingProductId = $httpRequest->getParam('listing_product_id', null);
        if ($listingProductId !== null) {
            $listingProductId = (int)$listingProductId;
        }

        $form = $this->printFormForCalculateAction($listingProductId);
        $html = "<div style='padding: 20px;background:#d3d3d3;position:sticky;top:0;width:100vw'>$form</div>";

        if ($httpRequest->getParam('print')) {
            try {
                $listingProduct = $this->productRepository->get((int)$listingProductId);
                $action = $this->actionCalculator->calculate(
                    $listingProduct,
                    true,
                    \M2E\TikTokShop\Model\Product::STATUS_CHANGER_USER,
                );

                $html .= '<div>' . $this->printProductInfo($listingProduct, $action) . '</div>';
            } catch (\Throwable $exception) {
                $html .= sprintf(
                    '<div style="margin: 20px 0">%s</div>',
                    $exception->getMessage()
                );
            }
        }

        return $html;
    }

    private function printFormForCalculateAction(?int $listingProductId): string
    {
        $formKey = $this->formKey->getFormKey();
        $actionUrl = $this->getUrl('*/*/*', ['action' => 'getRequestData']);

        return <<<HTML
<form style="margin: 0; font-size: 16px" method="get" enctype="multipart/form-data" action="$actionUrl">
    <input name="form_key" value="$formKey" type="hidden" />
    <input name="print" value="1" type="hidden" />

    <label style="display: inline-block;">
        Listing Product ID:
        <input name="listing_product_id" style="width: 200px;" required value="$listingProductId">
    </label>
    <div style="margin: 10px 0 0 0;">
        <button type="submit">Calculate Allowed Action</button>
    </div>
</form>
HTML;
    }

    private function printProductInfo(
        \M2E\TikTokShop\Model\Product $listingProduct,
        \M2E\TikTokShop\Model\Product\Action $action
    ): ?string {
        $calculateAction = 'Nothing';
        if ($action->isActionList()) {
            $calculateAction = 'List';
            $request = $this->listRequestFactory->create(
                $listingProduct,
                $action->getConfigurator(),
                $action->getVariantSettings(),
            );
            $printResult = $this->printRequestData($request);
        } elseif ($action->isActionRevise()) {
            $calculateAction = sprintf(
                'Revise (Reason (%s))',
                implode(' | ', $action->getConfigurator()->getEnabledDataTypes()),
            );
            $request = $this->reviseRequestFactory->create(
                $listingProduct,
                $action->getConfigurator(),
                $action->getVariantSettings()
            );
            $printResult = $this->printRequestData($request);
        } elseif ($action->isActionStop()) {
            $calculateAction = 'Stop';
            $request = $this->stopRequestFactory->create(
                $listingProduct,
                $action->getConfigurator(),
                $action->getVariantSettings()
            );
            $printResult = $this->printRequestData($request);
        } elseif ($action->isActionRelist()) {
            $calculateAction = 'Relist';
            $request = $this->relistRequestFactory->create(
                $listingProduct,
                $action->getConfigurator(),
                $action->getVariantSettings()
            );
            $printResult = $this->printRequestData($request);
        } else {
            $printResult = 'Nothing action allowed.';
        }
        $currentStatusTitle = \M2E\TikTokShop\Model\Product::getStatusTitle($listingProduct->getStatus());

        $productSku = $listingProduct->getMagentoProduct()->getSku();

        $listingTitle = $listingProduct->getListing()->getTitle();

        return <<<HTML
<style>
    table {
      border-collapse: collapse;
      width: 100%;
    }

    td, th {
      border: 1px solid #dddddd;
      text-align: left;
      padding: 8px;
    }

    tr:nth-child(even) {
      background-color: #f2f2f2;
    }

</style>
<table>
    <tr>
        <td>Listing</td>
        <td>$listingTitle</td>
    </tr>
    <tr>
        <td>Product (SKU)</td>
        <td>$productSku</td>
    </tr>
    <tr>
        <td>Current Product Status</td>
        <td>$currentStatusTitle</td>
    </tr>
    <tr>
        <td>Calculate Action</td>
        <td>$calculateAction</td>
    </tr>
    <tr>
        <td>Request Data</td>
        <td>$printResult</td>
    </tr>
</table>
HTML;
    }

    private function printRequestData(
        \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\AbstractRequest $request
    ): string {
        return sprintf(
            '<pre>%s</pre>',
            htmlspecialchars(
                json_encode(
                    $request->getRequestData(),
                    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR,
                ),
                ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401,
            ),
        );
    }

    /**
     * @title "Build Order Quote"
     * @description "Print Order Quote Data"
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \M2E\TikTokShop\Model\Exception
     * @throws \Throwable
     */
    public function getPrintOrderQuoteDataAction()
    {
        $isPrint = (bool)$this->getRequest()->getParam('print');
        $orderId = $this->getRequest()->getParam('order_id');

        $buildResultHtml = '';
        if ($isPrint && $orderId) {
            $orderResource = $this->_objectManager->create(\M2E\TikTokShop\Model\ResourceModel\Order::class);
            $order = $this->_objectManager->create(\M2E\TikTokShop\Model\Order::class);

            $orderResource->load($order, (int)$orderId);

            if (!$order->getId()) {
                $this->getMessageManager()->addErrorMessage('Unable to load order instance.');

                return $this->_redirect($this->controlPanelHelper->getPageModuleTabUrl());
            }

            // Store must be initialized before products
            // ---------------------------------------
            $order->associateWithStore();
            $order->associateItemsWithProducts();
            // ---------------------------------------

            $proxy = $order->getProxy()->setStore($order->getStore());

            $magentoQuoteBuilder = $this
                ->_objectManager
                ->create(\M2E\TikTokShop\Model\Magento\Quote\Builder::class, ['proxyOrder' => $proxy]);

            $magentoQuoteManager = $this
                ->_objectManager
                ->create(\M2E\TikTokShop\Model\Magento\Quote\Manager::class);

            $quote = $magentoQuoteBuilder->build();

            $shippingAddressData = $quote->getShippingAddress()->getData();
            unset(
                $shippingAddressData['cached_items_all'],
                $shippingAddressData['cached_items_nominal'],
                $shippingAddressData['cached_items_nonnominal'],
            );
            $billingAddressData = $quote->getBillingAddress()->getData();
            unset(
                $billingAddressData['cached_items_all'],
                $billingAddressData['cached_items_nominal'],
                $billingAddressData['cached_items_nonnominal'],
            );
            $quoteData = $quote->getData();
            unset(
                $quoteData['items'],
                $quoteData['extension_attributes'],
            );

            $items = [];
            foreach ($quote->getAllItems() as $item) {
                $items[] = $item->getData();
            }

            $magentoQuoteManager->save($quote->setIsActive(false));

            $buildResultHtml = json_encode(
                json_decode(
                    json_encode([
                        'Grand Total' => $quote->getGrandTotal(),
                        'Shipping Amount' => $quote->getShippingAddress()->getShippingAmount(),
                        'Quote Data' => $quoteData,
                        'Shipping Address Data' => $shippingAddressData,
                        'Billing Address Data' => $billingAddressData,
                        'Items' => $items,
                    ]),
                    true,
                ),
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
            );
        }

        $formKey = $this->formKey->getFormKey();
        $actionUrl = $this->getUrl('*/*/*', ['action' => 'getPrintOrderQuoteData']);

        $formHtml = <<<HTML
<form method="get" enctype="multipart/form-data" action="$actionUrl">
    <input name="form_key" value="{$formKey}" type="hidden" />
    <input name="print" value="1" type="hidden" />
    <div>
        <label>Order ID: </label>
        <input name="order_id" value="$orderId" required>
        <button type="submit">Build</button>
    </div>
</form>
HTML;
        $resultHtml = $formHtml;
        if ($buildResultHtml !== '') {
            $resultHtml .= "<h3>Result</h3><div><pre>$buildResultHtml</pre></div>";
        }

        return $resultHtml;
    }

    /**
     * @title "Print Inspector Data"
     * @description "Print Inspector Data"
     * @new_line
     */
    public function getInspectorDataAction()
    {
        if ($this->getRequest()->getParam('print')) {
            $listingProductId = $this->getRequest()->getParam('listing_product_id');

            $listingProduct = $this
                ->_objectManager
                ->create(\M2E\TikTokShop\Model\Product\Repository::class)
                ->get($listingProductId);

            $instructionCollection = $this
                ->_objectManager
                ->create(\M2E\TikTokShop\Model\ResourceModel\Instruction\CollectionFactory::class)
                ->create();

            $instructionCollection->addFieldToFilter('listing_product_id', $listingProductId);

            $instructions = [];
            foreach ($instructionCollection->getItems() as $instruction) {
                $instruction->setListingProduct($listingProduct);
                $instructions[$instruction->getId()] = $instruction;
            }

            $checkerInput = $this
                ->_objectManager
                ->create(\M2E\TikTokShop\Model\Instruction\SynchronizationTemplate\Checker\InputFactory::class)
                ->create($listingProduct, $instructions);

            $html = '<pre>';

            $notListedChecker = $this
                ->_objectManager
                ->create(\M2E\TikTokShop\Model\Instruction\SynchronizationTemplate\Checker\CheckerFactory::class)
                ->create(
                    \M2E\TikTokShop\Model\Instruction\SynchronizationTemplate\Checker\NotListedChecker::class,
                    $checkerInput,
                );

            $html .= '<b>NotListed</b><br>';
            $html .= 'isAllowed: ' . json_encode($notListedChecker->isAllowed()) . '<br>';

            $inactiveChecker = $this
                ->_objectManager
                ->create(\M2E\TikTokShop\Model\Instruction\SynchronizationTemplate\Checker\CheckerFactory::class)
                ->create(
                    \M2E\TikTokShop\Model\Instruction\SynchronizationTemplate\Checker\InactiveChecker::class,
                    $checkerInput,
                );

            $html .= '<b>Inactive</b><br>';
            $html .= 'isAllowed: ' . json_encode($inactiveChecker->isAllowed()) . '<br>';

            $activeChecker = $this
                ->_objectManager
                ->create(\M2E\TikTokShop\Model\Instruction\SynchronizationTemplate\Checker\CheckerFactory::class)
                ->create(
                    \M2E\TikTokShop\Model\Instruction\SynchronizationTemplate\Checker\ActiveChecker::class,
                    $checkerInput,
                );

            $html .= '<b>Active</b><br>';
            $html .= 'isAllowed: ' . json_encode($activeChecker->isAllowed()) . '<br>';

            $magentoProduct = $listingProduct->getMagentoProduct();
            $html .= 'isStatusEnabled: ' . json_encode($magentoProduct->isStatusEnabled()) . '<br>';
            $html .= 'isStockAvailability: ' . json_encode($magentoProduct->isStockAvailability()) . '<br>';

            //--

            return $this->getResponse()->setBody($html);
        }

        $formKey = $this->formKey->getFormKey();
        $actionUrl = $this->getUrl('*/*/*', ['action' => 'getInspectorData']);

        return <<<HTML
<form method="get" enctype="multipart/form-data" action="{$actionUrl}">

    <div style="margin: 5px 0; width: 400px;">
        <label style="width: 170px; display: inline-block;">Listing Product ID: </label>
        <input name="listing_product_id" style="width: 200px;" required>
    </div>

    <input name="form_key" value="{$formKey}" type="hidden" />
    <input name="print" value="1" type="hidden" />

    <div style="margin: 10px 0; width: 365px; text-align: right;">
        <button type="submit">Show</button>
    </div>

</form>
HTML;
    }

    /**
     * @title "Add Account"
     * @description "Add account by auth code"
     */
    public function createAccountAction(): string
    {
        if ($this->getRequest()->getParam('auth_code')) {
            $params = $this->getRequest()->getParams();

            try {
                $this->accountCreate->create($params['auth_code'], $params['region']);
            } catch (\Throwable $e) {
                return $e->getMessage();
            }

            return 'Created';
        }

        $formKey = $this->formKey->getFormKey();
        $actionUrl = $this->getUrl('*/*/*', ['action' => 'createAccount']);

        return <<<HTML
<form method="get" enctype="multipart/form-data" action="$actionUrl">
    <div style="margin: 5px 0; width: 400px;">
        <label style="width: 170px; display: inline-block;">Auth Code: </label>
        <input name="auth_code" style="width: 200px;" required>
    </div>
    <div style="margin: 5px 0; width: 400px;">
        <label style="width: 170px; display: inline-block;">Region: </label>
        <select name="region" style="width: 200px" required>
            <option value="GB">United Kingdom</option>
            <option value="US">United States</option>
        </select>
    </div>
    <input name="form_key" value="$formKey" type="hidden" />

    <div style="margin: 10px 0; width: 365px; text-align: right;">
        <button type="submit">Create</button>
    </div>

</form>
HTML;
    }
}
