<?php

namespace M2E\TikTokShop\Block\Adminhtml\Order\Log\Grid;

class LastActions extends \M2E\TikTokShop\Block\Adminhtml\Log\Grid\LastActions
{
    /** @var \M2E\TikTokShop\Helper\View */
    protected $viewHelper;

    public function __construct(
        \M2E\TikTokShop\Helper\View $viewHelper,
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Template $context,
        \M2E\TikTokShop\Helper\Data $dataHelper,
        array $data = []
    ) {
        parent::__construct($context, $dataHelper, $data);
        $this->viewHelper = $viewHelper;
    }

    protected function _construct()
    {
        parent::_construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('lastOrderActions');
        // ---------------------------------------
    }

    //########################################

    protected function getActions(array $logs)
    {
        $actions = $this->getGroupedActions($logs);

        $this->sortActions($actions);

        return $actions;
    }

    protected function getGroupedActions(array $logs)
    {
        $actions = [];

        foreach ($logs as $log) {
            $actions[] = [
                'type' => $log->getData('type'),
                'text' => $this->viewHelper->getModifiedLogMessage($log->getData('description')),
                'initiator' => $this->getInitiator([$log]),
                'date' => $date = $log->getData('create_date'),
                'localized_date' => $this->_localeDate->formatDate($date, \IntlDateFormatter::MEDIUM, true),
            ];
        }

        return $actions;
    }

    //########################################
}
