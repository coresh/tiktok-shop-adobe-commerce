<?php

namespace M2E\TikTokShop\Block\Adminhtml\Listing\Moving\FailedProducts;

class Grid extends \M2E\TikTokShop\Block\Adminhtml\Magento\Grid\AbstractGrid
{
    private $magentoProductCollectionFactory;
    /** @var \M2E\TikTokShop\Helper\Module\Configuration */
    private $moduleConfiguration;
    private \M2E\TikTokShop\Model\Magento\ProductFactory $ourMagentoProductFactory;

    public function __construct(
        \M2E\TikTokShop\Helper\Module\Configuration $moduleConfiguration,
        \M2E\TikTokShop\Model\Magento\ProductFactory $ourMagentoProductFactory,
        \M2E\TikTokShop\Model\ResourceModel\Magento\Product\CollectionFactory $magentoProductCollectionFactory,
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = []
    ) {
        $this->magentoProductCollectionFactory = $magentoProductCollectionFactory;
        $this->moduleConfiguration = $moduleConfiguration;
        $this->ourMagentoProductFactory = $ourMagentoProductFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    public function _construct()
    {
        parent::_construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('listingFailedProductsGrid');
        // ---------------------------------------

        // Set default values
        // ---------------------------------------
        $this->setDefaultSort('id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        // ---------------------------------------
    }

    protected function _prepareCollection()
    {
        $failedProducts = \M2E\Core\Helper\Json::decode($this->getRequest()->getParam('failed_products'));

        /** @var \M2E\TikTokShop\Model\ResourceModel\Magento\Product\Collection $collection */
        $collection = $this->magentoProductCollectionFactory->create()
                                                            ->addAttributeToSelect('sku')
                                                            ->addAttributeToSelect('name')
                                                            ->addAttributeToSelect('type_id');

        $collection->joinStockItem();
        $collection->addFieldToFilter('entity_id', ['in' => $failedProducts]);

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('product_id', [
            'header' => __('Product ID'),
            'align' => 'right',
            'type' => 'number',
            'width' => '100px',
            'index' => 'entity_id',
            'filter_index' => 'entity_id',
            'frame_callback' => [$this, 'callbackColumnProductId'],
        ]);

        $this->addColumn('title', [
            'header' => __('Product Title / Product SKU'),
            'align' => 'left',
            'type' => 'text',
            'width' => '200px',
            'index' => 'name',
            'filter_index' => 'name',
            'escape' => false,
            'frame_callback' => [$this, 'callbackColumnTitle'],
            'filter_condition_callback' => [$this, 'callbackFilterTitle'],
        ]);
    }

    //########################################

    public function callbackColumnProductId($productId, $product, $column, $isExport)
    {
        $url = $this->getUrl('catalog/product/edit', ['id' => $productId]);
        $withoutImageHtml = '<a href="' . $url . '" target="_blank">' . $productId . '</a>&nbsp;';

        if (!$this->moduleConfiguration->getViewShowProductsThumbnailsMode()) {
            return $withoutImageHtml;
        }

        $magentoProduct = $this->ourMagentoProductFactory->create();
        $magentoProduct->setProduct($product);

        $imageUrlResized = $magentoProduct->getThumbnailImage();
        if ($imageUrlResized === null) {
            return $withoutImageHtml;
        }

        $imageUrlResizedUrl = $imageUrlResized->getUrl();

        $imageHtml = $productId . '<div style="margin-top: 5px">' .
            '<img style="max-width: 100px; max-height: 100px;" src="' . $imageUrlResizedUrl . '" /></div>';
        $withImageHtml = str_replace('>' . $productId . '<', '>' . $imageHtml . '<', $withoutImageHtml);

        return $withImageHtml;
    }

    public function callbackColumnTitle($value, $row, $column, $isExport)
    {
        $value = '<div style="margin-left: 3px">' . \M2E\Core\Helper\Data::escapeHtml($value);

        $tempSku = $row->getData('sku');
        if ($tempSku === null) {
            $tempSku = $this->ourMagentoProductFactory->createByProductId((int)$row->getData('entity_id'))
                                                      ->getSku();
        }

        $value .= '<br/><strong>' . __('SKU') . ':</strong> ';
        $value .= \M2E\Core\Helper\Data::escapeHtml($tempSku) . '</div>';

        return $value;
    }

    public function callbackColumnType($value, $row, $column, $isExport)
    {
        return '<div style="margin-left: 3px">' . \M2E\Core\Helper\Data::escapeHtml($value) . '</div>';
    }

    protected function callbackFilterTitle($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value == null) {
            return;
        }

        $collection->addFieldToFilter(
            [
                ['attribute' => 'sku', 'like' => '%' . $value . '%'],
                ['attribute' => 'name', 'like' => '%' . $value . '%'],
            ]
        );
    }

    //########################################

    protected function _toHtml()
    {
        $this->js->add(
            <<<JS

        $$('#listingFailedProductsGrid div.grid th').each(function(el) {
            el.style.padding = '4px';
        });

        $$('#listingFailedProductsGrid div.grid td').each(function(el) {
            el.style.padding = '4px';
        });
JS
        );

        return parent::_toHtml();
    }

    //########################################

    public function getGridUrl()
    {
        return $this->getData('grid_url');
    }

    public function getRowUrl($item)
    {
        return false;
    }

    //########################################
}
