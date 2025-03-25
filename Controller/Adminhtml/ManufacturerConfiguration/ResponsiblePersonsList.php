<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Controller\Adminhtml\ManufacturerConfiguration;

class ResponsiblePersonsList extends AbstractManufacturerConfiguration
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
        $accountId = (int)$this->getRequest()->getParam('account_id');
        $account = $this->accountRepository->find($accountId);

        if ($account === null) {
            $this->setJsonContent([
                'result' => false,
                'message' => 'Account not found',
            ]);

            return $this->getResult();
        }

        $force = (bool)(int)$this->getRequest()->getParam('force', 0);

        $persons = [];
        foreach ($this->complianceService->getAllResponsiblePersons($account, $force)->getAll() as $responsiblePerson) {
            $persons[] = [
                'id' => $responsiblePerson->id,
                'title' => sprintf('%s (%s)', $responsiblePerson->name, $responsiblePerson->email),
            ];
        }

        $this->setJsonContent([
            'result' => true,
            'responsiblePersons' => $persons,
        ]);

        return $this->getResult();
    }
}
