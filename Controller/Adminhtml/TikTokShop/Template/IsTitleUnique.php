<?php

namespace M2E\TikTokShop\Controller\Adminhtml\TikTokShop\Template;

use M2E\TikTokShop\Controller\Adminhtml\TikTokShop\AbstractTemplate;

class IsTitleUnique extends AbstractTemplate
{
    private \M2E\TikTokShop\Model\ResourceModel\Template\Synchronization\CollectionFactory $syncCollectionFactory;
    private \M2E\TikTokShop\Model\ResourceModel\Template\SellingFormat\CollectionFactory $sellingCollectionFactory;
    private \M2E\TikTokShop\Model\ResourceModel\Template\Description\CollectionFactory $descriptionCollectionFactory;

    public function __construct(
        \M2E\TikTokShop\Model\ResourceModel\Template\Synchronization\CollectionFactory $syncCollectionFactory,
        \M2E\TikTokShop\Model\ResourceModel\Template\SellingFormat\CollectionFactory $sellingCollectionFactory,
        \M2E\TikTokShop\Model\ResourceModel\Template\Description\CollectionFactory $descriptionCollectionFactory,
        \M2E\TikTokShop\Model\TikTokShop\Template\Manager $templateManager,
        \M2E\TikTokShop\Controller\Adminhtml\Context $context
    ) {
        parent::__construct($templateManager);
        $this->syncCollectionFactory = $syncCollectionFactory;
        $this->sellingCollectionFactory = $sellingCollectionFactory;
        $this->descriptionCollectionFactory = $descriptionCollectionFactory;
    }

    public function execute()
    {
        $nick = $this->getRequest()->getParam('nick');
        $ignoreId = $this->getRequest()->getParam('id_value');
        $title = $this->getRequest()->getParam('title');

        if ($title == '') {
            $this->setJsonContent(['unique' => false]);

            return $this->getResult();
        }

        if ($nick === \M2E\TikTokShop\Model\TikTokShop\Template\Manager::TEMPLATE_SYNCHRONIZATION) {
            return $this->isUniqueTitleSynchronizationTemplate($ignoreId, $title);
        }

        if ($nick === \M2E\TikTokShop\Model\TikTokShop\Template\Manager::TEMPLATE_DESCRIPTION) {
            return $this->isUniqueTitleDescriptionTemplate($ignoreId, $title);
        }

        if ($nick === \M2E\TikTokShop\Model\TikTokShop\Template\Manager::TEMPLATE_SELLING_FORMAT) {
            return $this->isUniqueTitleSellingFormatTemplate($ignoreId, $title);
        }

        throw new \M2E\TikTokShop\Model\Exception\Logic('Unknown nick ' . $nick);
    }

    private function isUniqueTitleSynchronizationTemplate($ignoreId, $title)
    {
        $collection = $this->syncCollectionFactory
            ->create()
            ->addFieldToFilter(
                \M2E\TikTokShop\Model\ResourceModel\Template\Synchronization::COLUMN_IS_CUSTOM_TEMPLATE,
                0
            )
            ->addFieldToFilter(
                \M2E\TikTokShop\Model\ResourceModel\Template\Synchronization::COLUMN_TITLE,
                $title
            );

        if ($ignoreId) {
            $collection->addFieldToFilter(
                \M2E\TikTokShop\Model\ResourceModel\Template\Synchronization::COLUMN_ID,
                ['neq' => $ignoreId]
            );
        }

        $this->setJsonContent(['unique' => $collection->getSize() === 0]);

        return $this->getResult();
    }

    private function isUniqueTitleDescriptionTemplate($ignoreId, $title)
    {
        $collection = $this->descriptionCollectionFactory
            ->create()
            ->addFieldToFilter(
                \M2E\TikTokShop\Model\ResourceModel\Template\Description::COLUMN_IS_CUSTOM_TEMPLATE,
                0
            )
            ->addFieldToFilter(
                \M2E\TikTokShop\Model\ResourceModel\Template\Description::COLUMN_TITLE,
                $title
            );

        if ($ignoreId) {
            $collection->addFieldToFilter(
                \M2E\TikTokShop\Model\ResourceModel\Template\Description::COLUMN_ID,
                ['neq' => $ignoreId]
            );
        }

        $this->setJsonContent(['unique' => $collection->getSize() === 0]);

        return $this->getResult();
    }

    private function isUniqueTitleSellingFormatTemplate($ignoreId, $title)
    {
        $collection = $this->sellingCollectionFactory
            ->create()
            ->addFieldToFilter(
                \M2E\TikTokShop\Model\ResourceModel\Template\SellingFormat::COLUMN_IS_CUSTOM_TEMPLATE,
                0
            )
            ->addFieldToFilter(
                \M2E\TikTokShop\Model\ResourceModel\Template\SellingFormat::COLUMN_TITLE,
                $title
            );

        if ($ignoreId) {
            $collection->addFieldToFilter(
                \M2E\TikTokShop\Model\ResourceModel\Template\SellingFormat::COLUMN_ID,
                ['neq' => $ignoreId]
            );
        }

        $this->setJsonContent(['unique' => $collection->getSize() === 0]);

        return $this->getResult();
    }
}
