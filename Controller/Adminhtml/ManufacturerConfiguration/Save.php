<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Controller\Adminhtml\ManufacturerConfiguration;

class Save extends AbstractManufacturerConfiguration
{
    private \M2E\TikTokShop\Model\ManufacturerConfiguration\CreateOrUpdateService $createOrUpdateService;

    public function __construct(
        \M2E\TikTokShop\Model\ManufacturerConfiguration\CreateOrUpdateService $createOrUpdateService,
        $context = null
    ) {
        parent::__construct($context);
        $this->createOrUpdateService = $createOrUpdateService;
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $title = (string)$this->getRequest()->getParam('title');
        $accountId = (int)$this->getRequest()->getParam('account_id');
        $manufacturerId = (string)$this->getRequest()->getParam('manufacturer_id');
        $responsiblePersonIds = (array)$this->getRequest()->getParam('responsible_person_ids');

        try {
            $this->createOrUpdateService->execute(
                $title,
                $accountId,
                $manufacturerId,
                $responsiblePersonIds,
                !empty($id) ? (int)$id : null
            );
        } catch (\Throwable $exception) {
            $this->setJsonContent([
                'status' => false,
                'message' => $exception->getMessage(),
            ]);

            return $this->getResult();
        }

        $this->setJsonContent(['status' => true]);

        return $this->getResult();
    }
}
