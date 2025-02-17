<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Controller\Adminhtml\Settings\License;

class Change extends \M2E\TikTokShop\Controller\Adminhtml\AbstractBase
{
    private \M2E\TikTokShop\Model\Servicing\Dispatcher $servicing;
    private \M2E\Core\Model\LicenseService $licenseService;

    public function __construct(
        \M2E\TikTokShop\Model\Servicing\Dispatcher $servicing,
        \M2E\Core\Model\LicenseService $licenseService,
        \M2E\TikTokShop\Controller\Adminhtml\Context $context
    ) {
        parent::__construct($context);
        $this->servicing = $servicing;
        $this->licenseService = $licenseService;
    }

    public function execute()
    {
        if ($this->getRequest()->isPost()) {
            $result = $this->handleUpdate();

            if ($result) {
                $this->setJsonContent([
                    'success' => true,
                    'message' => (string)__('The License Key has been updated.'),
                ]);
            } else {
                $this->setJsonContent([
                    'success' => false,
                    'message' => (string)__('You are trying to use the unknown License Key.'),
                ]);
            }

            return $this->getResult();
        }

        $this->setAjaxContent(
            $this->getLayout()->createBlock(
                \M2E\TikTokShop\Block\Adminhtml\System\Config\Sections\License\Change::class
            )
        );

        return $this->getResult();
    }

    private function handleUpdate(): bool
    {
        $post = $this->getRequest()->getPostValue();

        $key = strip_tags($post['new_license_key']);
        $this->licenseService->updateKey($key);

        try {
            $this->servicing->processTask(\M2E\TikTokShop\Model\Servicing\Task\License::NAME);
        } catch (\Throwable $e) {
            return false;
        }

        $license = $this->licenseService->get();
        if (
            !$license->hasKey()
            || !$license->getInfo()->getDomainIdentifier()->getValidValue()
            || !$license->getInfo()->getIpIdentifier()->getValidValue()
        ) {
            return false;
        }

        return true;
    }
}
