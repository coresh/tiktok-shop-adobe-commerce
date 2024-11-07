<?php

namespace M2E\TikTokShop\Block\Adminhtml\Order\UploadByUser;

class Grid extends \M2E\TikTokShop\Block\Adminhtml\Magento\Grid\AbstractGrid
{
    protected \M2E\TikTokShop\Model\ResourceModel\Collection\CustomFactory $customCollectionFactory;
    private \M2E\TikTokShop\Model\Cron\Task\Order\UploadByUser\ManagerFactory $uploadByUserManagerFactory;
    private \M2E\TikTokShop\Model\Account\Repository $accountRepository;

    public function __construct(
        \M2E\TikTokShop\Model\Account\Repository $accountRepository,
        \M2E\TikTokShop\Model\Cron\Task\Order\UploadByUser\ManagerFactory $uploadByUserManagerFactory,
        \M2E\TikTokShop\Model\ResourceModel\Collection\CustomFactory $customCollectionFactory,
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = []
    ) {
        parent::__construct($context, $backendHelper, $data);
        $this->customCollectionFactory = $customCollectionFactory;
        $this->uploadByUserManagerFactory = $uploadByUserManagerFactory;
        $this->accountRepository = $accountRepository;
    }

    public function _construct()
    {
        parent::_construct();

        $this->setId('orderUploadByUserPopupGrid');

        $this->setPagerVisibility(false);
        $this->setFilterVisibility(false);
        $this->setUseAjax(true);
    }

    //########################################

    protected function _prepareCollection()
    {
        $collection = $this->customCollectionFactory->create();

        foreach ($this->accountRepository->getAll() as $account) {
            $manager = $this->getManager($account);
            $item = new \Magento\Framework\DataObject(
                [
                    'title' => $account->getTitle(),
                    'identifier' => $manager->getIdentifier(),
                    'from_date' => $manager->getFromDate()
                        ? $manager->getFromDate()->format('Y-m-d H:i:s')
                        : null,
                    'to_date' => $manager->getToDate()
                        ? $manager->getToDate()->format('Y-m-d H:i:s')
                        : null,
                    '_manager_' => $manager,
                    '_account_' => $account,
                ]
            );
            $collection->addItem($item);
        }

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'title',
            [
                'header' => __('Title'),
                'align' => 'left',
                'width' => '300px',
                'type' => 'text',
                'sortable' => false,
                'index' => 'title',
            ]
        );

        $this->addColumn(
            'from_date',
            [
                'header' => __('From Date'),
                'align' => 'left',
                'width' => '200px',
                'index' => 'from_date',
                'sortable' => false,
                'type' => 'datetime',
                'format' => \IntlDateFormatter::MEDIUM,
                'frame_callback' => [$this, 'callbackColumnDate'],
            ]
        );

        $this->addColumn(
            'to_date',
            [
                'header' => __('To Date'),
                'align' => 'left',
                'width' => '200px',
                'index' => 'to_date',
                'type' => 'datetime',
                'sortable' => false,
                'format' => \IntlDateFormatter::MEDIUM,
                'frame_callback' => [$this, 'callbackColumnDate'],
            ]
        );

        $this->addColumn(
            'action',
            [
                'header' => __('Action'),
                'width' => '80px',
                'type' => 'text',
                'align' => 'right',
                'sortable' => false,
                'frame_callback' => [$this, 'callbackColumnAction'],
            ]
        );

        return parent::_prepareColumns();
    }

    //########################################

    public function callbackColumnDate($value, $row, $column, $isExport)
    {
        /** @var \M2E\TikTokShop\Model\Cron\Task\Order\UploadByUser\Manager $manager */
        $manager = $row['_manager_'];

        if ($manager->isEnabled()) {
            return $value;
        }

        /** @var \M2E\TikTokShop\Model\Account $account */
        $account = $row['_account_'];

        return <<<HTML
<script>

require([
    'mage/calendar'
], function () {
    jQuery('#{$account->getId()}_{$column->getIndex()}').calendar({
        showsTime: true,
        dateFormat: "yy-mm-dd",
        timeFormat: 'HH:mm:00',
        showButtonPanel: false
    })
})

</script>

<form id="{$account->getId()}_{$column->getIndex()}_form">
    <input type="text" id="{$account->getId()}_{$column->getIndex()}" name="{$account->getId()}_{$column->getIndex()}"
           class="input-text admin__control-text required-entry validate-date" />
</form>
HTML;
    }

    public function callbackColumnAction($value, $row, $column, $isExport)
    {
        /** @var \M2E\TikTokShop\Model\Cron\Task\Order\UploadByUser\Manager $manager */
        $manager = $row['_manager_'];

        /** @var \M2E\TikTokShop\Model\Account $account */
        $account = $row['_account_'];

        $data = [
            'label' => $manager->isEnabled()
                ? __('Cancel')
                : __('Reimport'),

            'onclick' => $manager->isEnabled()
                ? "UploadByUserObj.resetUpload({$account->getId()})"
                : "UploadByUserObj.configureUpload({$account->getId()})",

            'class' => 'action primary',
        ];

        $state = '';
        if ($manager->isEnabled()) {
            $inProgressText = __('(in progress)');
            $state = <<<HTML
<br/>
<span style="color: orange; font-style: italic;">$inProgressText</span>
HTML;
        }

        $button = $this->getLayout()->createBlock(\M2E\TikTokShop\Block\Adminhtml\Magento\Button::class)->setData(
            $data
        );

        return $button->toHtml() . $state;
    }

    //########################################

    protected function getManager(
        \M2E\TikTokShop\Model\Account $account
    ): \M2E\TikTokShop\Model\Cron\Task\Order\UploadByUser\Manager {
        return $this->uploadByUserManagerFactory->create($account);
    }

    public function getRowUrl($item)
    {
        return '';
    }
}
