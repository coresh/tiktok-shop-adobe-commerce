<?php

namespace M2E\TikTokShop\Block\Adminhtml\TikTokShop\Template\Category\View;

class Edit extends \M2E\TikTokShop\Block\Adminhtml\Magento\Form\AbstractContainer
{
    private \M2E\TikTokShop\Model\Category\Dictionary $dictionary;

    public function __construct(
        \M2E\TikTokShop\Model\Category\Dictionary $dictionary,
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Widget $context,
        array $data = []
    ) {
        $this->dictionary = $dictionary;
        parent::__construct($context, $data);
    }

    protected function _construct()
    {
        parent::_construct();

        $this->removeButton('save');

        $this->setId('tikTokShopConfigurationCategoryViewTabsItemSpecificsEdit');
        $this->_controller = 'adminhtml_tikTokShop_template_category_view';

        $this->_headerText = '';

        $this->updateButton(
            'reset',
            'onclick',
            'TikTokShopTemplateCategorySpecificsObj.resetSpecifics()'
        );

        $saveButtons = [
            'id' => 'save_and_continue',
            'label' => __('Save And Continue Edit'),
            'class' => 'add',
            'button_class' => '',
            'data_attribute' => [
                'mage-init' => [
                    'button' => [
                        'event' => 'save',
                        'target' => '#edit_form',
                        'eventData' => [
                            'action' => [
                                'args' => [
                                    'back' => 'edit',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'class_name' => \M2E\TikTokShop\Block\Adminhtml\Magento\Button\SplitButton::class,
            'options' => [
                'save' => [
                    'label' => __('Save And Back'),
                    'data_attribute' => [
                        'mage-init' => [
                            'button' => [
                                'event' => 'save',
                                'target' => '#edit_form',
                                'eventData' => [
                                    'action' => [
                                        'args' => [
                                            'back' => 'categories_grid',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->addButton('save_buttons', $saveButtons);

        if (!$this->dictionary->hasRecordsOfAttributes()) {
            $this->removeButton('reset');
            $this->removeButton('save_and_continue');
        }
    }

    public function getBackUrl()
    {
        return $this->getUrl('*/tiktokshop_template_category/index');
    }

    public function getDictionary(): \M2E\TikTokShop\Model\Category\Dictionary
    {
        return $this->dictionary;
    }
}
