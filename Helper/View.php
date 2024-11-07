<?php

namespace M2E\TikTokShop\Helper;

class View
{
    public const LISTING_CREATION_MODE_FULL = 0;
    public const LISTING_CREATION_MODE_LISTING_ONLY = 1;

    public const MOVING_LISTING_OTHER_SELECTED_SESSION_KEY = 'moving_listing_other_selected';
    public const MOVING_LISTING_PRODUCTS_SELECTED_SESSION_KEY = 'moving_listing_products_selected';

    /** @var \Magento\Backend\Model\UrlInterface */
    private $urlBuilder;
    /** @var \M2E\TikTokShop\Helper\View\TikTokShop */
    private $viewHelper;
    /** @var \M2E\TikTokShop\Helper\View\TikTokShop\Controller */
    private $controllerHelper;
    /** @var \Magento\Framework\App\RequestInterface */
    private $request;

    public function __construct(
        \Magento\Backend\Model\UrlInterface $urlBuilder,
        \M2E\TikTokShop\Helper\View\TikTokShop $viewHelper,
        \M2E\TikTokShop\Helper\View\TikTokShop\Controller $controllerHelper,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->viewHelper = $viewHelper;
        $this->controllerHelper = $controllerHelper;
        $this->request = $request;
    }

    public function getViewHelper(): View\TikTokShop
    {
        return $this->viewHelper;
    }

    public function getControllerHelper(): View\TikTokShop\Controller
    {
        return $this->controllerHelper;
    }

    public function getCurrentView(): ?string
    {
        $controllerName = $this->request->getControllerName();

        if ($controllerName === null) {
            return null;
        }

        if (stripos($controllerName, \M2E\TikTokShop\Helper\View\TikTokShop::NICK) !== false) {
            return \M2E\TikTokShop\Helper\View\TikTokShop::NICK;
        }

        if (stripos($controllerName, \M2E\TikTokShop\Helper\View\ControlPanel::NICK) !== false) {
            return \M2E\TikTokShop\Helper\View\ControlPanel::NICK;
        }

        if (stripos($controllerName, 'system_config') !== false) {
            return \M2E\TikTokShop\Helper\View\Configuration::NICK;
        }

        return null;
    }

    // ---------------------------------------

    public function isCurrentViewTikTokShop(): bool
    {
        return $this->getCurrentView() == \M2E\TikTokShop\Helper\View\TikTokShop::NICK;
    }

    public function isCurrentViewControlPanel(): bool
    {
        return $this->getCurrentView() == \M2E\TikTokShop\Helper\View\ControlPanel::NICK;
    }

    public function isCurrentViewConfiguration(): bool
    {
        return $this->getCurrentView() == \M2E\TikTokShop\Helper\View\Configuration::NICK;
    }

    public function getUrl($row, $controller, $action, array $params = []): string
    {
        return $this->urlBuilder->getUrl("*/tiktokshop_$controller/$action", $params);
    }

    public function getModifiedLogMessage($logMessage)
    {
        return \M2E\TikTokShop\Helper\Data::escapeHtml(
            \M2E\TikTokShop\Helper\Module\Log::decodeDescription($logMessage),
            ['a'],
            ENT_NOQUOTES
        );
    }
}
