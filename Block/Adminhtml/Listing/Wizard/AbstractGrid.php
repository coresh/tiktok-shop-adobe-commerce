<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Block\Adminhtml\Listing\Wizard;

use M2E\TikTokShop\Model\ResourceModel\Listing\Wizard as WizardResource;
use M2E\TikTokShop\Model\ResourceModel\Listing\Wizard\Product as WiardProductResource;
use M2E\TikTokShop\Model\ResourceModel\Product as ProductResource;
use M2E\TikTokShop\Model\ResourceModel\Listing as ListingResource;

abstract class AbstractGrid extends \M2E\TikTokShop\Block\Adminhtml\Magento\Product\Grid
{
    protected \M2E\TikTokShop\Helper\Magento\Product $magentoProductHelper;
    protected \M2E\TikTokShop\Model\ResourceModel\Magento\Product\CollectionFactory $magentoProductCollectionFactory;
    protected \Magento\Catalog\Model\Product\Type $type;
    private \M2E\TikTokShop\Model\ResourceModel\Listing\Wizard $wizardResource;
    private \M2E\TikTokShop\Model\ResourceModel\Listing\Wizard\Product $listingWizardProductResource;
    private \M2E\TikTokShop\Model\Listing\Ui\RuntimeStorage $uiListingRuntimeStorage;
    private \M2E\TikTokShop\Model\Listing\Wizard\Ui\RuntimeStorage $uiWizardRuntimeStorage;
    private \M2E\TikTokShop\Model\ResourceModel\Product $productResource;
    private \M2E\TikTokShop\Model\ResourceModel\Listing $listingResource;

    public function __construct(
        \M2E\TikTokShop\Model\ResourceModel\Listing $listingResource,
        \M2E\TikTokShop\Model\ResourceModel\Product $productResource,
        \M2E\TikTokShop\Model\Listing\Wizard\Ui\RuntimeStorage $uiWizardRuntimeStorage,
        \M2E\TikTokShop\Model\Listing\Ui\RuntimeStorage $uiListingRuntimeStorage,
        \M2E\TikTokShop\Model\ResourceModel\Listing\Wizard $wizardResource,
        \M2E\TikTokShop\Model\ResourceModel\Listing\Wizard\Product $listingWizardProductResource,
        \M2E\TikTokShop\Model\ResourceModel\Magento\Product\CollectionFactory $magentoProductCollectionFactory,
        \Magento\Catalog\Model\Product\Type $type,
        \M2E\TikTokShop\Helper\Magento\Product $magentoProductHelper,
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \M2E\TikTokShop\Helper\Data $dataHelper,
        \M2E\TikTokShop\Helper\Data\GlobalData $globalDataHelper,
        \M2E\TikTokShop\Helper\Data\Session $sessionHelper,
        array $data = []
    ) {
        $this->magentoProductCollectionFactory = $magentoProductCollectionFactory;
        $this->type = $type;
        $this->magentoProductHelper = $magentoProductHelper;
        $this->wizardResource = $wizardResource;
        $this->listingWizardProductResource = $listingWizardProductResource;
        $this->uiListingRuntimeStorage = $uiListingRuntimeStorage;
        $this->uiWizardRuntimeStorage = $uiWizardRuntimeStorage;
        $this->productResource = $productResource;
        $this->listingResource = $listingResource;

        parent::__construct(
            $globalDataHelper,
            $sessionHelper,
            $context,
            $backendHelper,
            $dataHelper,
            $data
        );
    }

    public function _construct()
    {
        parent::_construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('ttsListingProductGrid' . $this->getListing()->getId());
        // ---------------------------------------

        $this->hideMassactionDropDown = true;
        $this->showAdvancedFilterProductsOption = false;
    }

