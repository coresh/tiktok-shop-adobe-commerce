<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Block\Adminhtml\ManufacturerConfiguration;

class Grid extends \M2E\TikTokShop\Block\Adminhtml\Magento\Grid\AbstractGrid
{
    private \M2E\TikTokShop\Model\ResourceModel\ManufacturerConfiguration\CollectionFactory $productCollectionFactory;
    private \M2E\TikTokShop\Model\Account\Repository $accountRepository;

    public function __construct(
        \M2E\TikTokShop\Model\Account\Repository $accountRepository,
        \M2E\TikTokShop\Model\ResourceModel\ManufacturerConfiguration\CollectionFactory $productCollectionFactory,
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = []
    ) {
        parent::__construct($context, $backendHelper, $data);
        $this->productCollectionFactory = $productCollectionFactory;
        $this->accountRepository = $accountRepository;
    }

    public function _construct()
    {
        parent::_construct();

        $this->setId('manufacturerConfigurationGrid');
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        $collection = $this->productCollectionFactory->create();
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _beforeToHtml()
    {
        $editUrl = $this->getUrl('*/manufacturerConfiguration/edit');
        $deleteUrl = $this->getUrl('*/manufacturerConfiguration/delete');
        $gridJsName = $this->getJsObjectName();

        $js = <<<JS
require([
    'TikTokShop/ManufacturerConfiguration/Grid'
], function() {
    window.ManufacturerConfigurationGridObj = new ManufacturerConfigurationGrid({
        "editUrl": '$editUrl',
        "deleteUrl": '$deleteUrl',
        "gridJsName": '$gridJsName'
    });
});
JS;

        $this->js->add($js);
        return parent::_beforeToHtml();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('id', [
            'header' => __('ID'),
            'type' => 'number',
            'width' => '30px',
            'index' => 'id',
            'filter_index' => 'id',
        ]);

        $this->addColumn('title', [
            'header' => __('Manufacturer Title'),
            'type' => 'text',
            'index' => 'title',
            'filter_index' => 'title',
        ]);

        $this->addColumn('account_id', [
            'header' => __('Account'),
            'type' => 'options',
            'index' => 'account_id',
            'filter_index' => 'account_id',
            'frame_callback' => [$this, 'callbackColumnAccount'],
            'options' => $this->getEnabledAccountTitles(),
        ]);

        $this->addColumn('action', [
            'header' => __('Action'),
            'filter' => false,
            'sortable' => false,
            'align' => 'left',
            'type' => 'action',
            'renderer' => \M2E\TikTokShop\Block\Adminhtml\Magento\Grid\Column\Renderer\Action::class,
            'actions' => [
                [
                    'caption' => __('Edit'),
                    'onclick_action' => "ManufacturerConfigurationGridObj.actionEdit",
                    'field' => 'id',
                ],
                [
                    'caption' => __('Delete'),
                    'onclick_action' => "ManufacturerConfigurationGridObj.actionDelete",
                    'field' => 'id',
                ],
            ],
        ]);
    }

    public function getEmptyText()
    {
        return (string)__(
            '<div class="tts-manufacturer-empty-text">
            Use the <strong>‘Add New’</strong> button to provide address and contact information for the Manufacturer of your products,
            along with details of the Responsible Person if needed. For the Title, use the Magento product
            Brand that corresponds to the Manufacturer.
        </div>'
        );
    }

    public function callbackColumnAccount($value, $row, $column, $isExport)
    {
        if (empty($value)) {
            return __('Any');
        }

        return $value;
    }

    public function getGridUrl(): string
    {
        return $this->getUrl('*/manufacturerConfiguration/index');
    }

    public function getRowUrl($item)
    {
        return $this->getUrl('*/manufacturerConfiguration/edit', ['id' => $item->getId()]);
    }

    public function getRowClickCallback()
    {
        return <<<JS
function (grid, event) {
    const element = Event.findElement(event, 'tr');

    if (['a', 'input', 'select', 'option'].indexOf(Event.element(event).tagName.toLowerCase()) !== -1) {
        return;
    }

    const url = element.title;
    if (!url) {
        return;
    }

    var win = window.open(url);
    win.focus();

    var intervalId = setInterval(function () {

        if (!win.closed) {
            return;
        }

        clearInterval(intervalId);
        grid.reload();

    }, 1000);

    return win;
}

JS;
    }

    private function getEnabledAccountTitles(): array
    {
        $result = [];
        foreach ($this->accountRepository->getAll() as $account) {
            if (!$account->hasAnyEuShop()) {
                continue;
            }

            $result[$account->getId()] = $account->getTitle();
        }

        return $result;
    }
}
