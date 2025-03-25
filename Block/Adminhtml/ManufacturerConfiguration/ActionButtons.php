<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Block\Adminhtml\ManufacturerConfiguration;

class ActionButtons extends \M2E\TikTokShop\Block\Adminhtml\Magento\AbstractContainer
{
    /** @var \M2E\TikTokShop\Block\Adminhtml\ManufacturerConfiguration\Grid */
    private Grid $grid;

    public function __construct(
        \M2E\TikTokShop\Block\Adminhtml\ManufacturerConfiguration\Grid $grid,
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Widget $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->grid = $grid;
    }

    public function toHtml(): string
    {
        $gridJsObjName = $this->grid->getJsObjectName();

        $js = <<<JS
require([
    'TikTokShop/ManufacturerConfiguration/ActionButtons'
], function() {
    window.ManufacturerConfigurationActionButtonsObj = new ManufacturerConfigurationActionButtons('$gridJsObjName');
});
JS;
        $this->js->add($js);

        return sprintf(
            '<div class="page-main-actions"><div class="page-actions">%s</div></div>',
            $this->getAddNewButton()
        );
    }

    private function getAddNewButton()
    {
        $addNewButtonConfiguration = [
            'label' => __('Add new'),
            'class' => 'primary new-manufacture-configuration',
            'onclick' => sprintf(
                "ManufacturerConfigurationActionButtonsObj.addNew('%s')",
                $this->getUrl('*/manufacturerConfiguration/create')
            )
        ];

        return $this
            ->getLayout()
            ->createBlock(\M2E\TikTokShop\Block\Adminhtml\Magento\Button::class)
            ->setData($addNewButtonConfiguration)
            ->toHtml();
    }
}
