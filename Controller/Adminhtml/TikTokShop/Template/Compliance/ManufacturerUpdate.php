<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Controller\Adminhtml\TikTokShop\Template\Compliance;

class ManufacturerUpdate extends \M2E\TikTokShop\Controller\Adminhtml\TikTokShop\AbstractTemplate
{
    private \M2E\TikTokShop\Model\Template\Compliance\ComplianceService $complianceService;
    private \M2E\TikTokShop\Model\Account\Repository $accountRepository;

    public function __construct(
        \M2E\TikTokShop\Model\Template\Compliance\ComplianceService $complianceService,
        \M2E\TikTokShop\Model\Account\Repository $accountRepository,
        \M2E\TikTokShop\Model\TikTokShop\Template\Manager $templateManager
    ) {
        parent::__construct($templateManager);

        $this->accountRepository = $accountRepository;
        $this->complianceService = $complianceService;
    }

    public function execute()
    {
        $post = $this->getRequest()->getPost('manufacturer');
        $accountId = (int)$post['account_id'];
        $account = $this->accountRepository->find($accountId);

        if ($account === null) {
            $this->setJsonContent([
                'result' => false,
                'messages' => ['Account Id is required'],
            ]);

            return $this->getResult();
        }

        $manufacture = new \M2E\TikTokShop\Model\Channel\Manufacturer(
            empty($post['manufacturer_id']) ? null : $post['manufacturer_id'],
            $post['name'],
            $post['registered_trade_name'],
            $post['email'],
            $post['country_code'],
            $post['local_number'],
            $post['address'],
        );

        $errors = [];
        try {
            if ($manufacture->id !== null) {
                $this->complianceService->updateManufacturer($account, $manufacture);
            } else {
                $this->complianceService->createManufacturer($account, $manufacture);
            }
        } catch (\M2E\TikTokShop\Model\Exception\Connection\UnableUpdateData $e) {
            $errors = [];
            foreach ($e->getMessageCollection()->getErrors() as $error) {
                $errors[] = $error->getText();
            }
        } catch (\M2E\TikTokShop\Model\Exception $e) {
            $errors = [$e->getMessage()];
        }

        $this->setJsonContent([
            'result' => empty($errors),
            'messages' => $errors,
        ]);

        return $this->getResult();
    }
}
