<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Controller\Adminhtml\TikTokShop\Listing;

class View extends \M2E\TikTokShop\Controller\Adminhtml\TikTokShop\AbstractListing
{
    private \M2E\TikTokShop\Helper\Data\GlobalData $globalData;
    private \M2E\TikTokShop\Helper\Data\Session $sessionHelper;
    private \M2E\TikTokShop\Model\Listing\Repository $listingRepository;
    private \M2E\TikTokShop\Model\TikTokShop\Magento\Product\RuleFactory $ruleFactory;
    private \M2E\TikTokShop\Model\Listing\Ui\RuntimeStorage $uiListingRuntimeStorage;
    private \M2E\TikTokShop\Model\Listing\Wizard\Repository $wizardRepository;

    public function __construct(
        \M2E\TikTokShop\Model\Listing\Repository $listingRepository,
        \M2E\TikTokShop\Helper\Data\GlobalData $globalData,
        \M2E\TikTokShop\Helper\Data\Session $sessionHelper,
        \M2E\TikTokShop\Model\TikTokShop\Magento\Product\RuleFactory $ruleFactory,
        \M2E\TikTokShop\Model\Listing\Ui\RuntimeStorage $uiListingRuntimeStorage,
        \M2E\TikTokShop\Model\Listing\Wizard\Repository $wizardRepository
    ) {
        parent::__construct();

        $this->globalData = $globalData;
        $this->sessionHelper = $sessionHelper;
        $this->listingRepository = $listingRepository;
        $this->ruleFactory = $ruleFactory;
        $this->uiListingRuntimeStorage = $uiListingRuntimeStorage;
        $this->wizardRepository = $wizardRepository;
    }

    public function execute()
    {
        $id = (int)$this->getRequest()->getParam('id');

        $listing = $this->listingRepository->get($id);
        $this->uiListingRuntimeStorage->setListing($listing);

        if ($this->getRequest()->getQuery('ajax')) {
            // Set rule model
            // ---------------------------------------
            $this->setRuleData('tiktokshop_rule_view_listing', $listing);
            // ---------------------------------------

            $this->setAjaxContent(
                $this->getLayout()
                     ->createBlock(
                         \M2E\TikTokShop\Block\Adminhtml\TikTokShop\Listing\View::class,
                     )
                     ->getGridHtml(),
            );

            return $this->getResult();
        }

        if ((bool)$this->getRequest()->getParam('do_list', false)) {
            $this->sessionHelper->setValue(
                'products_ids_for_list',
                implode(',', $this->sessionHelper->getValue('added_products_ids')),
            );

            return $this->_redirect('*/*/*', [
                '_current' => true,
                'do_list' => null,
                'view_mode' => \M2E\TikTokShop\Block\Adminhtml\Listing\View\Switcher::VIEW_MODE_TIKTOKSHOP,
            ]);
        }

        $existWizard = $this->wizardRepository->findNotCompletedByListingAndType(
            $listing,
            \M2E\TikTokShop\Model\Listing\Wizard::TYPE_GENERAL
        );

        if (($existWizard !== null) && (!$existWizard->isCompleted())) {
            $this->getMessageManager()->addNoticeMessage(
                __(
                    'Please make sure you finish adding new Products before moving to the next step.',
                ),
            );

            return $this->_redirect('*/listing_wizard/index', ['id' => $existWizard->getId()]);
        }

        // Set rule model
        // ---------------------------------------
        $this->setRuleData('tiktokshop_rule_view_listing', $listing);
        // ---------------------------------------

        $this->setPageHelpLink('https://docs-m2.m2epro.com/m2e-tiktok-shop-listings');

        $this->getResultPage()
             ->getConfig()
             ->getTitle()
             ->prepend(
                 (string)__(
                     'M2E TikTok Shop Connect Listing "%listing_title"',
                     ['listing_title' => $listing->getTitle()]
                 )
             );

        $this->addContent(
            $this->getLayout()->createBlock(
                \M2E\TikTokShop\Block\Adminhtml\TikTokShop\Listing\View::class,
            ),
        );

        return $this->getResult();
    }

    protected function setRuleData(string $prefix, \M2E\TikTokShop\Model\Listing $listing): void
    {
        $storeId = $listing->getStoreId();
        $prefix .= $listing->getId();

        $this->globalData->setValue('rule_prefix', $prefix);

        $ruleModel = $this->ruleFactory->create()
                                       ->setData(
                                           [
                                               'prefix' => $prefix,
                                               'store_id' => $storeId,
                                           ],
                                       );

        $ruleParam = $this->getRequest()->getPost('rule');
        if (!empty($ruleParam)) {
            $this->sessionHelper->setValue(
                $prefix,
                $ruleModel->getSerializedFromPost($this->getRequest()->getPostValue()),
            );
        } elseif ($ruleParam !== null) {
            $this->sessionHelper->setValue($prefix, []);
        }

        $sessionRuleData = $this->sessionHelper->getValue($prefix);
        if (!empty($sessionRuleData)) {
            $ruleModel->loadFromSerialized($sessionRuleData);
        }

        $this->globalData->setValue('rule_model', $ruleModel);
    }
}
