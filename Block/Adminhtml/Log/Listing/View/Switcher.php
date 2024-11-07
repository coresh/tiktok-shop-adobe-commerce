<?php

namespace M2E\TikTokShop\Block\Adminhtml\Log\Listing\View;

class Switcher extends \M2E\TikTokShop\Block\Adminhtml\Switcher
{
    public const VIEW_MODE_SEPARATED = 'separated';
    public const VIEW_MODE_GROUPED = 'grouped';

    protected $paramName = 'view_mode';
    protected $viewMode = null;

    /** @var \M2E\TikTokShop\Helper\Data\Session */
    private $dataSessionHelper;

    public function __construct(
        \M2E\TikTokShop\Helper\Data\Session $dataSessionHelper,
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Template $context,
        array $data = []
    ) {
        $this->dataSessionHelper = $dataSessionHelper;

        parent::__construct($context, $data);
    }

    public function getLabel()
    {
        return (string)__('View Mode');
    }

    public function getStyle()
    {
        return self::ADVANCED_STYLE;
    }

    public function hasDefaultOption(): bool
    {
        return false;
    }

    public function getDefaultParam()
    {
        $sessionViewMode = $this->dataSessionHelper->getValue('tikTokShop_log_listing_view_mode');

        if ($sessionViewMode === null) {
            return self::VIEW_MODE_SEPARATED;
        }

        return $sessionViewMode;
    }

    public function getSelectedParam()
    {
        if ($this->viewMode !== null) {
            return $this->viewMode;
        }

        $selectedViewMode = parent::getSelectedParam();

        $this->dataSessionHelper->setValue('tikTokShop_log_listing_view_mode', $selectedViewMode);

        $this->viewMode = $selectedViewMode;

        return $this->viewMode;
    }

    //---------------------------------------

    protected function loadItems()
    {
        $this->items = [
            'mode' => [
                'value' => [
                    [
                        'label' => __('Separated'),
                        'value' => self::VIEW_MODE_SEPARATED,
                    ],
                    [
                        'label' => __('Grouped'),
                        'value' => self::VIEW_MODE_GROUPED,
                    ],
                ],
            ],
        ];
    }
}
