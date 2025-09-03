<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Block\Adminhtml\TikTokShop\Template\Category;

use M2E\TikTokShop\Model\Category\Dictionary;
use M2E\TikTokShop\Model\ResourceModel\Category\Dictionary\CollectionFactory as DictionaryCollectionFactory;

class Grid extends \M2E\TikTokShop\Block\Adminhtml\Magento\Grid\AbstractGrid
{
    private DictionaryCollectionFactory $categoryDictionaryCollectionFactory;
    private \M2E\TikTokShop\Model\ResourceModel\Shop\CollectionFactory $shopCollectionFactory;
    private \M2E\TikTokShop\Model\ResourceModel\Product $productResource;
    private \M2E\Core\Ui\AppliedFilters\Manager $appliedFiltersManager;

    public function __construct(
        DictionaryCollectionFactory $categoryDictionaryCollectionFactory,
        \M2E\TikTokShop\Model\ResourceModel\Shop\CollectionFactory $shopCollectionFactory,
        \M2E\TikTokShop\Model\ResourceModel\Product $productResource,
        \M2E\Core\Ui\AppliedFilters\Manager $appliedFiltersManager,
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = []
    ) {
        $this->categoryDictionaryCollectionFactory = $categoryDictionaryCollectionFactory;
        $this->shopCollectionFactory = $shopCollectionFactory;
        $this->productResource = $productResource;
        $this->appliedFiltersManager = $appliedFiltersManager;

        parent::__construct($context, $backendHelper, $data);
    }

    public function _construct()
    {
        parent::_construct();

        $this->setId('tikTokShopTemplateCategoryGrid');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setDefaultSort('id');
        $this->setDefaultDir('asc');
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', ['_current' => true]);
    }

    public function getRowUrl($item)
    {
        return false;
    }

    protected function _prepareCollection()
    {
        $collection = $this->categoryDictionaryCollectionFactory->create();

        $collection->addFieldToFilter(
            \M2E\TikTokShop\Model\ResourceModel\Category\Dictionary::COLUMN_STATE,
            ['neq' => Dictionary::DRAFT_STATE]
        );

        $collection->joinLeft(
            ['products' => $this->createProductCountJoinTable()],
            'template_category_id = id',
            ['product_count' => 'count']
        );

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'category_id',
            [
                'header' => __('Category ID'),
                'align' => 'center',
                'type' => 'text',
                'index' => \M2E\TikTokShop\Model\ResourceModel\Category\Dictionary::COLUMN_CATEGORY_ID,
            ]
        );

        $this->addColumn(
            'path',
            [
                'header' => __('Title'),
                'align' => 'left',
                'type' => 'text',
                'escape' => true,
                'index' => \M2E\TikTokShop\Model\ResourceModel\Category\Dictionary::COLUMN_PATH,
                'filter_condition_callback' => [$this, 'callbackFilterPath'],
                'frame_callback' => [$this, 'callbackColumnFilterPath'],
            ]
        );

        $this->addColumn(
            'shop_name',
            [
                'header' => __('Shop'),
                'align' => 'left',
                'type' => 'options',
                'width' => '100px',
                'index' => \M2E\TikTokShop\Model\ResourceModel\Category\Dictionary::COLUMN_SHOP_ID,
                'frame_callback' => [$this, 'callbackColumnShop'],
                'options' => $this->getShopIdOptions(),
            ]
        );

        $this->addColumn(
            'product_count',
            [
                'header' => __('Products'),
                'align' => 'center',
                'type' => 'number',
                'index' => 'product_count',
                'filter_index' => 'products.count',
                'frame_callback' => [$this, 'callbackColumnProductCount'],
            ]
        );

        $this->addColumn(
            'total_attributes',
            [
                'header' => __('Attributes: Total'),
                'align' => 'left',
                'type' => 'number',
                'index' => \M2E\TikTokShop\Model\ResourceModel\Category\Dictionary::COLUMN_TOTAL_PRODUCT_ATTRIBUTES,
                'filter' => false,
            ]
        );

        $this->addColumn(
            'used_attributes',
            [
                'header' => __('Attributes: Used'),
                'align' => 'left',
                'type' => 'number',
                'index' => \M2E\TikTokShop\Model\ResourceModel\Category\Dictionary::COLUMN_USED_PRODUCT_ATTRIBUTES,
                'filter' => false,
            ]
        );

        $this->addColumn(
            'actions',
            [
                'header' => __('Actions'),
                'align' => 'left',
                'type' => 'action',
                'filter' => false,
                'sortable' => false,
                'renderer' => \M2E\TikTokShop\Block\Adminhtml\Magento\Grid\Column\Renderer\Action::class,
                'actions' => [
                    [
                        'caption' => __('Edit'),
                        'url' => [
                            'base' => '*/tiktokshop_category/view',
                            'params' => [
                                'dictionary_id' => '$id',
                            ],
                        ],
                        'field' => 'id',
                    ],
                ],
            ]
        );

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('main_table.id');
        $this->getMassactionBlock()->setFormFieldName('ids');

        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label' => __('Remove'),
                'url' => $this->getUrl('*/tiktokshop_category/delete'),
                'confirm' => __('Are you sure?'),
            ]
        );

        return parent::_prepareMassaction();
    }

    /**
     * @param string $value
     * @param \M2E\TikTokShop\Model\Category\Dictionary $row
     * @param $column
     * @param $isExport
     *
     * @return string
     */
    public function callbackColumnFilterPath($value, $row, $column, $isExport)
    {
        if (empty($value)) {
            return '';
        }

        if (!$row->isCategoryValid()) {
            $value .= sprintf(' <span style="color: #f00;">%s</span>', __('Invalid'));
        }

        return $value;
    }

    protected function callbackFilterPath($collection, $column)
    {
        $value = $column->getFilter()->getValue();
        if ($value == null) {
            return;
        }

        $collection->getSelect()->where('main_table.path LIKE ?', '%' . $value . '%');
    }

    /**
     * @param \M2E\TikTokShop\Model\Category\Dictionary $row
     */
    public function callbackColumnShop($value, $row, $column, $isExport)
    {
        return $row->getShop()->getShopNameWithRegion();
    }

    /**
     * @param \M2E\TikTokShop\Model\Category\Dictionary $row
     */
    public function callbackColumnProductCount($value, $row, $column, $isExport): string
    {
        $appliedFiltersBuilder = new \M2E\Core\Ui\AppliedFilters\Builder();
        $appliedFiltersBuilder->addSelectFilter('product_template_category_id', [$row->getId()]);

        $url = $this->appliedFiltersManager->createUrlWithAppliedFilters(
            '*/product_grid/allItems',
            $appliedFiltersBuilder->build()
        );

        return sprintf('<a href="%s" target="_blank">%s</a>', $url, $value);
    }

    private function getShopIdOptions(): array
    {
        $collection = $this->shopCollectionFactory->create();
        $options = [];
        /** @var \M2E\TikTokShop\Model\Shop $item */
        foreach ($collection as $item) {
            $options[$item->getId()] = $item->getShopNameWithRegion();
        }

        return $options;
    }

    private function createProductCountJoinTable(): \Magento\Framework\DB\Select
    {
        return $this->productResource
            ->getConnection()
            ->select()
            ->from(
                ['temp' => $this->productResource->getMainTable()],
                [
                    'template_category_id' => $this->productResource::COLUMN_TEMPLATE_CATEGORY_ID,
                    'count' => new \Zend_Db_Expr('COUNT(*)'),
                ]
            )
            ->group($this->productResource::COLUMN_TEMPLATE_CATEGORY_ID);
    }
}
