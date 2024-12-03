<?php

namespace M2E\TikTokShop\Controller\Adminhtml\TikTokShop\Template;

class Save extends \M2E\TikTokShop\Controller\Adminhtml\TikTokShop\AbstractTemplate
{
    private \M2E\TikTokShop\Helper\Module\Wizard $wizardHelper;
    private \M2E\TikTokShop\Helper\Url $urlHelper;
    private \M2E\TikTokShop\Model\Template\Synchronization\SaveService $synchronizationSaveService;
    private \M2E\TikTokShop\Model\Template\Description\SaveService $descriptionSaveService;
    private \M2E\TikTokShop\Model\Template\SellingFormat\SaveService $sellingFormatSaveService;
    private \M2E\TikTokShop\Model\Template\Compliance\SaveService $complianceSaveService;

    public function __construct(
        \M2E\TikTokShop\Model\Template\SellingFormat\SaveService $sellingFormatSaveService,
        \M2E\TikTokShop\Model\Template\Synchronization\SaveService $synchronizationSaveService,
        \M2E\TikTokShop\Model\Template\Description\SaveService $descriptionSaveService,
        \M2E\TikTokShop\Model\Template\Compliance\SaveService $complianceSaveService,
        \M2E\TikTokShop\Helper\Module\Wizard $wizardHelper,
        \M2E\TikTokShop\Helper\Url $urlHelper,
        \M2E\TikTokShop\Model\TikTokShop\Template\Manager $templateManager
    ) {
        parent::__construct($templateManager);

        $this->wizardHelper = $wizardHelper;
        $this->urlHelper = $urlHelper;
        $this->synchronizationSaveService = $synchronizationSaveService;
        $this->descriptionSaveService = $descriptionSaveService;
        $this->sellingFormatSaveService = $sellingFormatSaveService;
        $this->complianceSaveService = $complianceSaveService;
    }

    public function execute()
    {
        $templates = [];
        $templateNicks = $this->templateManager->getAllTemplates();

        // ---------------------------------------
        foreach ($templateNicks as $nick) {
            if ($this->isSaveAllowed($nick)) {
                $template = $this->saveTemplate($nick);

                if ($template) {
                    $templates[] = [
                        'nick' => $nick,
                        'id' => (int)$template->getId(),
                        'title' => \M2E\TikTokShop\Helper\Data::escapeJs(
                            \M2E\TikTokShop\Helper\Data::escapeHtml($template->getTitle())
                        ),
                    ];
                }
            }
        }
        // ---------------------------------------

        // ---------------------------------------
        if ($this->isAjax()) {
            $this->setJsonContent($templates);

            return $this->getResult();
        }
        // ---------------------------------------

        if (count($templates) == 0) {
            $this->messageManager->addError(__('Policy was not saved.'));

            return $this->_redirect('*/*/index');
        }

        $template = array_shift($templates);

        $this->messageManager->addSuccess(__('Policy was saved.'));

        $extendedRoutersParams = [
            'edit' => [
                'id' => $template['id'],
                'nick' => $template['nick'],
                'close_on_save' => $this->getRequest()->getParam('close_on_save'),
            ],
        ];

        if ($this->wizardHelper->isActive(\M2E\TikTokShop\Helper\View\TikTokShop::WIZARD_INSTALLATION_NICK)) {
            $extendedRoutersParams['edit']['wizard'] = true;
        }

        return $this->_redirect(
            $this->urlHelper->getBackUrl(
                'list',
                [],
                $extendedRoutersParams
            )
        );
    }

    protected function isSaveAllowed($templateNick)
    {
        if (!$this->getRequest()->isPost()) {
            return false;
        }

        $requestedTemplateNick = $this->getRequest()->getPost('nick');

        if ($requestedTemplateNick === null) {
            return true;
        }

        if ($requestedTemplateNick == $templateNick) {
            return true;
        }

        return false;
    }

    protected function saveTemplate($nick)
    {
        $data = $this->getRequest()->getPost($nick);

        if ($data === null) {
            return null;
        }

        if ($nick === \M2E\TikTokShop\Model\TikTokShop\Template\Manager::TEMPLATE_SYNCHRONIZATION) {
            return $this->synchronizationSaveService->save($data);
        }

        if ($nick === \M2E\TikTokShop\Model\TikTokShop\Template\Manager::TEMPLATE_DESCRIPTION) {
            return $this->descriptionSaveService->save($data);
        }

        if ($nick === \M2E\TikTokShop\Model\TikTokShop\Template\Manager::TEMPLATE_SELLING_FORMAT) {
            return $this->sellingFormatSaveService->save($data);
        }

        if ($nick === \M2E\TikTokShop\Model\TikTokShop\Template\Manager::TEMPLATE_COMPLIANCE) {
            return $this->complianceSaveService->save($data);
        }

        throw new \M2E\TikTokShop\Model\Exception\Logic('Unknown nick ' . $nick);
    }
}
