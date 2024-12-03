<?php

namespace M2E\TikTokShop\Model\TikTokShop\Template;

class Manager
{
    private $ownerObject = null;
    private $templateNick = null;

    public const MODE_PARENT = 0;
    public const MODE_CUSTOM = 1;
    public const MODE_TEMPLATE = 2;

    public const COLUMN_PREFIX = 'template';

    public const TEMPLATE_DESCRIPTION = 'description';
    public const TEMPLATE_SELLING_FORMAT = 'selling_format';
    public const TEMPLATE_SYNCHRONIZATION = 'synchronization';
    public const TEMPLATE_COMPLIANCE = 'compliance';

    protected \M2E\TikTokShop\Model\ActiveRecord\Factory $activeRecordFactory;

    private \M2E\TikTokShop\Model\Template\SellingFormatFactory $sellingFormatFactory;
    private \M2E\TikTokShop\Model\Template\SynchronizationFactory $synchronizationFactory;
    private \M2E\TikTokShop\Model\Template\DescriptionFactory $descriptionFactory;
    private \M2E\TikTokShop\Model\Template\ComplianceFactory $complianceFactory;

    public function __construct(
        \M2E\TikTokShop\Model\Template\SellingFormatFactory $sellingFormatFactory,
        \M2E\TikTokShop\Model\Template\SynchronizationFactory $synchronizationFactory,
        \M2E\TikTokShop\Model\Template\DescriptionFactory $descriptionFactory,
        \M2E\TikTokShop\Model\Template\ComplianceFactory $complianceFactory,
        \M2E\TikTokShop\Model\ActiveRecord\Factory $activeRecordFactory
    ) {
        $this->activeRecordFactory = $activeRecordFactory;

        $this->complianceFactory = $complianceFactory;
        $this->sellingFormatFactory = $sellingFormatFactory;
        $this->synchronizationFactory = $synchronizationFactory;
        $this->descriptionFactory = $descriptionFactory;
    }

    //########################################

    /**
     * @return \M2E\TikTokShop\Model\Listing|\M2E\TikTokShop\Model\Product|null
     */
    public function getOwnerObject()
    {
        return $this->ownerObject;
    }

    /**
     * @param \M2E\TikTokShop\Model\Listing|\M2E\TikTokShop\Model\Product $object
     *
     * @return $this
     * @throws \M2E\TikTokShop\Model\Exception
     */
    public function setOwnerObject($object): self
    {
        if (
            !($object instanceof \M2E\TikTokShop\Model\Listing) &&
            !($object instanceof \M2E\TikTokShop\Model\Product)
        ) {
            throw new \M2E\TikTokShop\Model\Exception('Owner object is out of knowledge range.');
        }

        $this->ownerObject = $object;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getTemplateNick(): ?string
    {
        return $this->templateNick;
    }

    /**
     * @throws \M2E\TikTokShop\Model\Exception
     */
    public function setTemplate(string $nick): self
    {
        if (!in_array(strtolower($nick), $this->getAllTemplates())) {
            throw new \M2E\TikTokShop\Model\Exception('Policy nick is out of knowledge range.');
        }

        $this->templateNick = strtolower($nick);

        return $this;
    }

    //########################################

    /**
     * @return array
     */
    public function getAllTemplates(): array
    {
        return [
            self::TEMPLATE_DESCRIPTION,
            self::TEMPLATE_SELLING_FORMAT,
            self::TEMPLATE_SYNCHRONIZATION,
            self::TEMPLATE_COMPLIANCE
        ];
    }

    //########################################

    public function getModeColumnName(): string
    {
        return self::COLUMN_PREFIX . '_' . $this->getTemplateNick() . '_mode';
    }

    public function getTemplateIdColumnName(): string
    {
        return self::COLUMN_PREFIX . '_' . $this->getTemplateNick() . '_id';
    }

    //########################################

    public function getIdColumnValue()
    {
        if ($this->isModeParent()) {
            return null;
        }

        return $this->getOwnerObject()->getData($this->getTemplateIdColumnName());
    }

    //########################################

    public function getModeValue()
    {
        return $this->getOwnerObject()->getData($this->getModeColumnName());
    }

    /**
     * @return bool
     */
    public function isModeParent()
    {
        return $this->getModeValue() == self::MODE_PARENT;
    }

    /**
     * @deprecated
     * @return null|string
     * @throws \M2E\TikTokShop\Model\Exception\Logic
     */
    public function getTemplateModelName(): ?string
    {
        $name = null;

        switch ($this->getTemplateNick()) {
            case self::TEMPLATE_SELLING_FORMAT:
                $name = 'TikTokShop_Template_SellingFormat';
                break;
            case self::TEMPLATE_DESCRIPTION:
                $name = 'TikTokShop_Template_Description';
                break;
            case self::TEMPLATE_SYNCHRONIZATION:
                $name = 'TikTokShop_Template_Synchronization';
                break;
            case self::TEMPLATE_COMPLIANCE:
                $name = 'TikTokShop_Template_Compliance';
                break;
        }

        if ($name === null) {
            throw new \M2E\TikTokShop\Model\Exception\Logic(
                sprintf('Template nick "%s" is unknown.', $this->getTemplateNick())
            );
        }

        return $name;
    }

    public function getTemplateModel()
    {
        switch ($this->getTemplateNick()) {
            case self::TEMPLATE_SELLING_FORMAT:
                return $this->sellingFormatFactory->create();
            case self::TEMPLATE_SYNCHRONIZATION:
                return $this->synchronizationFactory->create();
            case self::TEMPLATE_DESCRIPTION:
                return $this->descriptionFactory->create();
            case self::TEMPLATE_COMPLIANCE:
                return $this->complianceFactory->createEmpty();
        }

        throw new \M2E\TikTokShop\Model\Exception\Logic(
            sprintf('Template nick "%s" is unknown.', $this->getTemplateNick())
        );
    }
}
