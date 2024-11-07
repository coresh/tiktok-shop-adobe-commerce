<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Account\Settings;

class InvoicesAndShipment
{
    private bool $isCreateMagentoInvoice = true;
    private bool $isCreateMagentoShipment = true;

    public function isCreateMagentoInvoice(): bool
    {
        return $this->isCreateMagentoInvoice;
    }

    public function createWithMagentoInvoice(bool $status): self
    {
        $new = clone $this;
        $new->isCreateMagentoInvoice = $status;

        return $new;
    }

    // ----------------------------------------

    public function isCreateMagentoShipment(): bool
    {
        return $this->isCreateMagentoShipment;
    }

    public function createWithMagentoShipment(bool $status): self
    {
        $new = clone $this;
        $new->isCreateMagentoShipment = $status;

        return $new;
    }
}
