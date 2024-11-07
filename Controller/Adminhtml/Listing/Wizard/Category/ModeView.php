<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Controller\Adminhtml\Listing\Wizard\Category;

use M2E\TikTokShop\Model\Listing\Wizard\StepDeclarationCollectionFactory;

class ModeView extends \M2E\TikTokShop\Controller\Adminhtml\Listing\Wizard\StepAbstract
{
    protected function getStepNick(): string
    {
        return StepDeclarationCollectionFactory::STEP_SELECT_CATEGORY_MODE;
    }

    protected function process(\M2E\TikTokShop\Model\Listing $listing)
    {
        $this->addContent(
            $this->getLayout()->createBlock(
                \M2E\TikTokShop\Block\Adminhtml\Listing\Wizard\CategorySelectMode::class,
            ),
        );

        $this->getResultPage()
             ->getConfig()
             ->getTitle()
             ->prepend(__('Set Your Categories'));

        $this->setPageHelpLink('https://docs-m2.m2epro.com/set-categories-for-tiktok-shop-products');

        return $this->getResult();
    }
}
