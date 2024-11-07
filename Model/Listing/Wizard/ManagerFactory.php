<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Listing\Wizard;

class ManagerFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;
    /** @var \M2E\TikTokShop\Model\Listing\Wizard\Repository */
    private Repository $repository;
    /** @var \M2E\TikTokShop\Model\Listing\Wizard\StepDeclarationCollectionFactory */
    private StepDeclarationCollectionFactory $stepDeclarationCollectionFactory;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        Repository $repository,
        \M2E\TikTokShop\Model\Listing\Wizard\StepDeclarationCollectionFactory $stepDeclarationCollectionFactory
    ) {
        $this->objectManager = $objectManager;
        $this->repository = $repository;
        $this->stepDeclarationCollectionFactory = $stepDeclarationCollectionFactory;
    }

    public function create(\M2E\TikTokShop\Model\Listing\Wizard $wizard): Manager
    {
        $stepCollection = $this->stepDeclarationCollectionFactory->create($wizard->getType());

        return $this->objectManager->create(Manager::class, ['wizard' => $wizard, 'stepCollection' => $stepCollection]);
    }

    public function createById(int $id): Manager
    {
        return $this->create($this->repository->get($id));
    }
}
