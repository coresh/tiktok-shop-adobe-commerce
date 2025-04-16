<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Listing;

class Wizard extends \M2E\TikTokShop\Model\ActiveRecord\AbstractModel
{
    public const TYPE_GENERAL = 'general';
    public const TYPE_UNMANAGED = 'unmanaged';

    private \M2E\TikTokShop\Model\Listing $listing;

    /** @var \M2E\TikTokShop\Model\Listing\Repository */
    private Repository $listingRepository;
    /** @var \M2E\TikTokShop\Model\Listing\Wizard\Repository */
    private Wizard\Repository $wizardRepository;

    /** @var \M2E\TikTokShop\Model\Listing\Wizard\Step[] */
    private array $steps;

    public function __construct(
        \M2E\TikTokShop\Model\Listing\Wizard\Repository $wizardRepository,
        Repository $listingRepository,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data,
        );
        $this->listingRepository = $listingRepository;
        $this->wizardRepository = $wizardRepository;
    }

    protected function _construct(): void
    {
        parent::_construct();
        $this->_init(\M2E\TikTokShop\Model\ResourceModel\Listing\Wizard::class);
    }

    /**
     * @param \M2E\TikTokShop\Model\Listing $listing
     * @param string $type
     * @param string $firstStepNick
     *
     * @return $this
     */
    public function init(\M2E\TikTokShop\Model\Listing $listing, string $type, string $firstStepNick): self
    {
        $this
            ->setData(\M2E\TikTokShop\Model\ResourceModel\Listing\Wizard::COLUMN_LISTING_ID, $listing->getId())
            ->setData(\M2E\TikTokShop\Model\ResourceModel\Listing\Wizard::COLUMN_TYPE, $type)
            ->setData(\M2E\TikTokShop\Model\ResourceModel\Listing\Wizard::COLUMN_CURRENT_STEP_NICK, $firstStepNick)
            ->setData(
                \M2E\TikTokShop\Model\ResourceModel\Listing\Wizard::COLUMN_PROCESS_START_DATE,
                \M2E\Core\Helper\Date::createCurrentGmt(),
            );

        return $this;
    }

    public function initListing(\M2E\TikTokShop\Model\Listing $listing): void
    {
        $this->listing = $listing;
    }

    public function getListing(): \M2E\TikTokShop\Model\Listing
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (isset($this->listing)) {
            return $this->listing;
        }

        return $this->listing = $this->listingRepository->get($this->getListingId());
    }

    public function initSteps(array $steps): self
    {
        $this->steps = $steps;

        return $this;
    }

    public function getSteps(): array
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (isset($this->steps)) {
            return $this->steps;
        }

        return $this->steps = $this->wizardRepository->findSteps($this);
    }

    // ----------------------------------------

    public function getId(): int
    {
        return (int)parent::getId();
    }

    public function getListingId(): int
    {
        return (int)$this->getData(\M2E\TikTokShop\Model\ResourceModel\Listing\Wizard::COLUMN_LISTING_ID);
    }

    public function getType(): string
    {
        return $this->getData(\M2E\TikTokShop\Model\ResourceModel\Listing\Wizard::COLUMN_TYPE);
    }

    public function setProductCountTotal(int $count): self
    {
        $this->setData(\M2E\TikTokShop\Model\ResourceModel\Listing\Wizard::COLUMN_PRODUCT_COUNT_TOTAL, $count);

        return $this;
    }

    public function complete(int $productCount): self
    {
        $this->setData(\M2E\TikTokShop\Model\ResourceModel\Listing\Wizard::COLUMN_PRODUCT_COUNT_TOTAL, $productCount)
             ->setData(\M2E\TikTokShop\Model\ResourceModel\Listing\Wizard::COLUMN_IS_COMPLETED, 1)
             ->setData(
                 \M2E\TikTokShop\Model\ResourceModel\Listing\Wizard::COLUMN_PROCESS_END_DATE,
                 \M2E\Core\Helper\Date::createCurrentGmt()->format('Y-m-d H:i:s'),
             );

        return $this;
    }

    public function isCompleted(): bool
    {
        return (bool)$this->getData(\M2E\TikTokShop\Model\ResourceModel\Listing\Wizard::COLUMN_IS_COMPLETED);
    }

    public function setCurrentStepNick(string $nick): self
    {
        $this->setData(\M2E\TikTokShop\Model\ResourceModel\Listing\Wizard::COLUMN_CURRENT_STEP_NICK, $nick);

        return $this;
    }

    public function getCurrentStepNick(): string
    {
        return $this->getData(\M2E\TikTokShop\Model\ResourceModel\Listing\Wizard::COLUMN_CURRENT_STEP_NICK);
    }

    public static function validateType(string $type): void
    {
        if (!in_array($type, [self::TYPE_GENERAL, self::TYPE_UNMANAGED])) {
            throw new \LogicException('Wrong listing wizard type.');
        }
    }
}
