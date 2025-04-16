<?php

namespace M2E\TikTokShop\Block\Adminhtml\Log\Grid;

use M2E\TikTokShop\Block\Adminhtml\Magento\AbstractBlock;
use M2E\TikTokShop\Model\Log\AbstractModel as LogModel;

abstract class LastActions extends AbstractBlock
{
    public const VIEW_LOG_LINK_SHOW = 0;
    public const VIEW_LOG_LINK_HIDE = 1;

    public const ACTIONS_COUNT = 3;
    public const PRODUCTS_LIMIT = 100;

    protected $_template = 'log/last_actions.phtml';
    protected $tip = null;
    protected $iconSrc = null;
    protected $rows = [];

    public static $actionsSortOrder = [
        LogModel::TYPE_SUCCESS => 1,
        LogModel::TYPE_ERROR => 2,
        LogModel::TYPE_WARNING => 3,
        LogModel::TYPE_INFO => 4,
    ];

    /** @var \M2E\TikTokShop\Helper\Data */
    private $dataHelper;

    public function __construct(
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Template $context,
        \M2E\TikTokShop\Helper\Data $dataHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->dataHelper = $dataHelper;
    }

    public function getTip()
    {
        return $this->tip;
    }

    public function getIconSrc()
    {
        return $this->iconSrc;
    }

    public function getEncodedRows()
    {
        return base64_encode(\M2E\Core\Helper\Json::encode($this->rows));
    }

    public function getEntityId()
    {
        if (!$this->hasData('entity_id') || !is_int($this->getData('entity_id'))) {
            throw new \M2E\TikTokShop\Model\Exception\Logic('Entity ID is not set.');
        }

        return $this->getData('entity_id');
    }

    public function getViewHelpHandler()
    {
        if (!$this->hasData('view_help_handler') || !is_string($this->getData('view_help_handler'))) {
            throw new \M2E\TikTokShop\Model\Exception\Logic('View help handler is not set.');
        }

        return $this->getData('view_help_handler');
    }

    public function getCloseHelpHandler()
    {
        if (!$this->hasData('hide_help_handler') || !is_string($this->getData('hide_help_handler'))) {
            throw new \M2E\TikTokShop\Model\Exception\Logic('Close help handler is not set.');
        }

        return $this->getData('hide_help_handler');
    }

    public function getHideViewLogLink()
    {
        if ($this->hasData('hide_view_log_link')) {
            return self::VIEW_LOG_LINK_HIDE;
        }

        return self::VIEW_LOG_LINK_SHOW;
    }

    //########################################

    protected function getInitiator(array $actionLogs)
    {
        if (empty($actionLogs)) {
            return '';
        }

        $log = reset($actionLogs);

        if (!isset($log['initiator'])) {
            return '';
        }

        switch ($log['initiator']) {
            case \M2E\Core\Helper\Data::INITIATOR_UNKNOWN:
                return '';
            case \M2E\Core\Helper\Data::INITIATOR_USER:
                return __('Manual');
            case \M2E\Core\Helper\Data::INITIATOR_EXTENSION:
                return __('Automatic');
        }

        return '';
    }

    protected function getActionTitle(array $actionLogs)
    {
        if (empty($actionLogs)) {
            return '';
        }

        $log = reset($actionLogs);

        if (!isset($log['action'])) {
            return '';
        }

        $availableActions = $this->getAvailableActions();
        $action = $log['action'];

        if (isset($availableActions[$action])) {
            return $availableActions[$action];
        }

        return '';
    }

    protected function getMainType(array $actionLogs)
    {
        $types = array_column($actionLogs, 'type');

        return empty($types) ? 0 : max($types);
    }

    protected function getMainDate(array $actionLogs)
    {
        if (count($actionLogs) > 1) {
            $row = array_reduce($actionLogs, function ($a, $b) {
                return ($a === null || strtotime($a['create_date']) < strtotime($b['create_date'])) ? $b : $a;
            });
        } else {
            $row = reset($actionLogs);
        }

        return $row['create_date'];
    }

