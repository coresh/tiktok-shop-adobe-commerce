<?php

namespace M2E\TikTokShop\Block\Adminhtml\System\Config;

use M2E\TikTokShop\Block\Adminhtml\Magento\Form\AbstractForm;

class Sections extends AbstractForm
{
    /** @var \Magento\Framework\View\Asset\Repository */
    protected $assetRepo;

    public const SECTION_ID_MODULE_AND_CHANNELS = 'tiktokshop_module_and_channels';
    public const SECTION_ID_INTERFACE_AND_MAGENTO_INVENTORY = 'tiktokshop_interface_and_magento_inventory';
    public const SECTION_ID_LOGS_CLEARING = 'tiktokshop_logs_clearing';
    public const SECTION_ID_LICENSE = 'tiktokshop_extension_key';

    public const SELECT = \M2E\TikTokShop\Block\Adminhtml\System\Config\Form\Element\Select::class;
    public const TEXT = \M2E\TikTokShop\Block\Adminhtml\System\Config\Form\Element\Text::class;
    public const LINK = \M2E\TikTokShop\Block\Adminhtml\System\Config\Form\Element\Link::class;
    public const HELP_BLOCK = \M2E\TikTokShop\Block\Adminhtml\Magento\Form\Element\HelpBlock::class;
    public const STATE_CONTROL_BUTTON =
        \M2E\TikTokShop\Block\Adminhtml\System\Config\Form\Element\StateControlButton::class;
    public const BUTTON = \M2E\TikTokShop\Block\Adminhtml\System\Config\Form\Element\Button::class;
    public const NOTE = \Magento\Framework\Data\Form\Element\Note::class;

    /**
     * @param \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Template $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param array $data
     */
    public function __construct(
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        $this->assetRepo = $context->getAssetRepository();
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _toHtml(): string
    {
        $generalBlock = $this->getLayout()->createBlock(\M2E\TikTokShop\Block\Adminhtml\General::class);
        $asset = $this->assetRepo->createAsset("M2E_TikTokShop::css/style.css");

        $this->js->addRequireJs(
            ['s' => 'TikTokShop/Settings'],
            <<<JS
window.SettingsObj = new Settings();
JS
        );

        $html = <<<HTML
{$generalBlock->toHtml()}
<style>
@import url("{$asset->getUrl()}");
</style>
HTML;

        return $html . parent::_toHtml();
    }
}