    protected function _prepareCollection()
    {
        $collection = $this->magentoProductCollectionFactory->create();
        $collection->addAttributeToSelect('sku');
        $collection->addAttributeToSelect('name');
        $collection->addAttributeToSelect('type_id');

        $collection->setStoreId($this->getListing()->getStoreId());
        $collection->joinStockItem();

        // ---------------------------------------
        $collection->getSelect()->distinct();
        // ---------------------------------------

        // Set filter store
        // ---------------------------------------
        $store = $this->_getStore();

        if ($store->getId()) {
            $collection->joinAttribute(
                'price',
                'catalog_product/price',
                'entity_id',
                null,
                'left',
                $store->getId(),
            );
            $collection->joinAttribute(
                'status',
                'catalog_product/status',
                'entity_id',
                null,
                'inner',
                $store->getId(),
            );
            $collection->joinAttribute(
                'visibility',
                'catalog_product/visibility',
                'entity_id',
                null,
                'inner',
                $store->getId(),
            );
            $collection->joinAttribute(
                'thumbnail',
                'catalog_product/thumbnail',
                'entity_id',
                null,
                'left',
                $store->getId(),
            );
        } else {
            $collection->addAttributeToSelect('price');
            $collection->addAttributeToSelect('status');
            $collection->addAttributeToSelect('visibility');
            $collection->addAttributeToSelect('thumbnail');
        }

        // ---------------------------------------
        $hideProductsFromOthersListings = true;
        if ($this->getRequest()->has('show_products_others_listings')) {
            $hideProductsFromOthersListings = false;
        }

        $collection = $this->skipAddedProductsInWizard($collection);
        $collection = $this->skipProductsInListing($collection);

        if ($hideProductsFromOthersListings) {
            $collection = $this->skipProductsFromOthersListings($collection);
        }

        $collection->addFieldToFilter(
            [
                [
                    'attribute' => 'type_id',
                    'in' => [
                        \M2E\TikTokShop\Helper\Magento\Product::TYPE_SIMPLE,
                        \M2E\TikTokShop\Helper\Magento\Product::TYPE_CONFIGURABLE,
                    ],
                ],
            ],
        );

        $this->setCollection($collection);

        $this->getCollection()->addWebsiteNamesToResult();

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('product_id', [
            'header' => __('ID'),
            'align' => 'right',
            'width' => '100px',
            'type' => 'number',
            'index' => 'entity_id',
            'filter_index' => 'entity_id',
            'store_id' => $this->getListing()->getStoreId(),
            'renderer' => \M2E\TikTokShop\Block\Adminhtml\Magento\Grid\Column\Renderer\ProductId::class,
        ]);

        $this->addColumn('name', [
            'header' => __('Title'),
            'align' => 'left',
            'type' => 'text',
            'index' => 'name',
            'filter_index' => 'name',
            'escape' => false,
            'frame_callback' => [$this, 'callbackColumnProductTitle'],
        ]);

        $this->addColumn('type', [
            'header' => __('Type'),
            'align' => 'left',
            'width' => '90px',
            'type' => 'options',
            'sortable' => false,
            'index' => 'type_id',
            'filter_index' => 'type_id',
            'options' => $this->getProductTypes(),
        ]);

        $this->addColumn('is_in_stock', [
            'header' => __('Stock Availability'),
            'align' => 'left',
            'width' => '90px',
            'type' => 'options',
            'sortable' => false,
            'index' => 'is_in_stock',
            'filter_index' => 'is_in_stock',
            'options' => [
                '1' => __('In Stock'),
                '0' => __('Out of Stock'),
            ],
            'frame_callback' => [$this, 'callbackColumnIsInStock'],
        ]);

        $this->addColumn('sku', [
            'header' => __('SKU'),
            'align' => 'left',
            'width' => '90px',
            'type' => 'text',
            'index' => 'sku',
            'filter_index' => 'sku',
        ]);

        $store = $this->_getStore();

        $this->addColumn('price', [
            'header' => __('Price'),
            'align' => 'right',
            'width' => '100px',
            'type' => 'price',
            'filter' => \M2E\TikTokShop\Block\Adminhtml\Magento\Grid\Column\Filter\Price::class,
            'currency_code' => $store->getBaseCurrency()->getCode(),
            'index' => 'price',
            'filter_index' => 'price',
            'frame_callback' => [$this, 'callbackColumnPrice'],
        ]);

        $this->addColumn('qty', [
            'header' => __('QTY'),
            'align' => 'right',
            'width' => '100px',
            'type' => 'number',
            'index' => 'qty',
            'filter_index' => 'qty',
            'frame_callback' => [$this, 'callbackColumnQty'],
        ]);

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');

        return parent::_prepareMassaction();
    }

    protected function _addColumnFilterToCollection($column)
    {
        if ($this->getCollection()) {
            if ($column->getId() === 'websites') {
                $this->getCollection()->joinField(
                    'websites',
                    'catalog_product_website',
                    'website_id',
                    'product_id=entity_id',
                    null,
                    'left',
                );
            }
        }

        return parent::_addColumnFilterToCollection($column);
    }

    protected function _getStore(): \Magento\Store\Model\Store
    {
        return $this->_storeManager->getStore($this->getListing()->getStoreId());
    }

    abstract protected function getSelectedProductsCallback();

    protected function _toHtml()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->js->add(
                <<<JS
            require([
                'TikTokShop/General/PhpFunctions',
            ], function(){

                wait(function() {
                    return typeof ProductGridObj != 'undefined';
                }, function() {
                  return ProductGridObj.massactionMassSelectStyleFix();
                }, 20);
            });
JS,
            );

