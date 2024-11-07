<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Block\Adminhtml\Grid\Column\Renderer;

use M2E\TikTokShop\Model\Product;

class Status extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Options
{
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
        $html = '';
        $listingProductId = (int)$row->getData('listing_product_id');

        if ($this->getColumn()->getData('showLogIcon')) {
            /** @var \M2E\TikTokShop\Block\Adminhtml\Grid\Column\Renderer\ViewLogIcon\Listing $viewLogIcon */
            $viewLogIcon = $this->getLayout()->createBlock(
                \M2E\TikTokShop\Block\Adminhtml\Grid\Column\Renderer\ViewLogIcon\Listing::class,
                '',
                [
                    'data' => ['jsHandler' => 'TikTokShopListingViewTikTokShopGridObj'],
                ]
            );
            $html = $viewLogIcon->render($row);

            $additionalData = (array)\M2E\TikTokShop\Helper\Json::decode($row->getData('additional_data'));
            $synchNote = $additionalData['synch_template_list_rules_note'] ?? [];
            if (!empty($synchNote)) {
                $synchNote = $this->viewHelper->getModifiedLogMessage($synchNote);

                if (empty($html)) {
                    $html = <<<HTML
<span class="fix-magento-tooltip m2e-tooltip-grid-warning" style="float:right;">
    {$this->getTooltipHtml($synchNote, 'map_link_error_icon_' . $row->getId())}
</span>
HTML;
                } else {
                    $html .= <<<HTML
<div id="synch_template_list_rules_note_{$listingProductId}" style="display: none">{$synchNote}</div>
HTML;
                }
            }
        }
        $html .= $this->getCurrentStatus($row);

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

        $scheduledAction = $this->scheduledActionRepository->findByListingProductId((int)$row->getData('id'));
        if ($scheduledAction === null) {
            return $html;
        }

        switch ($scheduledAction->getActionType()) {
            case Product::ACTION_LIST:
                $html .= '<br/><span style="color: #605fff">[List is Scheduled...]</span>';
                break;

            case Product::ACTION_RELIST:
                $html .= '<br/><span style="color: #605fff">[Relist is Scheduled...]</span>';
                break;

            case Product::ACTION_REVISE:
                $html .= '<br/><span style="color: #605fff">[Revise is Scheduled...]</span>';
                break;

            case Product::ACTION_STOP:
                $html .= '<br/><span style="color: #605fff">[Stop is Scheduled...]</span>';
                break;

            case Product::ACTION_DELETE:
                $html .= '<br/><span style="color: #605fff">[Delete is Scheduled...]</span>';
                break;

            default:
                break;
        }

        return $html;
    }
}
