<?php

namespace M2E\TikTokShop\Block\Adminhtml\TikTokShop\Template\Category\Chooser\Tabs;

class Browse extends \M2E\TikTokShop\Block\Adminhtml\Magento\AbstractBlock
{
    public \M2E\TikTokShop\Helper\View\TikTokShop $viewHelper;
    private \M2E\TikTokShop\Helper\Module\Wizard $wizardHelper;

    public function __construct(
        \M2E\TikTokShop\Helper\View\TikTokShop $viewHelper,
        \M2E\TikTokShop\Helper\Module\Wizard $wizardHelper,
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Template $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->viewHelper = $viewHelper;
        $this->wizardHelper = $wizardHelper;
    }

    public function _construct()
    {
        parent::_construct();

        $this->setId('tikTokShopCategoryChooserCategoryBrowse');
        $this->setTemplate('tiktokshop/template/category/chooser/tabs/browse.phtml');
    }

    public function isWizardActive()
    {
        return $this->wizardHelper->isActive(\M2E\TikTokShop\Helper\View\TikTokShop::WIZARD_INSTALLATION_NICK);
    }

    public function getInviteOnlyNotice()
    {
        $message = (string)__('This category is invite-only. Please reach out to your account manager or ' .
            'contact TikTok Shop support for permission to access this category, or select another category ' .
            'that is available.');

        $noticeHtml = $this
            ->getLayout()
            ->createBlock(\Magento\Framework\View\Element\Messages::class)
            ->addNotice($message)
            ->toHtml();

        return sprintf('<div class="invite-only-notification" style="display: none">%s</div>', $noticeHtml);
    }
}
