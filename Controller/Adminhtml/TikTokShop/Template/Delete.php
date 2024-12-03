<?php

namespace M2E\TikTokShop\Controller\Adminhtml\TikTokShop\Template;

use M2E\TikTokShop\Controller\Adminhtml\TikTokShop\AbstractTemplate;

class Delete extends AbstractTemplate
{
    private \M2E\TikTokShop\Model\Template\SellingFormat\Repository $sellingFormatRepository;
    private \M2E\TikTokShop\Model\Template\Description\Repository $descriptionRepository;
    private \M2E\TikTokShop\Model\Template\Synchronization\Repository $synchronizationRepository;
    private \M2E\TikTokShop\Model\Template\Compliance\Repository $complianceRepository;

    public function __construct(
        \M2E\TikTokShop\Model\Template\SellingFormat\Repository $sellingFormatRepository,
        \M2E\TikTokShop\Model\Template\Description\Repository $descriptionRepository,
        \M2E\TikTokShop\Model\Template\Synchronization\Repository $synchronizationRepository,
        \M2E\TikTokShop\Model\Template\Compliance\Repository $complianceRepository,
        \M2E\TikTokShop\Model\TikTokShop\Template\Manager $templateManager
    ) {
        parent::__construct($templateManager);
        $this->sellingFormatRepository = $sellingFormatRepository;
        $this->descriptionRepository = $descriptionRepository;
        $this->synchronizationRepository = $synchronizationRepository;
        $this->complianceRepository = $complianceRepository;
    }

    public function execute()
    {
        // ---------------------------------------
        $id = $this->getRequest()->getParam('id');
        $nick = $this->getRequest()->getParam('nick');
        // ---------------------------------------

        if ($nick === \M2E\TikTokShop\Model\TikTokShop\Template\Manager::TEMPLATE_SYNCHRONIZATION) {
            return $this->deleteSynchronizationTemplate($id);
        }

        if ($nick === \M2E\TikTokShop\Model\TikTokShop\Template\Manager::TEMPLATE_DESCRIPTION) {
            return $this->deleteDescriptionTemplate($id);
        }

        if ($nick === \M2E\TikTokShop\Model\TikTokShop\Template\Manager::TEMPLATE_SELLING_FORMAT) {
            return $this->deleteSellingFormatTemplate($id);
        }

        if ($nick === \M2E\TikTokShop\Model\TikTokShop\Template\Manager::TEMPLATE_COMPLIANCE) {
            return $this->deleteComplianceTemplate($id);
        }

        throw new \M2E\TikTokShop\Model\Exception\Logic('Unknown nick ' . $nick);
    }

    private function deleteSynchronizationTemplate($id): \Magento\Framework\App\ResponseInterface
    {
        try {
            $template = $this->synchronizationRepository->get((int)$id);
        } catch (\M2E\TikTokShop\Model\Exception\Logic $exception) {
            $this->messageManager
                ->addError(__($exception->getMessage()));
            return $this->_redirect('*/*/index');
        }

        if ($template->isLocked()) {
            $this->messageManager
                ->addError(__('Policy cannot be deleted as it is used in Listing Settings.'));

            return $this->_redirect('*/*/index');
        }

        $this->synchronizationRepository->delete($template);

        $this->messageManager
                ->addSuccess(__('Policy was deleted.'));

        return $this->_redirect('*/*/index');
    }

    private function deleteDescriptionTemplate($id): \Magento\Framework\App\ResponseInterface
    {
        try {
            $template = $this->descriptionRepository->get((int)$id);
        } catch (\M2E\TikTokShop\Model\Exception\Logic $exception) {
            $this->messageManager
                ->addError(__($exception->getMessage()));
            return $this->_redirect('*/*/index');
        }

        if ($template->isLocked()) {
            $this->messageManager
                ->addError(__('Policy cannot be deleted as it is used in Listing Settings.'));

            return $this->_redirect('*/*/index');
        }

        $this->descriptionRepository->delete($template);

        $this->messageManager
            ->addSuccess(__('Policy was deleted.'));

        return $this->_redirect('*/*/index');
    }

    private function deleteSellingFormatTemplate($id)
    {
        try {
            $template = $this->sellingFormatRepository->get((int)$id);
        } catch (\M2E\TikTokShop\Model\Exception\Logic $exception) {
            $this->messageManager
                ->addError(__($exception->getMessage()));
            return $this->_redirect('*/*/index');
        }

        if ($template->isLocked()) {
            $this->messageManager
                ->addError(__('Policy cannot be deleted as it is used in Listing Settings.'));

            return $this->_redirect('*/*/index');
        }

        $this->sellingFormatRepository->delete($template);

        $this->messageManager
            ->addSuccess(__('Policy was deleted.'));

        return $this->_redirect('*/*/index');
    }

    private function deleteComplianceTemplate($id)
    {
        try {
            $template = $this->complianceRepository->get((int)$id);
        } catch (\M2E\TikTokShop\Model\Exception\Logic $exception) {
            $this->messageManager
                ->addError(__($exception->getMessage()));
            return $this->_redirect('*/*/index');
        }

        if ($template->isLocked()) {
            $this->messageManager
                ->addError(__('Policy cannot be deleted as it is used in Listing Settings.'));

            return $this->_redirect('*/*/index');
        }

        $this->complianceRepository->delete($template);

        $this->messageManager
            ->addSuccess(__('Policy was deleted.'));

        return $this->_redirect('*/*/index');
    }
}
