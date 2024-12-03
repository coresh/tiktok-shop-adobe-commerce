<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Template;

use M2E\TikTokShop\Model\ResourceModel\Template\Compliance as ComplianceResource;

class Compliance extends \M2E\TikTokShop\Model\ActiveRecord\AbstractModel
{
    private \M2E\TikTokShop\Model\Account\Repository $accountRepository;
    private \M2E\TikTokShop\Model\Account $account;
    private \M2E\TikTokShop\Model\ResourceModel\Listing\CollectionFactory $listingCollectionFactory;

    public function __construct(
        \M2E\TikTokShop\Model\Account\Repository $accountRepository,
        \M2E\TikTokShop\Model\ResourceModel\Listing\CollectionFactory $listingCollectionFactory,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry
    ) {
        parent::__construct(
            $context,
            $registry
        );

        $this->listingCollectionFactory = $listingCollectionFactory;
        $this->accountRepository = $accountRepository;
    }

    public function _construct()
    {
        parent::_construct();
        $this->_init(\M2E\TikTokShop\Model\ResourceModel\Template\Compliance::class);
    }

    public function create(
        int $accountId,
        string $title,
        string $manufacturerId,
        string $responsiblePersonId
    ): self {
        $this->setData(ComplianceResource::COLUMN_ACCOUNT_ID, $accountId)
             ->setTitle($title)
            ->setManufacturerId($manufacturerId)
            ->setResponsiblePersonId($responsiblePersonId);

        return $this;
    }

    public function getTitle(): string
    {
        return (string)$this->getData(ComplianceResource::COLUMN_TITLE);
    }

    public function getAccountId(): int
    {
        return (int)$this->getData(ComplianceResource::COLUMN_ACCOUNT_ID);
    }

    public function getManufacturerId(): string
    {
        return (string)$this->getData(ComplianceResource::COLUMN_MANUFACTURER_ID);
    }

    public function getResponsiblePersonId(): string
    {
        return (string)$this->getData(ComplianceResource::COLUMN_RESPONSIBLE_PERSON_ID);
    }

    public function setTitle(string $title): self
    {
        $this->setData(ComplianceResource::COLUMN_TITLE, $title);

        return $this;
    }

    public function setManufacturerId(string $manufacturerId): self
    {
        $this->setData(ComplianceResource::COLUMN_MANUFACTURER_ID, $manufacturerId);

        return $this;
    }

    public function setResponsiblePersonId(string $responsiblePersonId): self
    {
        $this->setData(ComplianceResource::COLUMN_RESPONSIBLE_PERSON_ID, $responsiblePersonId);

        return $this;
    }

    public function getAccount(): \M2E\TikTokShop\Model\Account
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (isset($this->account)) {
            return $this->account;
        }

        return $this->account = $this->accountRepository->get($this->getAccountId());
    }

    public function isLocked(): bool
    {
        return (bool)$this
            ->listingCollectionFactory
            ->create()
            ->addFieldToFilter(
                \M2E\TikTokShop\Model\ResourceModel\Listing::COLUMN_TEMPLATE_COMPLIANCE_ID,
                $this->getId()
            )
            ->getSize();
    }
}
