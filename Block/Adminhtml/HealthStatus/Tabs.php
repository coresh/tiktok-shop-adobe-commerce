<?php

namespace M2E\TikTokShop\Block\Adminhtml\HealthStatus;

use M2E\TikTokShop\Model\HealthStatus\Task\IssueType;
use M2E\TikTokShop\Model\HealthStatus\Task\InfoType;

class Tabs extends \M2E\TikTokShop\Block\Adminhtml\Magento\Tabs\AbstractTabs
{
    public const TAB_ID_DASHBOARD = 'dashboard';
    public const TAB_ID_NOTIFICATIONS = 'notifications';

    /** @var \M2E\TikTokShop\Model\HealthStatus\Task\Result\Set */
    private $resultSet;

    //########################################

    public function __construct(
        \M2E\TikTokShop\Model\HealthStatus\Task\Result\Set $resultSet,
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Backend\Model\Auth\Session $authSession,
        array $data = []
    ) {
        parent::__construct($context, $jsonEncoder, $authSession, $data);
        $this->resultSet = $resultSet;
    }

    //########################################

    public function _construct()
    {
        parent::_construct();

        $this->setId('healthStatus');
        $this->setDestElementId('healthStatus_tab_container');
    }

    //########################################

    protected function _prepareLayout()
    {
        $this->css->addFile('health_status.css');

        // ---------------------------------------
        $resultSet = clone $this->resultSet;
        $resultSet->fill($this->resultSet->getByType(InfoType::TYPE));

        /** @var \M2E\TikTokShop\Block\Adminhtml\HealthStatus\Tabs\Dashboard $tabObj */
        $tabObj = $this->getLayout()->createBlock(
            \M2E\TikTokShop\Block\Adminhtml\HealthStatus\Tabs\Dashboard::class,
            '',
            [
                'resultSet' => $resultSet,
            ]
        );

        $this->addTab(self::TAB_ID_DASHBOARD, [
            'label' => __('Dashboard'),
            'title' => __('Dashboard'),
            'content' => $tabObj->toHtml(),
        ]);
        // ---------------------------------------

        // -- Dynamic Tabs for Issues
        // ---------------------------------------
        $createdTabs = [];

        foreach ($this->resultSet->getByType(IssueType::TYPE) as $result) {
            if (in_array($result->getTabName(), $createdTabs)) {
                continue;
            }

            if ($result->isSuccess() && !$result->isTaskMustBeShowIfSuccess()) {
                continue;
            }

            $resultSet = clone $this->resultSet;
            $resultSet->fill(
                $this->resultSet->getByTab(
                    $this->resultSet->getTabKey($result)
                )
            );

            /** @var \M2E\TikTokShop\Block\Adminhtml\HealthStatus\Tabs\IssueGroup $tabObj */
            $tabObj = $this->getLayout()->createBlock(
                \M2E\TikTokShop\Block\Adminhtml\HealthStatus\Tabs\IssueGroup::class,
                '',
                [
                    'resultSet' => $resultSet,
                ]
            );

            $tabClass = '';
            $resultSet->isCritical() && $tabClass = 'health-status-tab-critical';
            $resultSet->isWaring() && $tabClass = 'health-status-tab-warning';
            $resultSet->isNotice() && $tabClass = 'health-status-tab-notice';

            $this->addTab('issue_tab_' . $resultSet->getTabKey($result), [
                'label' => $result->getTabName(),
                'title' => $result->getTabName(),
                'content' => $tabObj->toHtml(),
                'class' => $tabClass,
            ]);

            $createdTabs[] = $result->getTabName();
        }
        // ---------------------------------------

        // ---------------------------------------
        /** @var \M2E\TikTokShop\Block\Adminhtml\HealthStatus\Tabs\Notifications $tabObj */
        $tabObj = $this->getLayout()->createBlock(
            \M2E\TikTokShop\Block\Adminhtml\HealthStatus\Tabs\Notifications::class
        );

        $this->addTab(self::TAB_ID_NOTIFICATIONS, [
            'label' => __('Notification Settings'),
            'title' => __('Notification Settings'),
            'content' => $tabObj->toHtml(),
        ]);
        // ---------------------------------------

        $this->setActiveTab($this->getRequest()->getParam('tab', self::TAB_ID_DASHBOARD));

        return parent::_prepareLayout();
    }

    //########################################

    public function getActiveTabById($id)
    {
        return isset($this->_tabs[$id]) ? $this->_tabs[$id] : null;
    }

    //########################################
}
