<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Block\Adminhtml\Settings;

class Tabs extends \M2E\TikTokShop\Block\Adminhtml\Magento\Tabs\AbstractTabs
{
    public const TAB_ID_MAIN = 'main';
    public const TAB_ID_GPSR = 'gpsr';

    private \M2E\TikTokShop\Model\Account\Repository $accountRepository;

    public function __construct(
        \M2E\TikTokShop\Model\Account\Repository $accountRepository,
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Backend\Model\Auth\Session $authSession,
        array $data = []
    ) {
        parent::__construct($context, $jsonEncoder, $authSession, $data);
        $this->accountRepository = $accountRepository;
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setId('configuration_settings_tabs');
        $this->setDestElementId('tabs_container');
    }

    protected function _prepareLayout()
    {
        $this->css->addFile('settings.css');

        // ---------------------------------------

        $tab = [
            'label' => __('Main'),
            'title' => __('Main'),
            'content' => $this->getLayout()
                              ->createBlock(\M2E\TikTokShop\Block\Adminhtml\Settings\Tabs\Main::class)
                              ->toHtml(),
        ];
        $this->addTab(self::TAB_ID_MAIN, $tab);

        $this->addGpsrTab();

        // ---------------------------------------

        $activeTab = $this->getRequest()->getParam('activeTab', self::TAB_ID_MAIN);
        $this->setActiveTab($activeTab);

        return parent::_prepareLayout();
    }

    public function getActiveTab()
    {
        return $this->_tabs[self::TAB_ID_MAIN] ?? null;
    }

    protected function _beforeToHtml()
    {
        $this->jsUrl->add(
            $this->getUrl('*/tiktokshop/getGlobalMessages'),
            'getGlobalMessages'
        );

        $this->jsTranslator->addTranslations([
            'Settings saved' => __('Settings saved'),
            'Error' => __('Error'),
        ]);
        $this->js->addRequireJs(
            [
                's' => 'TikTokShop/Settings',
            ],
            <<<JS

        window.SettingsObj = new Settings();
JS
        );

        return parent::_beforeToHtml();
    }

    private function isNeedShowGpsrTab(): bool
    {
        foreach ($this->accountRepository->getAll() as $account) {
            if ($account->hasAnyEuShop()) {
                return true;
            }
        }

        return false;
    }

    private function addGpsrTab()
    {
        if (!$this->isNeedShowGpsrTab()) {
            return;
        }

        $titleBlock = $this
            ->getLayout()
            ->createBlock(\M2E\TikTokShop\Block\Adminhtml\ManufacturerConfiguration\Title::class);

        $gridBlock = $this
            ->getLayout()
            ->createBlock(\M2E\TikTokShop\Block\Adminhtml\ManufacturerConfiguration\Grid::class);

        $actionButtonsBlock = $this
            ->getLayout()
            ->createBlock(
                \M2E\TikTokShop\Block\Adminhtml\ManufacturerConfiguration\ActionButtons::class,
                '',
                [
                    'grid' => $gridBlock
                ]
            );

        $helpContent = __(
            '<p><strong>To comply with EU regulations</strong>, please provide the ' .
            '<strong>Manufacturer</strong> and <strong>Responsible Person</strong> details for your products.</p><br>' .
            '<ul><li>The <strong>Manufacturer</strong> is the entity that produces the product.</li>' .
            '<li>The <strong>Responsible Person</strong> is the individual or company within ' .
            'the EU responsible for ensuring product compliance and safety.</li></ul><br>' .
            '<p>On this page, you will find a list of <strong>Manufacturers already created</strong> for the ' .
            'respective %channel_title account. To add a new Manufacturer and their EU Responsible Person, ' .
            'click <strong>Create New</strong>.</p>',
            [
                'channel_title' => \M2E\TikTokShop\Helper\Module::getChannelTitle()
            ]
        );

        $helpBlock = $this
            ->getLayout()
            ->createBlock(\M2E\TikTokShop\Block\Adminhtml\HelpBlock::class)
            ->setData(['content' => $helpContent]);

        $tab = [
            'label' => __('GPSR'),
            'title' => __('GPSR'),
            'content' => $titleBlock->toHtml()
                . $helpBlock->toHtml()
                . $actionButtonsBlock->toHtml()
                . $gridBlock->toHtml(),
        ];

        $this->addTab(self::TAB_ID_GPSR, $tab);
    }
}
