<?php

namespace M2E\TikTokShop\Block\Adminhtml\Listing\Log;

use M2E\TikTokShop\Block\Adminhtml\Magento\AbstractBlock;

class ErrorsSummary extends AbstractBlock
{
    public array $errors = [];
    protected \Magento\Framework\App\ResourceConnection $resourceConnection;
    protected \M2E\TikTokShop\Helper\View $viewHelper;

    public function __construct(
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \M2E\TikTokShop\Helper\View $viewHelper,
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Template $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->resourceConnection = $resourceConnection;
        $this->viewHelper = $viewHelper;
    }

    public function _construct()
    {
        parent::_construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('listingLogErrorsSummary');
        // ---------------------------------------

        $this->setTemplate('listing/log/errors_summary.phtml');
    }

    protected function _beforeToHtml()
    {
        $tableName = $this->getData('table_name');
        $actionIdsString = $this->getData('action_ids');

        $countField = 'product_id';

        if ($this->getData('type_log') == 'listing') {
            $countField = 'product_id';
        } elseif ($this->getData('type_log') == 'listing_other') {
            $countField = 'listing_other_id';
        }

        $connection = $this->resourceConnection->getConnection();
        $fields = new \Zend_Db_Expr('COUNT(`' . $countField . '`) as `count_products`, `description`');
        $dbSelect = $connection->select()
                               ->from($tableName, $fields)
                               ->where('`action_id` IN (' . $actionIdsString . ')')
                               ->where('`type` = ?', \M2E\TikTokShop\Model\Log\AbstractModel::TYPE_ERROR)
                               ->group('description')
                               ->order(['count_products DESC'])
                               ->limit(100);

        $newErrors = [];
        $tempErrors = $connection->fetchAll($dbSelect);

        foreach ($tempErrors as $row) {
            $row['description'] = $this->viewHelper->getModifiedLogMessage($row['description']);
            $newErrors[] = $row;
        }

        $this->errors = $newErrors;

        return parent::_beforeToHtml();
    }

    //########################################
}
