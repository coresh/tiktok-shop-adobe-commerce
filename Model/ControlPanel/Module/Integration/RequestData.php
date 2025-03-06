<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\ControlPanel\Module\Integration;

use M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action;

class RequestData
{
    private const PARAM_PRODUCT_ID = 'product_id';
    private const PARAM_CALCULATOR_ACTION = 'calculator_action';
    private const PARAM_PRINT = 'print';

    private \M2E\TikTokShop\Model\Product\Repository $productRepository;
    private \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Type\ListAction\RequestFactory $listRequestFactory;
    private \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Type\Revise\RequestFactory $reviseRequestFactory;
    private \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Type\Relist\RequestFactory $relistRequestFactory;
    private \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Type\Stop\RequestFactory $stopRequestFactory;
    private \M2E\TikTokShop\Model\Product\ActionCalculator $actionCalculator;
    private \Magento\Framework\Data\Form\FormKey $formKey;
    private \Magento\Framework\UrlInterface $url;
    private \Magento\Framework\Escaper $escaper;

    public function __construct(
        \M2E\TikTokShop\Model\Product\Repository $productRepository,
        \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Type\ListAction\RequestFactory $listRequestFactory,
        \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Type\Revise\RequestFactory $reviseRequestFactory,
        \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Type\Relist\RequestFactory $relistRequestFactory,
        \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Type\Stop\RequestFactory $stopRequestFactory,
        \M2E\TikTokShop\Model\Product\ActionCalculator $actionCalculator,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\Escaper $escaper
    ) {
        $this->productRepository = $productRepository;
        $this->listRequestFactory = $listRequestFactory;
        $this->reviseRequestFactory = $reviseRequestFactory;
        $this->relistRequestFactory = $relistRequestFactory;
        $this->stopRequestFactory = $stopRequestFactory;
        $this->actionCalculator = $actionCalculator;
        $this->formKey = $formKey;
        $this->url = $url;
        $this->escaper = $escaper;
    }

    public function execute(\Magento\Framework\App\RequestInterface $request): string
    {
        $productId = $request->getParam(self::PARAM_PRODUCT_ID, '');
        $calculatorAction = $request->getParam(self::PARAM_CALCULATOR_ACTION, 'auto');

        $body = $this->printFormForCalculateAction($productId, $calculatorAction);

        if ($request->getParam(self::PARAM_PRINT)) {
            try {
                $listingProduct = $this->productRepository->get((int)$productId);

                if ($calculatorAction === 'list') {
                    $variantSetting = new Action\VariantSettings();
                    foreach ($listingProduct->getVariants() as $variant) {
                        $variantSetting->add($variant->getId(), Action\VariantSettings::ACTION_ADD);
                    }

                    $action = \M2E\TikTokShop\Model\Product\Action::createList(
                        $listingProduct,
                        (new Action\Configurator())->enableAll(),
                        $variantSetting
                    );
                } elseif ($calculatorAction === 'revise') {
                    $variantSetting = new Action\VariantSettings();
                    foreach ($listingProduct->getVariants() as $variant) {
                        $variantSetting->add($variant->getId(), Action\VariantSettings::ACTION_REVISE);
                    }

                    $action = \M2E\TikTokShop\Model\Product\Action::createRevise(
                        $listingProduct,
                        (new Action\Configurator())->enableAll(),
                        $variantSetting
                    );
                } elseif ($calculatorAction === 'relist') {
                    $variantSetting = new Action\VariantSettings();
                    foreach ($listingProduct->getVariants() as $variant) {
                        $variantSetting->add($variant->getId(), Action\VariantSettings::ACTION_ADD);
                    }

                    $action = \M2E\TikTokShop\Model\Product\Action::createRelist(
                        $listingProduct,
                        (new Action\Configurator())->enableAll(),
                        $variantSetting
                    );
                } elseif ($calculatorAction === 'stop') {
                    $action = \M2E\TikTokShop\Model\Product\Action::createStop(
                        $listingProduct,
                    );
                } else {
                    $action = $this->actionCalculator->calculate(
                        $listingProduct,
                        true,
                        \M2E\TikTokShop\Model\Product::STATUS_CHANGER_USER,
                    );
                }

                $body .= '<div>' . $this->printProductInfo($listingProduct, $action) . '</div>';
            } catch (\Throwable $exception) {
                $body .= sprintf(
                    '<div style="margin: 20px 0">%s</div>',
                    $exception->getMessage()
                );
            }
        }

        return $this->renderHtml($body);
    }

