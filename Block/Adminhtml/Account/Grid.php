<?php

namespace M2E\TikTokShop\Block\Adminhtml\Account;

class Grid extends \M2E\TikTokShop\Block\Adminhtml\Magento\Grid\AbstractGrid
{
    private \M2E\TikTokShop\Helper\View $viewHelper;

    public function __construct(
        \M2E\TikTokShop\Helper\View $viewHelper,
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = []
    ) {
        $this->viewHelper = $viewHelper;
        parent::__construct($context, $backendHelper, $data);
    }

    public function _construct()
    {
        parent::_construct();

        $this->css->addFile('account/grid.css');

        // Initialize view
        // ---------------------------------------
        $view = $this->viewHelper->getCurrentView();
        // ---------------------------------------

        // Initialization block
        // ---------------------------------------
        $this->setId($view . 'AccountGrid');
        // ---------------------------------------

        // Set default values
        // ---------------------------------------
        $this->setDefaultSort('title');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        // ---------------------------------------

        $this->jsTranslator->add(
            'confirmation_account_delete',
            __(
                '<p>You are about to delete your %channel_title seller account from %extension_title. ' .
                'This will remove the account-related Listings and Products from the extension and disconnect the ' .
                'synchronization. Your listings on the channel will <b>not</b> be affected.</p>' .
                '<p>Please confirm if you would like to delete the account.</p>' .
                '<p>Note: once the account is no longer connected to your %extension_title, please ' .
                'remember to delete it from <a href="%url">M2E Accounts</a></p>',
                [
                    'url' => \M2E\Core\Helper\Module\Support::ACCOUNTS_URL,
                    'channel_title' => \M2E\TikTokShop\Helper\Module::getChannelTitle(),
                    'extension_title' => \M2E\TikTokShop\Helper\Module::getExtensionTitle()
                ]
            )
        );
    }

    protected function _prepareColumns()
    {
        $this->addColumn('create_date', [
            'header' => (string)__('Creation Date'),
            'align' => 'left',
            'width' => '150px',
            'type' => 'datetime',
            'filter' => \M2E\TikTokShop\Block\Adminhtml\Magento\Grid\Column\Filter\Datetime::class,
            'format' => \IntlDateFormatter::MEDIUM,
            'filter_time' => true,
            'index' => 'create_date',
            'filter_index' => 'main_table.create_date',
        ]);

        $this->addColumn('update_date', [
            'header' => (string)__('Update Date'),
            'align' => 'left',
            'width' => '150px',
            'type' => 'datetime',
            'filter' => \M2E\TikTokShop\Block\Adminhtml\Magento\Grid\Column\Filter\Datetime::class,
            'format' => \IntlDateFormatter::MEDIUM,
            'filter_time' => true,
            'index' => 'update_date',
            'filter_index' => 'main_table.update_date',
        ]);

        $this->addColumn('actions', [
            'header' => (string)__('Actions'),
            'align' => 'left',
            'width' => '150px',
            'type' => 'action',
            'index' => 'actions',
            'filter' => false,
            'sortable' => false,
            'getter' => 'getId',
            'renderer' => \M2E\TikTokShop\Block\Adminhtml\Magento\Grid\Column\Renderer\Action::class,
            'frame_callback' => [$this, 'callbackColumnActions'],
        ]);

        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/accountGrid', ['_current' => true]);
    }
}
