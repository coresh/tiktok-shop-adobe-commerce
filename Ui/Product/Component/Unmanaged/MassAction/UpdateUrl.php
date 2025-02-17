<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Ui\Product\Component\Unmanaged\MassAction;

class UpdateUrl extends \Magento\Ui\Component\Action
{
    protected \Magento\Framework\UrlInterface $urlBuilder;
    protected \Magento\Framework\App\RequestInterface $request;

    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\UrlInterface $urlBuilder,
        array $components = [],
        array $data = [],
        $actions = null
    ) {
        parent::__construct($context, $components, $data, $actions);

        $this->urlBuilder = $urlBuilder;
        $this->request = $request;
    }

    public function prepare()
    {
        parent::prepare();

        $config = $this->getConfiguration();

        $accountId = $this->request->getParam('account');

        if (isset($config['url'])) {
            $config['url'] = $this->urlBuilder->getUrl($config['url'], ['account_id' => $accountId]);
        }

        $this->setData('config', $config);
    }
}
