<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Block\Adminhtml\Grid\Column\Renderer;

use M2E\TikTokShop\Model\Product;

class Status extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Options
{
    use \M2E\TikTokShop\Block\Adminhtml\Traits\BlockTrait;

    protected string $dataKeyStatus = 'status';

    private \M2E\TikTokShop\Helper\View $viewHelper;
    private \M2E\TikTokShop\Model\ScheduledAction\Repository $scheduledActionRepository;

    public function __construct(
        \M2E\TikTokShop\Model\ScheduledAction\Repository $scheduledActionRepository,
        \M2E\TikTokShop\Helper\View $viewHelper,
        \Magento\Backend\Block\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->viewHelper = $viewHelper;
        $this->scheduledActionRepository = $scheduledActionRepository;
    }

    public function render(\Magento\Framework\DataObject $row): string
    {
        $html = '<div>';
        $html .= $this->getShowLogIcon($row);
        $html .= $this->getAuditFailedReasonsTooltip($row);
        $html .= $this->getCurrentStatus($row);
        $html .= '</div>';
        $html .= $this->getScheduledTag($row);

        return $html;
    }

    public function renderExport(\Magento\Framework\DataObject $row): string
    {
        return strip_tags($this->getCurrentStatus($row));
    }

    // ----------------------------------------

    private function getCurrentStatus(\Magento\Framework\DataObject $row): string
    {
        $html = '';

        switch ($row->getData('status')) {
            case Product::STATUS_NOT_LISTED:
                $html .= '<span style="color: gray;">' . Product::getStatusTitle(Product::STATUS_NOT_LISTED) . '</span>';
                break;

            case Product::STATUS_LISTED:
                $html .= '<span style="color: green;">' . Product::getStatusTitle(Product::STATUS_LISTED) . '</span>';
                break;

            case Product::STATUS_INACTIVE:
                $html .= '<span style="color: red;">' . Product::getStatusTitle(Product::STATUS_INACTIVE) . '</span>';
                break;

            case Product::STATUS_BLOCKED:
                $html .= '<span style="color: orange;">' . Product::getStatusTitle(Product::STATUS_BLOCKED) . '</span>';
                break;

            default:
                break;
        }

        return $html;
    }

    private function getScheduledTag(\Magento\Framework\DataObject $row): string
    {
        $html = '';

        $scheduledAction = $this
            ->scheduledActionRepository
            ->findByListingProductId((int)$row->getData('id'));
        if ($scheduledAction === null) {
            return $html;
        }

        switch ($scheduledAction->getActionType()) {
            case Product::ACTION_LIST:
                $html .= '<span style="color: #605fff">[List is Scheduled...]</span>';
                break;

            case Product::ACTION_RELIST:
                $html .= '<span style="color: #605fff">[Relist is Scheduled...]</span>';
                break;

            case Product::ACTION_REVISE:
                $html .= '<span style="color: #605fff">[Revise is Scheduled...]</span>';
                break;

            case Product::ACTION_STOP:
                $html .= '<span style="color: #605fff">[Stop is Scheduled...]</span>';
                break;

            case Product::ACTION_DELETE:
                $html .= '<span style="color: #605fff">[Delete is Scheduled...]</span>';
                break;

            default:
                break;
        }

        return "<div>$html</div>";
    }

    /**
     * @param Product $row
     *
     * @return string
     */
    private function getAuditFailedReasonsTooltip(\Magento\Framework\DataObject $row)
    {
        $auditFailedReasons = $row->getData('audit_failed_reasons');
        if (empty($auditFailedReasons)) {
            return '';
        }

        $auditFailedReasons = json_decode($auditFailedReasons, true);
        if (empty($auditFailedReasons)) {
            return '';
        }

        $reasons = [];
        foreach ($auditFailedReasons as $auditFailedReason) {
            $reasons = array_merge($reasons, $auditFailedReason['reasons']);
        }

        if (count($reasons) === 1) {
            $tooltipHtml = sprintf('<p>%s</p>', reset($reasons));
        } else {
            $reasons = array_map(static function ($reason) {
                return '<li style="list-style: disc">' . $reason . '</li>';
            }, $reasons);
            $tooltipHtml = sprintf('<ul style="margin: 0; padding: 0 0 0 20px">%s</ul>', implode($reasons));
        }

        $html = '<span class="fix-magento-tooltip m2e-tooltip-grid-warning" style="float:right;">';
        $html .= $this->getTooltipHtml($tooltipHtml);
        $html .= '</span>';

        return $html;
    }

    private function getShowLogIcon(\Magento\Framework\DataObject $row): string
    {
        if (!$this->getColumn()->getData('showLogIcon')) {
            return '';
        }

        /** @var \M2E\TikTokShop\Block\Adminhtml\Grid\Column\Renderer\ViewLogIcon\Listing $viewLogIcon */
        $viewLogIcon = $this->getLayout()->createBlock(
            \M2E\TikTokShop\Block\Adminhtml\Grid\Column\Renderer\ViewLogIcon\Listing::class,
            '',
            [
                'data' => ['jsHandler' => 'TikTokShopListingViewTikTokShopGridObj'],
            ]
        );

        return $viewLogIcon->render($row);
    }
}
