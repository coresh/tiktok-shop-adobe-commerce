<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Listing\Wizard;

class Step extends \M2E\TikTokShop\Model\ActiveRecord\AbstractModel
{
    public function _construct(): void
    {
        parent::_construct();
        $this->_init(\M2E\TikTokShop\Model\ResourceModel\Listing\Wizard\Step::class);
    }

    public function init(\M2E\TikTokShop\Model\Listing\Wizard $wizard, string $nick): self
    {
        $this->setData(\M2E\TikTokShop\Model\ResourceModel\Listing\Wizard\Step::COLUMN_WIZARD_ID, $wizard->getId())
             ->setData(\M2E\TikTokShop\Model\ResourceModel\Listing\Wizard\Step::COLUMN_NICK, $nick);

        return $this;
    }

    public function getWizardId(): int
    {
        return (int)$this->getData(\M2E\TikTokShop\Model\ResourceModel\Listing\Wizard\Step::COLUMN_WIZARD_ID);
    }

    public function getNick(): string
    {
        return $this->getData(\M2E\TikTokShop\Model\ResourceModel\Listing\Wizard\Step::COLUMN_NICK);
    }

    public function setResultData(array $data): self
    {
        $this->setData(\M2E\TikTokShop\Model\ResourceModel\Listing\Wizard\Step::COLUMN_DATA, json_encode($data));

        return $this;
    }

    public function getResultData(): array
    {
        $value = $this->getData(\M2E\TikTokShop\Model\ResourceModel\Listing\Wizard\Step::COLUMN_DATA);
        if (empty($value)) {
            return [];
        }

        return (array)json_decode($value, true);
    }

    public function complete(): self
    {
        $this->changeCompleteStatus(true, false);

        return $this;
    }

    public function notComplete(): self
    {
        $this->changeCompleteStatus(false, false)
             ->setResultData([]);

        return $this;
    }

    public function isSkipped(): bool
    {
        return (bool)$this->getData(\M2E\TikTokShop\Model\ResourceModel\Listing\Wizard\Step::COLUMN_IS_SKIPPED);
    }

    public function isCompleted(): bool
    {
        return (bool)$this->getData(\M2E\TikTokShop\Model\ResourceModel\Listing\Wizard\Step::COLUMN_IS_COMPLETED);
    }

    public function skip(): self
    {
        $this->changeCompleteStatus(true, true);

        return $this;
    }

    private function changeCompleteStatus(bool $isCompleted, bool $isSkipped): self
    {
        $this->setData(\M2E\TikTokShop\Model\ResourceModel\Listing\Wizard\Step::COLUMN_IS_COMPLETED, (int)$isCompleted)
             ->setData(\M2E\TikTokShop\Model\ResourceModel\Listing\Wizard\Step::COLUMN_IS_SKIPPED, (int)$isSkipped);

        return $this;
    }
}
