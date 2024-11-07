<?php

namespace M2E\TikTokShop\Block\Adminhtml\ControlPanel\Info;

use M2E\TikTokShop\Block\Adminhtml\Magento\AbstractBlock;

/**
 * Class \M2E\TikTokShop\Block\Adminhtml\ControlPanel\Info\License
 */
class License extends AbstractBlock
{
    /** @var \M2E\TikTokShop\Helper\Client */
    private $clientHelper;
    /** @var \M2E\TikTokShop\Helper\Data */
    private $dataHelper;
    /** @var \M2E\TikTokShop\Helper\Module */
    private $moduleHelper;
    /** @var \M2E\TikTokShop\Helper\Module\License */
    private $licenseHelper;
    /** @var array */
    public $licenseData;
    /** @var array */
    public $locationData;

    /**
     * @param \M2E\TikTokShop\Helper\Client $clientHelper
     * @param \M2E\TikTokShop\Helper\Data $dataHelper
     * @param \M2E\TikTokShop\Helper\Module $moduleHelper
     * @param \M2E\TikTokShop\Helper\Module\License $licenseHelper
     * @param \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Template $context
     * @param array $data
     */
    public function __construct(
        \M2E\TikTokShop\Helper\Client $clientHelper,
        \M2E\TikTokShop\Helper\Data $dataHelper,
        \M2E\TikTokShop\Helper\Module $moduleHelper,
        \M2E\TikTokShop\Helper\Module\License $licenseHelper,
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Template $context,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->clientHelper = $clientHelper;
        $this->dataHelper = $dataHelper;
        $this->moduleHelper = $moduleHelper;
        $this->licenseHelper = $licenseHelper;
    }

    public function _construct()
    {
        parent::_construct();

        $this->setId('controlPanelInfoLicense');
        $this->setTemplate('control_panel/info/license.phtml');
    }

    // ----------------------------------------

    protected function _beforeToHtml()
    {
        $this->licenseData = [
            'key' => \M2E\TikTokShop\Helper\Data::escapeHtml($this->licenseHelper->getKey()),
            'domain' => \M2E\TikTokShop\Helper\Data::escapeHtml($this->licenseHelper->getDomain()),
            'ip' => \M2E\TikTokShop\Helper\Data::escapeHtml($this->licenseHelper->getIp()),
            'valid' => [
                'domain' => $this->licenseHelper->isValidDomain(),
                'ip' => $this->licenseHelper->isValidIp(),
            ],
        ];

        $this->locationData = [
            'domain' => $this->clientHelper->getDomain(),
            'ip' => $this->clientHelper->getIp(),
            'directory' => $this->clientHelper->getBaseDirectory(),
            'relative_directory' => $this->moduleHelper->getBaseRelativeDirectory(),
        ];

        return parent::_beforeToHtml();
    }
}
