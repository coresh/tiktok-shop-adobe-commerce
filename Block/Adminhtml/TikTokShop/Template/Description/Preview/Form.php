<?php

namespace M2E\TikTokShop\Block\Adminhtml\TikTokShop\Template\Description\Preview;

use M2E\TikTokShop\Block\Adminhtml\Magento\Form\AbstractForm;
use Magento\Framework\Message\MessageInterface;

class Form extends AbstractForm
{
    protected function _prepareForm()
    {
        $form = $this->_formFactory->create();

        $form->addField(
            'show',
            'hidden',
            [
                'name' => 'show',
                'value' => 1,
            ]
        );

        $fieldset = $form->addFieldset(
            'magento_block_template_description_form',
            [
                'legend' => __('Select Product'),
            ]
        );

        if ($errorMessage = $this->getData('error_message')) {
            $fieldset->addField(
                'messages',
                self::MESSAGES,
                [
                    'messages' => [
                        [
                            'type' => MessageInterface::TYPE_ERROR,
                            'content' => $errorMessage,
                        ],
                    ],
                ]
            );
        }

        $viewButton = $this->getLayout()->createBlock(\M2E\TikTokShop\Block\Adminhtml\Magento\Button::class);
        $viewButton->addData([
            'label' => __('View'),
            'type' => 'submit',
        ]);

        $randomButton = $this->getLayout()->createBlock(\M2E\TikTokShop\Block\Adminhtml\Magento\Button::class);
        $randomButton->addData(
            [
                'label' => __('View Random Product'),
                'type' => 'submit',
                'onclick' => '$(\'product_id\').value = \'\'; return true;',
            ]
        );

        $fieldset->addField(
            'product_id',
            'text',
            [
                'name' => 'id',
                'value' => $this->getData('product_id'),
                'label' => __('Enter Product Id'),
                'after_element_html' => $viewButton->toHtml() . __('or') . $randomButton->toHtml(),
            ]
        );

        $fieldset->addField(
            'store_id',
            self::STORE_SWITCHER,
            [
                'value' => $this->getData('store_id'),
            ]
        );

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
