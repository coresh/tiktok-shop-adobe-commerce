<?php

namespace M2E\TikTokShop\Block\Adminhtml\System\Config\Sections\License;

class Change extends \M2E\TikTokShop\Block\Adminhtml\System\Config\Sections
{
    /** @var \M2E\TikTokShop\Helper\Module\License */
    private $licenseHelper;
    /** @var \M2E\TikTokShop\Helper\Data */
    private $dataHelper;

    /**
     * @param \M2E\TikTokShop\Helper\Module\License $licenseHelper
     * @param \M2E\TikTokShop\Helper\Data $dataHelper
     * @param \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Template $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param array $data
     */
    public function __construct(
        \M2E\TikTokShop\Helper\Module\License $licenseHelper,
        \M2E\TikTokShop\Helper\Data $dataHelper,
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);

        $this->licenseHelper = $licenseHelper;
        $this->dataHelper = $dataHelper;
    }

    protected function _prepareForm()
    {
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'edit_form',
                    'method' => 'post',
                    'action' => 'javascript:void(0)',
                ],
            ]
        );

        $fieldSet = $form->addFieldset('change_license', ['legend' => '', 'collapsable' => false]);

        $key = \M2E\TikTokShop\Helper\Data::escapeHtml($this->licenseHelper->getKey());
        $fieldSet->addField(
            'new_license_key',
            'text',
            [
                'name' => 'new_license_key',
                'label' => __('New License Key'),
                'title' => __('New License Key'),
                'value' => $key,
                'required' => true,
            ]
        );

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
