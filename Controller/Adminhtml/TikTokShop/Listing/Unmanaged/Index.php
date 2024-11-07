<?php

namespace M2E\TikTokShop\Controller\Adminhtml\TikTokShop\Listing\Unmanaged;

class Index extends \M2E\TikTokShop\Controller\Adminhtml\TikTokShop\AbstractListing
{
    use \M2E\TikTokShop\Controller\Adminhtml\Listing\Wizard\WizardTrait;

    private \M2E\TikTokShop\Model\Listing\Wizard\Repository $wizardRepository;

    public function __construct(
        \M2E\TikTokShop\Model\Listing\Wizard\Repository $wizardRepository
    ) {
        parent::__construct();

        $this->wizardRepository = $wizardRepository;
    }

    public function execute()
    {
        $wizard = $this->wizardRepository->findNotCompletedWizardByType(\M2E\TikTokShop\Model\Listing\Wizard::TYPE_UNMANAGED);

        if (null !== $wizard) {
            $this->getMessageManager()->addNoticeMessage(
                __(
                    'Please make sure you finish adding new Products before moving to the next step.',
                ),
            );

            return $this->redirectToIndex($wizard->getId());
        }

        if ($this->getRequest()->getQuery('ajax')) {
            $this->setAjaxContent(
                $this
                    ->getLayout()
                    ->createBlock(\M2E\TikTokShop\Block\Adminhtml\TikTokShop\Listing\Unmanaged\Grid::class)
            );

            return $this->getResult();
        }

        $this->addContent(
            $this
                ->getLayout()
                ->createBlock(\M2E\TikTokShop\Block\Adminhtml\TikTokShop\Listing\Unmanaged::class)
        );
        $this->getResultPage()->getConfig()->getTitle()->prepend(__('All Unmanaged Items'));
        $this->setPageHelpLink('https://docs-m2.m2epro.com/unmanaged-listings-on-m2e-tiktok-shop');

        return $this->getResult();
    }
}