            return parent::_toHtml();
        }

        // ---------------------------------------

        $this->jsUrl->add(
            $this->getUrl('*/tiktokshop_listing_product_add', ['_current' => true, 'step' => null]),
            'tiktokshop_listing_product_add',
        );

        $this->jsUrl->add(
            $this->getUrl(
                '*/listing_wizard_product/add',
                ['id' => $this->uiWizardRuntimeStorage->getManager()->getWizardId()],
            ),
            'listing_wizard_product_add_with_id',
        );

        $this->jsUrl->add(
            $this->getUrl(
                '*/listing_wizard_product/completeStep',
                ['id' => $this->uiWizardRuntimeStorage->getManager()->getWizardId()],
            ),
            'listing_wizard_product_complete_with_id',
        );
        // ---------------------------------------

        // ---------------------------------------
        $this->jsTranslator->addTranslations([
            'Category Settings' => __('Category Settings'),
            'Specifics' => __('Specifics'),
            'Based on Magento Categories' => __('Based on Magento Categories'),
            'You must select at least 1 Category.' =>
                __('You must select at least 1 Category.'),
            'Rule with the same Title already exists.' =>
                __('Rule with the same Title already exists.'),
            'Listing Settings Customization' => __('Listing Settings Customization'),
        ]);

        // ---------------------------------------

        $this->js->add(
            <<<JS
    require([
        'TikTokShop/Listing/Wizard/Product/Add',
        'TikTokShop/Plugin/AreaWrapper',
        'TikTokShop/Plugin/ProgressBar'
    ], function(){

        window.WrapperObj = new AreaWrapper('add_products_container');
        window.ProgressBarObj = new ProgressBar('add_products_progress_bar');

        window.ListingProductAdd = new ListingWizardProductAdd({
            get_selected_products: {$this->getSelectedProductsCallback()}
        })

        wait(function() {
            return typeof ProductGridObj != 'undefined';
        }, function() {
          return ProductGridObj.massactionMassSelectStyleFix();
        }, 20);
    });
JS,
        );

        return parent::_toHtml();
    }

    private function getProductTypes(): array
    {
        $magentoProductTypes = $this->type->getOptionArray();
        $knownTypes = [
            \M2E\TikTokShop\Helper\Magento\Product::TYPE_SIMPLE,
            \M2E\TikTokShop\Helper\Magento\Product::TYPE_CONFIGURABLE,
        ];

        foreach ($magentoProductTypes as $type => $magentoProductTypeLabel) {
            if (in_array($type, $knownTypes)) {
                continue;
            }

            unset($magentoProductTypes[$type]);
        }

        return $magentoProductTypes;
    }

    private function getListing(): \M2E\TikTokShop\Model\Listing
    {
        return $this->uiListingRuntimeStorage->getListing();
    }

    private function skipAddedProductsInWizard(
        \M2E\TikTokShop\Model\ResourceModel\Magento\Product\Collection $collection
    ): \M2E\TikTokShop\Model\ResourceModel\Magento\Product\Collection {
        $dbExcludeSelect = $collection->getConnection()->select();
        $dbExcludeSelect->distinct();
        $dbExcludeSelect->from(
            $this->listingWizardProductResource->getMainTable(),
            [WiardProductResource::COLUMN_MAGENTO_PRODUCT_ID]
        );

        $dbExcludeSelect->join(
            ['wizard' => $this->wizardResource->getMainTable()],
            sprintf(
                '`wizard`.`%s` = `%s`',
                WizardResource::COLUMN_ID,
                WiardProductResource::COLUMN_WIZARD_ID
            ),
            null,
        );

        $dbExcludeSelect->where(
            sprintf('`wizard`.`%s` = ?', WizardResource::COLUMN_ID),
            $this->uiWizardRuntimeStorage->getManager()->getWizardId()
        );

        $collection->getSelect()->where('e.entity_id NOT IN (?)', $dbExcludeSelect);

        return $collection;
    }

    private function skipProductsInListing(
        \M2E\TikTokShop\Model\ResourceModel\Magento\Product\Collection $collection
    ): \M2E\TikTokShop\Model\ResourceModel\Magento\Product\Collection {
        $productIdsInListingQuery = $collection->getConnection()->select();
        $productIdsInListingQuery->from(
            $this->productResource->getMainTable(),
            [ProductResource::COLUMN_MAGENTO_PRODUCT_ID]
        );
        $productIdsInListingQuery->distinct();
        $productIdsInListingQuery->where(
            sprintf('`%s` = ?', ProductResource::COLUMN_LISTING_ID),
            $this->getListing()->getId()
        );

        $collection->getSelect()->where('e.entity_id NOT IN (?)', $productIdsInListingQuery);

        return $collection;
    }

    private function skipProductsFromOthersListings(
        \M2E\TikTokShop\Model\ResourceModel\Magento\Product\Collection $collection
    ): \M2E\TikTokShop\Model\ResourceModel\Magento\Product\Collection {
        $productsInListingQuery = $collection->getConnection()->select();
        $productsInListingQuery->from(
            $this->productResource->getMainTable(),
            [ProductResource::COLUMN_MAGENTO_PRODUCT_ID]
        );
        $productsInListingQuery->distinct();
        $productsInListingQuery->join(
            ['listing' => $this->listingResource->getMainTable()],
            sprintf(
                '`listing`.`%s` = `%s`',
                ListingResource::COLUMN_ID,
                ProductResource::COLUMN_LISTING_ID
            ),
            null,
        );

        $productsInListingQuery->where('`listing`.`account_id` = ?', $this->getListing()->getAccountId());
        $productsInListingQuery->where('`listing`.`shop_id` = ?', $this->getListing()->getShopId());

        $collection->getSelect()
                   ->joinLeft(['sq' => $productsInListingQuery], 'sq.magento_product_id = e.entity_id', [])
                   ->where('sq.magento_product_id IS NULL');

        $collection->getSelect()->where('sq.magento_product_id IS NULL', $productsInListingQuery);

        return $collection;
    }
}