    //----------------------------------------

    abstract protected function getActions(array $logs);

    protected function sortActionLogs(array &$actions)
    {
        $sortOrder = self::$actionsSortOrder;

        foreach ($actions as &$actionLogs) {
            usort($actionLogs['items'], function ($a, $b) use ($sortOrder) {
                return $sortOrder[$a['type']] <=> $sortOrder[$b['type']];
            });
        }
    }

    protected function sortActions(array &$actions)
    {
        usort($actions, function ($a, $b) {
            return strtotime($b['date']) <=> strtotime($a['date']);
        });
    }

    protected function getRows()
    {
        if (!$this->hasData('logs') || !is_array($this->getData('logs'))) {
            throw new \M2E\TikTokShop\Model\Exception\Logic('Logs are not set.');
        }

        $logs = $this->getData('logs');

        if (empty($logs)) {
            return [];
        }

        return $this->getActions($logs);
    }

    //----------------------------------------

    protected function getAvailableActions()
    {
        if (!$this->hasData('available_actions') || !is_array($this->getData('available_actions'))) {
            throw new \M2E\TikTokShop\Model\Exception\Logic('Available actions are not set.');
        }

        return $this->getData('available_actions');
    }

    protected function getTips()
    {
        if (!$this->hasData('tips') || !is_array($this->getData('tips'))) {
            return [
                LogModel::TYPE_SUCCESS => (string)__('Last Action was completed.'),
                LogModel::TYPE_ERROR => (string)__('Last Action was completed with error(s).'),
                LogModel::TYPE_WARNING => (string)__('Last Action was completed with warning(s).'),
                LogModel::TYPE_INFO => (string)__('Last Action was completed with info(s).'),
            ];
        }

        return $this->getData('tips');
    }

    protected function getIcons()
    {
        if (!$this->hasData('icons') || !is_array($this->getData('icons'))) {
            return [
                LogModel::TYPE_SUCCESS => 'success',
                LogModel::TYPE_ERROR => 'error',
                LogModel::TYPE_WARNING => 'warning',
                LogModel::TYPE_INFO => 'info',
            ];
        }

        return $this->getData('icons');
    }

    protected function getDefaultTip()
    {
        return __('Last Action was completed.');
    }

    protected function getTipByType($type)
    {
        foreach ($this->getTips() as $tipType => $tip) {
            if ($tipType == $type) {
                return $tip;
            }
        }

        return $this->getDefaultTip();
    }

    protected function getDefaultIcon()
    {
        return 'success';
    }

    protected function getIconByType($type)
    {
        foreach ($this->getIcons() as $iconType => $icon) {
            if ($iconType == $type) {
                return $icon;
            }
        }

        return $this->getDefaultIcon();
    }

    //----------------------------------------

    protected function _beforeToHtml()
    {
        $rows = $this->getRows();

        if (empty($rows)) {
            return parent::_beforeToHtml();
        }

        $lastActionRow = $rows[0];
        // ---------------------------------------

        // Get log icon
        // ---------------------------------------
        $icon = $this->getDefaultIcon();
        $tip = $this->getDefaultTip();

        if (isset($lastActionRow['type'])) {
            $tip = $this->getTipByType($lastActionRow['type']);
            $icon = $this->getIconByType($lastActionRow['type']);
        }

        $this->tip = \M2E\Core\Helper\Data::escapeHtml($tip);
        $this->iconSrc = $this->getViewFileUrl('M2E_TikTokShop::images/log_statuses/' . $icon . '.png');
        $this->rows = $rows;
        // ---------------------------------------

        $this->jsPhp->addConstants(
            \M2E\TikTokShop\Helper\Data::getClassConstants(\M2E\TikTokShop\Model\Log\AbstractModel::class)
        );

        // ---------------------------------------

        return parent::_beforeToHtml();
    }

    protected function _toHtml()
    {
        if (empty($this->rows)) {
            return '';
        }

        return parent::_toHtml();
    }

    //########################################
}
