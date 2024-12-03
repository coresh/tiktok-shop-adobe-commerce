<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Controller\Adminhtml\Listing\Other\Moving;

class PrepareMoveToListing extends \M2E\TikTokShop\Controller\Adminhtml\AbstractListing
{
    private \M2E\TikTokShop\Helper\Data\Session $sessionHelper;
    private \M2E\TikTokShop\Helper\Module\Database\Structure $dbStructureHelper;
    private \M2E\TikTokShop\Model\UnmanagedProduct\Repository $unmanagedRepository;

    public function __construct(
        \M2E\TikTokShop\Model\UnmanagedProduct\Repository $unmanagedRepository,
        \M2E\TikTokShop\Helper\Data\Session $sessionHelper,
        \M2E\TikTokShop\Helper\Module\Database\Structure $dbStructureHelper,
        \M2E\TikTokShop\Controller\Adminhtml\Context $context
    ) {
        parent::__construct($context);

        $this->sessionHelper = $sessionHelper;
        $this->dbStructureHelper = $dbStructureHelper;
        $this->unmanagedRepository = $unmanagedRepository;
    }

    public function execute()
    {
        $isFirstPart = $this->getRequest()->getParam('is_first_part');
        $selectedProductsPart = $this->getRequest()->getParam('products_part');
        $isLastPart = $this->getRequest()->getParam('is_last_part');

        $sessionKey = \M2E\TikTokShop\Helper\View::MOVING_LISTING_OTHER_SELECTED_SESSION_KEY;

        if ($isFirstPart) {
            $this->sessionHelper->removeValue($sessionKey);
        }

        $selectedProducts = [];
        if ($sessionValue = $this->sessionHelper->getValue($sessionKey)) {
            $selectedProducts = $sessionValue;
        }

        $selectedProductsPart = explode(',', (string)$selectedProductsPart);

        $selectedProducts = array_merge($selectedProducts, $selectedProductsPart);
        $this->sessionHelper->setValue($sessionKey, $selectedProducts);

        if (!$isLastPart) {
            $this->setJsonContent(['result' => true]);

            return $this->getResult();
        }

        $unmanagedCollection = $this->unmanagedRepository->createCollection();
        $unmanagedCollection->addFieldToFilter('id', ['in' => $selectedProducts]);
        $unmanagedCollection->addFieldToFilter(
            \M2E\TikTokShop\Model\ResourceModel\UnmanagedProduct::COLUMN_MAGENTO_PRODUCT_ID,
            ['notnull' => true],
        );

        if ($unmanagedCollection->getSize() != count($selectedProducts)) {
            $this->sessionHelper->removeValue($sessionKey);

            $this->setJsonContent(
                [
                    'result' => false,
                    'message' => __('Only Linked Products must be selected.'),
                ],
            );

            return $this->getResult();
        }

        $unmanagedCollection
            ->getSelect()
            ->join(
                ['cpe' => $this->dbStructureHelper->getTableNameWithPrefix('catalog_product_entity')],
                sprintf('%s = cpe.entity_id', \M2E\TikTokShop\Model\ResourceModel\UnmanagedProduct::COLUMN_MAGENTO_PRODUCT_ID),
            );

        $row = $unmanagedCollection
            ->getSelect()
            ->group(['account_id', 'shop_id'])
            ->reset(\Magento\Framework\DB\Select::COLUMNS)
            ->columns(['account_id', 'shop_id'])
            ->query()
            ->fetch();

        if ($row !== false) {
            $response = [
                'result' => true,
                'accountId' => (int)$row['account_id'],
                'shopId' => (int)$row['shop_id'],
            ];
        } else {
            $response = [
                'result' => false,
                'message' => __('Magento product not found. Please reload the page.'),
            ];
        }

        $this->setJsonContent($response);

        return $this->getResult();
    }
}
