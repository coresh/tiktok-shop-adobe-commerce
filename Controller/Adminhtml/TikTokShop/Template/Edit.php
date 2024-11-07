<?php

namespace M2E\TikTokShop\Controller\Adminhtml\TikTokShop\Template;

use M2E\TikTokShop\Controller\Adminhtml\TikTokShop\AbstractTemplate;

class Edit extends AbstractTemplate
{
    private \M2E\TikTokShop\Helper\Component\TikTokShop\Template\Switcher\DataLoader $dataLoader;
    private \M2E\TikTokShop\Model\Template\Synchronization\Repository $synchronizationRepository;
    private \M2E\TikTokShop\Model\Template\SynchronizationFactory $synchronizationFactory;
    private \M2E\TikTokShop\Model\Template\Description\Repository $descriptionRepository;
    private \M2E\TikTokShop\Model\Template\DescriptionFactory $descriptionFactory;
    private \M2E\TikTokShop\Model\Template\SellingFormatFactory $sellingFormatFactory;
    private \M2E\TikTokShop\Model\Template\SellingFormat\Repository $sellingFormatRepository;

    public function __construct(
        \M2E\TikTokShop\Model\Template\SellingFormatFactory $sellingFormatFactory,
        \M2E\TikTokShop\Model\Template\SellingFormat\Repository $sellingFormatRepository,
        \M2E\TikTokShop\Model\Template\DescriptionFactory $descriptionFactory,
        \M2E\TikTokShop\Model\Template\Description\Repository $descriptionRepository,
        \M2E\TikTokShop\Model\Template\Synchronization\Repository $synchronizationRepository,
        \M2E\TikTokShop\Model\Template\SynchronizationFactory $synchronizationFactory,
        \M2E\TikTokShop\Helper\Component\TikTokShop\Template\Switcher\DataLoader $dataLoader,
        \M2E\TikTokShop\Model\TikTokShop\Template\Manager $templateManager
    ) {
        parent::__construct($templateManager);

        $this->dataLoader = $dataLoader;
        $this->synchronizationRepository = $synchronizationRepository;
        $this->synchronizationFactory = $synchronizationFactory;
        $this->descriptionRepository = $descriptionRepository;
        $this->descriptionFactory = $descriptionFactory;
        $this->sellingFormatFactory = $sellingFormatFactory;
        $this->sellingFormatRepository = $sellingFormatRepository;
    }

    public function execute()
    {
        // ---------------------------------------
        $id = $this->getRequest()->getParam('id');
        $nick = $this->getRequest()->getParam('nick');
        // ---------------------------------------

        if ($nick === \M2E\TikTokShop\Model\TikTokShop\Template\Manager::TEMPLATE_SYNCHRONIZATION) {
            return $this->executeSynchronizationTemplate($id);
        }

        if ($nick === \M2E\TikTokShop\Model\TikTokShop\Template\Manager::TEMPLATE_DESCRIPTION) {
            return $this->executeDescriptionTemplate($id);
        }

        if ($nick === \M2E\TikTokShop\Model\TikTokShop\Template\Manager::TEMPLATE_SELLING_FORMAT) {
            return $this->executeSellingFormatTemplate($id);
        }

        throw new \M2E\TikTokShop\Model\Exception\Logic('Unknown nick ' . $nick);
    }

    private function executeSynchronizationTemplate($id)
    {
        $template = $this->synchronizationRepository->find((int)$id);
        if ($template === null) {
            $template = $this->synchronizationFactory->create();
        }

        if (!$template->getId() && $id) {
            $this->getMessageManager()->addError(__('Policy does not exist.'));

            return $this->_redirect('*/*/index');
        }

        $dataLoader = $this->dataLoader;
        $dataLoader->load($template);

        // ---------------------------------------

        $this->setPageHelpLink('https://docs-m2.m2epro.com/synchronization-policy-for-tiktok-shop');

        if ($template->getId()) {
            $headerText =
                __(
                    'Edit "%template_title" Synchronization Policy',
                    [
                        'template_title' => \M2E\TikTokShop\Helper\Data::escapeHtml($template->getTitle()),
                    ],
                );
        } else {
            $headerText = __('Add Synchronization Policy');
        }

        $content = $this->getLayout()->createBlock(
            \M2E\TikTokShop\Block\Adminhtml\TikTokShop\Template\Edit::class,
            '',
            [
                'data' => [
                    'template_nick' => \M2E\TikTokShop\Model\TikTokShop\Template\Manager::TEMPLATE_SYNCHRONIZATION,
                ],
            ]
        );

        $content->toHtml();

        $this->getResult()->getConfig()->getTitle()->prepend($headerText);
        $this->addContent($content);

        return $this->getResult();
    }

    private function executeDescriptionTemplate($id)
    {
        $template = $this->descriptionRepository->find((int)$id);
        if ($template === null) {
            $template = $this->descriptionFactory->create();
        }

        if (!$template->getId() && $id) {
            $this->getMessageManager()->addError(__('Policy does not exist.'));

            return $this->_redirect('*/*/index');
        }

        $dataLoader = $this->dataLoader;
        $dataLoader->load($template);

        // ---------------------------------------

        $this->setPageHelpLink('https://docs-m2.m2epro.com/description-policy-for-tiktok-shop');

        if ($template->getId()) {
            $headerText =
                __(
                    'Edit "%template_title" Description Policy',
                    [
                        'template_title' => \M2E\TikTokShop\Helper\Data::escapeHtml($template->getTitle()),
                    ],
                );
        } else {
            $headerText = __('Add Description Policy');
        }

        $content = $this->getLayout()->createBlock(
            \M2E\TikTokShop\Block\Adminhtml\TikTokShop\Template\Edit::class,
            '',
            [
                'data' => [
                    'template_nick' => \M2E\TikTokShop\Model\TikTokShop\Template\Manager::TEMPLATE_DESCRIPTION,
                ],
            ]
        );

        $content->toHtml();

        $this->getResult()->getConfig()->getTitle()->prepend($headerText);
        $this->addContent($content);

        return $this->getResult();
    }

    private function executeSellingFormatTemplate($id)
    {
        $template = $this->sellingFormatRepository->find((int)$id);
        if ($template === null) {
            $template = $this->sellingFormatFactory->create();
        }

        if (!$template->getId() && $id) {
            $this->getMessageManager()->addError(__('Policy does not exist.'));

            return $this->_redirect('*/*/index');
        }

        $dataLoader = $this->dataLoader;
        $dataLoader->load($template);

        // ---------------------------------------

        $this->setPageHelpLink('https://docs-m2.m2epro.com/synchronization-policy-for-tiktok-shop');

        if ($template->getId()) {
            $headerText =
                __(
                    'Edit "%template_title" Selling Policy',
                    [
                        'template_title' => \M2E\TikTokShop\Helper\Data::escapeHtml($template->getTitle()),
                    ],
                );
        } else {
            $headerText = __('Add Selling Policy');
        }

        $content = $this->getLayout()->createBlock(
            \M2E\TikTokShop\Block\Adminhtml\TikTokShop\Template\Edit::class,
            '',
            [
                'data' => [
                    'template_nick' => \M2E\TikTokShop\Model\TikTokShop\Template\Manager::TEMPLATE_SELLING_FORMAT,
                ],
            ]
        );

        $content->toHtml();

        $this->getResult()->getConfig()->getTitle()->prepend($headerText);
        $this->addContent($content);

        return $this->getResult();
    }
}
