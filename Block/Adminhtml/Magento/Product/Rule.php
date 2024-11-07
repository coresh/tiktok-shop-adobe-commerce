<?php

namespace M2E\TikTokShop\Block\Adminhtml\Magento\Product;

use M2E\TikTokShop\Block\Adminhtml\Magento\Form\AbstractForm;

class Rule extends AbstractForm
{
    private \Magento\Rule\Block\Conditions $conditions;
    private \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $rendererFieldset;

    public function __construct(
        \Magento\Rule\Block\Conditions $conditions,
        \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $rendererFieldset,
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        $this->conditions = $conditions;
        $this->rendererFieldset = $rendererFieldset;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _prepareForm()
    {
        /** @var \M2E\TikTokShop\Model\Magento\Product\Rule $model */
        $model = $this->getData('rule_model');
        $storeId = $model->getStoreId();
        $prefix = $model->getPrefix();

        $form = $this->_formFactory->create();
        $form->setHtmlId($prefix);

        $renderer = $this->rendererFieldset
            ->setTemplate('M2E_TikTokShop::magento/product/rule.phtml')
            ->setNameInLayout('M2E_TikTokShop.magento_product_rule')
            ->setNewChildUrl(
                $this->getUrl(
                    '*/general/magentoRuleGetNewConditionHtml',
                    [
                        'prefix' => $prefix,
                        'store' => $storeId,
                    ]
                )
            );

        $fieldset = $form->addFieldset($prefix, [])->setRenderer($renderer);

        $fieldset->addField($prefix . '_field', 'text', [
            'name' => 'conditions' . $prefix,
            'label' => __('Conditions'),
            'title' => __('Conditions'),
            'required' => true,
        ])->setRule($model)->setRenderer($this->conditions);

        $this->setForm($form);

        return parent::_prepareForm();
    }
}