    private function printFormForCalculateAction(string $productId = '', string $selectedAction = 'auto'): string
    {
        $formKey = $this->formKey->getFormKey();
        $actionUrl = $this->url->getUrl('*/*/*', ['action' => 'getRequestData']);

        $actionsList = [
            ['value' => 'auto', 'label' => 'Auto'],
            ['value' => 'list', 'label' => 'List'],
            ['value' => 'revise', 'label' => 'Revise'],
            ['value' => 'relist', 'label' => 'Relist'],
            ['value' => 'stop', 'label' => 'Stop'],
        ];

        $actionsOptions = '';
        foreach ($actionsList as $action) {
            $actionsOptions .= sprintf(
                '<option value="%s" %s>%s</option>',
                $action['value'],
                $selectedAction === $action['value'] ? 'selected' : '',
                $action['label']
            );
        }

        return <<<HTML
<div class="sticky-form-wrapper">
    <form method="get" enctype="multipart/form-data" action="$actionUrl">
        <input name="form_key" value="$formKey" type="hidden" />
        <input name="print" value="1" type="hidden" />

        <div class="form-row">
            <label for="product_id">Product ID:</label>
            <input id="product_id" name="product_id" required value="$productId">
        </div>
        <div class="form-row">
            <label for="calculator_action">Action:</label>
            <select id="calculator_action" name="calculator_action">$actionsOptions</select>
        </div>
        <div class="form-row">
            <button class="run" type="submit">Run</button>
        </div>
    </form>
</div>
HTML;
    }

    private function printProductInfo(
        \M2E\TikTokShop\Model\Product $listingProduct,
        \M2E\TikTokShop\Model\Product\Action $action
    ): ?string {
        if ($action->isActionList()) {
            $calculateAction = 'List';
            $request = $this->listRequestFactory->create(
                $listingProduct,
                $action->getConfigurator(),
                $action->getVariantSettings(),
            );
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
        } elseif ($action->isActionStop()) {
            $calculateAction = 'Stop';
            $request = $this->stopRequestFactory->create(
                $listingProduct,
                $action->getConfigurator(),
                $action->getVariantSettings()
            );
        } elseif ($action->isActionRelist()) {
            $calculateAction = 'Relist';
            $request = $this->relistRequestFactory->create(
                $listingProduct,
                $action->getConfigurator(),
                $action->getVariantSettings()
            );
        } else {
            $request = null;
            $calculateAction = 'Nothing';
        }

        $requestData = $request === null
            ? 'Nothing action allowed.'
            : $this->printCodeBlock($request->getRequestData());

        $requestMetaData = $request === null
            ? 'Nothing action allowed.'
            : $this->printCodeBlock($request->getMetaData());

        $currentStatusTitle = \M2E\TikTokShop\Model\Product::getStatusTitle($listingProduct->getStatus());
        $productSku = $listingProduct->getMagentoProduct()->getSku();
        $listingTitle = $listingProduct->getListing()->getTitle();

        return <<<HTML
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
        <td>$requestData</td>
    </tr>
    <tr>
        <td>Request MetaData</td>
        <td>$requestMetaData</td>
    </tr>
</table>
HTML;
    }

    private function printCodeBlock(array $data): string
    {
        return sprintf(
            '<pre class="white-space_pre-wrap">%s</pre>',
            $this->escaper->escapeHtml(
                json_encode(
                    $data,
                    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR,
                ),
                ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401,
            ),
        );
    }

    private function renderHtml(string $body): string
    {
        return <<<HTML
<!DOCTYPE html>
<html lang="en">
  <head>
    <title>TTS Module Tools | Print Request Data</title>
    <style>
    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }

    .sticky-form-wrapper {
        background: #d3d3d3;
        position: sticky;
        top: 0;
        width: 100%
    }

    form {
        padding: 10px;
        font-size: 16px;
        position: relative
    }

    .form-row:not(:last-child) {
        margin-bottom: 10px
    }

    .form-row label {
        display: inline-block;
        min-width: 100px
    }

    .form-row input, .form-row select {
        min-width: 200px
    }

    button.run {
        padding: 7px 15px; font-weight: 700
    }

    table {
      border-collapse: collapse;
      width: 100%;
    }

    td:first-child {
        width: 200px;
    }

    .white-space_pre-wrap {
        white-space: pre-wrap;
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
  </head>
  <body>$body</body>
</html>
HTML;
    }
}
