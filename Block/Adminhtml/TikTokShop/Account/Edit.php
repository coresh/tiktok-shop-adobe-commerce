<?php

namespace M2E\TikTokShop\Block\Adminhtml\TikTokShop\Account;

class Edit extends \M2E\TikTokShop\Block\Adminhtml\Magento\Form\AbstractContainer
{
    private ?\M2E\TikTokShop\Model\Account $account;

    public function __construct(
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Widget $context,
        ?\M2E\TikTokShop\Model\Account $account = null,
        array $data = []
    ) {
        $this->account = $account;
        parent::__construct($context, $data);
    }

    protected function _construct()
    {
        parent::_construct();

        $this->_controller = 'adminhtml_tikTokShop_account';

        // Set buttons actions
        // ---------------------------------------
        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        $accountId = $this->getRequest()->getParam('id');

        if ($this->getRequest()->getParam('close_on_save', false)) {
            if ($accountId) {
                $this->addButton('save', [
                    'label' => __('Save And Close'),
                    'onclick' => 'TikTokShopAccountObj.saveAndClose()',
                    'class' => 'primary',
                ]);
            } else {
                $this->addButton('save_and_continue', [
                    'label' => __('Save And Continue Edit'),
                    'onclick' => 'TikTokShopAccountObj.saveAndEditClick(\'\',\'tiktokshopTabs\')',
                    'class' => 'primary',
                ]);
            }

            return;
        }

        $this->addButton('back', [
            'label' => __('Back'),
            'onclick' => 'TikTokShopAccountObj.backClick(\'' . $this->getUrl('*/tiktokshop_account/index') . '\')',
            'class' => 'back',
        ]);

        $saveButtonsProps = [];
        if ($this->account && $this->account->getId()) {
            $this->addButton('delete', [
                'label' => __('Delete'),
                'onclick' => 'TikTokShopAccountObj.deleteClick()',
                'class' => 'delete tiktokshop_delete_button primary',
            ]);

            $this->addButton('refresh', [
                'label' => __('Refresh Account Data'),
                'onclick' => 'setLocation(\'' . $this->getUrl('*/tiktokshop_account/refresh/', ['id' => $accountId]) . '\')',
                'class' => 'tiktokshop_refresh_button primary',
            ]);

            $saveButtonsProps['save'] = [
                'label' => __('Save And Back'),
                'onclick' => 'TikTokShopAccountObj.saveClick()',
                'class' => 'save primary',
            ];
        }

        // ---------------------------------------
        if (!empty($saveButtonsProps)) {
            $saveButtons = [
                'id' => 'save_and_continue',
                'label' => __('Save And Continue Edit'),
                'class' => 'add',
                'button_class' => '',
                'onclick' => 'TikTokShopAccountObj.saveAndEditClick(\'\', \'tikTokShopAccountEditTabs\')',
                'class_name' => \M2E\TikTokShop\Block\Adminhtml\Magento\Button\SplitButton::class,
                'options' => $saveButtonsProps,
            ];

            $this->addButton('save_buttons', $saveButtons);
        } else {
            $this->addButton('save_and_continue', [
                'label' => __('Save And Continue Edit'),
                'class' => 'add primary',
                'onclick' => 'TikTokShopAccountObj.saveAndEditClick(\'\')',
            ]);
        }
        // ---------------------------------------
    }

    protected function _prepareLayout()
    {
        $this->addChild('form', \M2E\TikTokShop\Block\Adminhtml\TikTokShop\Account\Edit\Form::class);

        return parent::_prepareLayout();
    }
}
