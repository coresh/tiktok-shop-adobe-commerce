<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Block\Adminhtml\ManufacturerConfiguration;

use M2E\TikTokShop\Model\ManufacturerConfiguration;

class CreateOrEdit extends \M2E\TikTokShop\Block\Adminhtml\Magento\Form\AbstractContainer
{
    private ?\M2E\TikTokShop\Model\ManufacturerConfiguration $manufacturerConfiguration;

    public function __construct(
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Widget $context,
        ?ManufacturerConfiguration $manufacturerConfiguration = null,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->manufacturerConfiguration = $manufacturerConfiguration;
    }

    protected function _construct()
    {
        parent::_construct();

        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        $this->addButton('save', [
            'label' => __('Save And Close'),
            'class' => 'primary',
            'onclick' => sprintf(
                "ManufacturerConfigurationFormActionObj.saveAndCloseClick('%s')",
                $this->getUrl('*/manufacturerConfiguration/save')
            ),
        ]);

        $this->addButton('close', [
            'label' => __('Close'),
            'class' => 'secondary',
            'onclick' => 'window.close()',
        ]);
    }

    protected function _prepareLayout()
    {
        $formBlock = $this
            ->getLayout()
            ->createBlock(
                \M2E\TikTokShop\Block\Adminhtml\ManufacturerConfiguration\Edit\Form::class,
                '',
                [
                    'manufacturerConfiguration' => $this->manufacturerConfiguration
                ]
            );

        $this->setChild('form', $formBlock);

        return parent::_prepareLayout();
    }

    protected function _beforeToHtml()
    {
        $js = <<<JS
require([
        'TikTokShop/ManufacturerConfiguration/FormAction'
        ], function() {
    window.ManufacturerConfigurationFormActionObj = new ManufacturerConfigurationFormAction();
    });
JS;

        $this->js->add($js);

        return parent::_beforeToHtml();
    }
}
