<?php

namespace M2E\TikTokShop\Block\Adminhtml;

class General extends Magento\AbstractBlock
{
    public int $blockNoticesShow;

    /** @var string */
    protected $_template = 'general.phtml';

    public \M2E\TikTokShop\Helper\View $viewHelper;
    private \Magento\Framework\ObjectManagerInterface $objectManager;
    private \M2E\TikTokShop\Helper\Data $dataHelper;
    private \M2E\TikTokShop\Helper\Module $moduleHelper;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \M2E\TikTokShop\Helper\View $viewHelper,
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Template $context,
        \M2E\TikTokShop\Helper\Data $dataHelper,
        \M2E\TikTokShop\Helper\Module $moduleHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->objectManager = $objectManager;
        $this->viewHelper = $viewHelper;
        $this->dataHelper = $dataHelper;
        $this->moduleHelper = $moduleHelper;
    }

    protected function _prepareLayout()
    {
        if ($this->getIsAjax()) {
            return parent::_prepareLayout();
        }

        $actions = $this->dataHelper->getControllerActions(
            'General',
            [],
            !$this->moduleHelper->areImportantTablesExist()
        );

        $this->jsUrl->addUrls($actions);

        $this->css->addFile('plugin/AreaWrapper.css');
        $this->css->addFile('plugin/ProgressBar.css');
        $this->css->addFile('help_block.css');
        $this->css->addFile('style.css');
        $this->css->addFile('grid.css');

        $currentView = $this->viewHelper->getCurrentView();
        if (!empty($currentView)) {
            $this->css->addFile($currentView . '/style.css');
        }

        return parent::_prepareLayout();
    }

    protected function _beforeToHtml()
    {
        if ($this->getIsAjax()) {
            return parent::_beforeToHtml();
        }

        $this->jsUrl->addUrls([
            'tiktokshop_skin_url' => $this->getViewFileUrl('M2E_TikTokShop'),
            'general/getCreateAttributeHtmlPopup' => $this->getUrl('*/general/getCreateAttributeHtmlPopup'),
        ]);

        $areImportantTablesExist = $this
            ->objectManager
            ->get(\M2E\TikTokShop\Helper\Module::class)->areImportantTablesExist();

        $this->blockNoticesShow = $areImportantTablesExist
            ? $this->objectManager
                ->get(\M2E\TikTokShop\Helper\Module\Configuration::class)->getViewShowBlockNoticesMode()
            : 0;

        $this->jsTranslator->addTranslations([
            'Are you sure?' => __('Are you sure?'),
            'Confirmation' => __('Confirmation'),
            'Help' => __('Help'),
            'Hide Block' => __('Hide Block'),
            'Show Tips' => __('Show Tips'),
            'Hide Tips' => __('Hide Tips'),
            'Back' => __('Back'),
            'Info' => __('Info'),
            'Warning' => __('Warning'),
            'Error' => __('Error'),
            'Close' => __('Close'),
            'Success' => __('Success'),
            'None' => __('None'),
            'Add' => __('Add'),
            'Save' => __('Save'),
            'Send' => __('Send'),
            'Cancel' => __('Cancel'),
            'Reset' => __('Reset'),
            'Confirm' => __('Confirm'),
            'Submit' => __('Submit'),
            'In Progress' => __('In Progress'),
            'Product(s)' => __('Product(s)'),
            'Continue' => __('Continue'),
            'Complete' => __('Complete'),
            'Yes' => __('Yes'),
            'No' => __('No'),

            'Collapse' => __('Collapse'),
            'Expand' => __('Expand'),

            'Reset Auto Rules' => __('Reset Auto Rules'),

            'Please select the Products you want to perform the Action on.' => __(
                'Please select the Products you want to perform the Action on.'
            ),
            'Please select Items.' => __('Please select Items.'),
            'Please select Action.' => __('Please select Action.'),
            'View Full Product Log' => __('View Full Product Log'),
            'This is a required field.' => __('This is a required field.'),
            'Please enter valid UPC' => __('Please enter valid UPC'),
            'Please enter valid EAN' => __('Please enter valid EAN'),
            'Please enter valid ISBN' => __('Please enter valid ISBN'),
            'Invalid input data. Decimal value required. Example 12.05' => __(
                'Invalid input data. Decimal value required. Example 12.05'
            ),
            'Email is not valid.' => __('Email is not valid.'),

            'You should select Attribute Set first.' => __('You should select Attribute Set first.'),

            'Create a New One...' => __('Create a New One...'),
            'Creation of New Magento Attribute' => __('Creation of New Magento Attribute'),

            'You should select Store View' => __('You should select Store View'),

            'Insert Magento Attribute in %s%' => __('Insert Magento Attribute in %s%'),
            'Attribute' => __('Attribute'),
            'Insert' => __('Insert'),

            'Settings have been saved.' => __('Settings have been saved.'),
            'You must select at least one Site you will work with.' =>
                __('You must select at least one Site you will work with.'),

            'Preparing to start. Please wait ...' => __('Preparing to start. Please wait ...'),

            'Unauthorized! Please login again' => __('Unauthorized! Please login again'),

            'Reset Unmanaged Listings' => __('Reset Unmanaged Listings'),
        ]);

        return parent::_beforeToHtml();
    }
}
