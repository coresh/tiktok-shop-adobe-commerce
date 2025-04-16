<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Block\Adminhtml\Listing\View;

class Switcher extends \M2E\TikTokShop\Block\Adminhtml\Switcher
{
    public const VIEW_MODE_TIKTOKSHOP = 'tikTokShop';
    public const VIEW_MODE_MAGENTO = 'magento';
    public const VIEW_MODE_SETTINGS = 'settings';

    protected $paramName = 'view_mode';
    private ?string $viewMode = null;

    protected \M2E\TikTokShop\Helper\Data\Session $sessionDataHelper;
    private \M2E\TikTokShop\Model\Listing\Ui\RuntimeStorage $uiListingRuntimeStorage;

    public function __construct(
        \M2E\TikTokShop\Model\Listing\Ui\RuntimeStorage $uiListingRuntimeStorage,
        \M2E\TikTokShop\Helper\Data\Session $sessionDataHelper,
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Template $context,
        array $data = []
    ) {
        $this->sessionDataHelper = $sessionDataHelper;
        $this->uiListingRuntimeStorage = $uiListingRuntimeStorage;
        parent::__construct($context, $data);
    }

    public function getDefaultViewMode(): string
    {
        return self::VIEW_MODE_TIKTOKSHOP;
    }

    public function getTooltip(): string
    {
        return (string)__(
            '<p>There are several <strong>View Modes</strong> available to you:</p>' .
            '<ul><li><p><strong>%channel_title</strong> - displays Product details with respect to %channel_title ' .
            'Item information. Using this Mode, you can easily filter down the list of Products based on %channel_title ' .
            'Item details as well as perform Actions to %channel_title Items in bulk ' .
            '(i.e. List, Revise, Relist, Stop, etc);</p></li>' .
            '<li><p><strong>Settings</strong> - displays information about the Settings set for the Products ' .
            '(i.e. Selling Settings, %channel_title Categories, etc). Using this Mode, you can easily find Products ' .
            'by reference to the Settings they use as well as edit already defined Settings in bulk.</p></li>' .
            '<li><p><strong>Magento</strong> - displays Products information with regard to Magento Catalog. ' .
            'Using this Mode, you can easily find Products based on Magento Product information ' .
            '(i.e. Magento QTY, Stock Status, etc);</p></li></ul>',
            [
                'channel_title' => \M2E\TikTokShop\Helper\Module::getChannelTitle()
            ]
        );
    }

    public function getLabel(): string
    {
        return (string)__('View Mode');
    }

    public function hasDefaultOption(): bool
    {
        return false;
    }

    public function getStyle(): int
    {
        return self::ADVANCED_STYLE;
    }

    public function getDefaultParam()
    {
        $sessionViewMode = $this->sessionDataHelper->getValue(
            "TikTokShop_listing_{$this->getListing()->getId()}_view_mode"
        );

        if ($sessionViewMode === null) {
            return $this->getDefaultViewMode();
        }

        return $sessionViewMode;
    }

    public function getSelectedParam()
    {
        if ($this->viewMode !== null) {
            return $this->viewMode;
        }

        $selectedViewMode = parent::getSelectedParam();

        $this->sessionDataHelper->setValue(
            "TikTokShop_listing_{$this->getListing()->getId()}_view_mode",
            $selectedViewMode
        );

        $this->viewMode = $selectedViewMode;

        return $this->viewMode;
    }

    protected function loadItems(): void
    {
        $this->items = [
            'mode' => [
                'value' => [
                    [
                        'value' => self::VIEW_MODE_TIKTOKSHOP,
                        'label' => (string)__(\M2E\TikTokShop\Helper\Module::getChannelTitle()),
                    ],
                    [
                        'value' => self::VIEW_MODE_SETTINGS,
                        'label' => (string)__('Settings'),
                    ],
                    [
                        'value' => self::VIEW_MODE_MAGENTO,
                        'label' => (string)__('Magento'),
                    ],
                ],
            ],
        ];
    }

    private function getListing(): \M2E\TikTokShop\Model\Listing
    {
        return $this->uiListingRuntimeStorage->getListing();
    }
}
