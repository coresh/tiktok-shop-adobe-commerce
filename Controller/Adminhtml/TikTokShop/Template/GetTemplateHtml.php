<?php

namespace M2E\TikTokShop\Controller\Adminhtml\TikTokShop\Template;

use M2E\TikTokShop\Controller\Adminhtml\TikTokShop\AbstractTemplate;

class GetTemplateHtml extends AbstractTemplate
{
    private \M2E\TikTokShop\Helper\Component\TikTokShop\Template\Switcher\DataLoader $templateSwitcherDataLoader;

    public function __construct(
        \M2E\TikTokShop\Helper\Component\TikTokShop\Template\Switcher\DataLoader $templateSwitcherDataLoader,
        \M2E\TikTokShop\Model\TikTokShop\Template\Manager $templateManager
    ) {
        parent::__construct($templateManager);

        $this->templateSwitcherDataLoader = $templateSwitcherDataLoader;
    }

    public function execute()
    {
        try {
            // ---------------------------------------
            $dataLoader = $this->templateSwitcherDataLoader;
            $dataLoader->load($this->getRequest());
            // ---------------------------------------

            // ---------------------------------------
            $templateNick = $this->getRequest()->getParam('nick');
            $templateDataForce = (bool)$this->getRequest()->getParam('data_force', false);

            /** @var \M2E\TikTokShop\Block\Adminhtml\TikTokShop\Listing\Template\Switcher $switcherBlock */
            $switcherBlock = $this
                ->getLayout()
                ->createBlock(
                    \M2E\TikTokShop\Block\Adminhtml\TikTokShop\Listing\Template\Switcher::class
                );
            $switcherBlock->setData(['template_nick' => $templateNick]);
            // ---------------------------------------

            $this->setAjaxContent($switcherBlock->getFormDataBlockHtml($templateDataForce));
        } catch (\Exception $e) {
            $this->setJsonContent(['error' => $e->getMessage()]);
        }

        return $this->getResult();
    }
}
