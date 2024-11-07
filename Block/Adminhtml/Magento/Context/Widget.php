<?php

namespace M2E\TikTokShop\Block\Adminhtml\Magento\Context;

use Magento\Backend\Block\Widget\Button;
use Magento\Backend\Block\Widget\Context;
use M2E\TikTokShop\Block\Adminhtml\Traits;
use M2E\TikTokShop\Block\Adminhtml\Magento\Renderer;

class Widget extends Context
{
    use Traits\RendererTrait;

    public function __construct(
        Renderer\CssRenderer $css,
        Renderer\JsPhpRenderer $jsPhp,
        Renderer\JsRenderer $js,
        Renderer\JsTranslatorRenderer $jsTranslatorRenderer,
        Renderer\JsUrlRenderer $jsUrlRenderer,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\View\LayoutInterface $layout,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Framework\View\DesignInterface $design,
        \Magento\Framework\Session\Generic $session,
        \Magento\Framework\Session\SidResolverInterface $sidResolver,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Framework\View\ConfigInterface $viewConfig,
        \Magento\Framework\App\Cache\StateInterface $cacheState,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\Filter\FilterManager $filterManager,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\View\FileSystem $viewFileSystem,
        \Magento\Framework\View\TemplateEnginePool $enginePool,
        \Magento\Framework\App\State $appState,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\Page\Config $pageConfig,
        \Magento\Framework\View\Element\Template\File\Resolver $resolver,
        \Magento\Framework\View\Element\Template\File\Validator $validator,
        \Magento\Framework\AuthorizationInterface $authorization,
        \Magento\Backend\Model\Session $backendSession,
        \Magento\Framework\Math\Random $mathRandom,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\Framework\Code\NameBuilder $nameBuilder,
        Button\ButtonList $buttonList,
        Button\ToolbarInterface $toolbar
    ) {
        $this->css = $css;
        $this->jsPhp = $jsPhp;
        $this->js = $js;
        $this->jsTranslator = $jsTranslatorRenderer;
        $this->jsUrl = $jsUrlRenderer;

        parent::__construct(
            $request,
            $layout,
            $eventManager,
            $urlBuilder,
            $cache,
            $design,
            $session,
            $sidResolver,
            $scopeConfig,
            $assetRepo,
            $viewConfig,
            $cacheState,
            $logger,
            $escaper,
            $filterManager,
            $localeDate,
            $inlineTranslation,
            $filesystem,
            $viewFileSystem,
            $enginePool,
            $appState,
            $storeManager,
            $pageConfig,
            $resolver,
            $validator,
            $authorization,
            $backendSession,
            $mathRandom,
            $formKey,
            $nameBuilder,
            $buttonList,
            $toolbar
        );
    }

    /**
     * @return \M2E\TikTokShop\Block\Adminhtml\Magento\Renderer\JsPhpRenderer
     */
    public function getJsPhp()
    {
        return $this->jsPhp;
    }

    /**
     * @return \M2E\TikTokShop\Block\Adminhtml\Magento\Renderer\JsTranslatorRenderer
     */
    public function getJsTranslator()
    {
        return $this->jsTranslator;
    }

    /**
     * @return \M2E\TikTokShop\Block\Adminhtml\Magento\Renderer\JsUrlRenderer
     */
    public function getJsUrl()
    {
        return $this->jsUrl;
    }

    /**
     * @return \M2E\TikTokShop\Block\Adminhtml\Magento\Renderer\JsRenderer
     */
    public function getJs()
    {
        return $this->js;
    }

    /**
     * @return \M2E\TikTokShop\Block\Adminhtml\Magento\Renderer\CssRenderer
     */
    public function getCss()
    {
        return $this->css;
    }
}
