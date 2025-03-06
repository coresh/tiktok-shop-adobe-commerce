<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\GlobalProduct;

class MoveSession
{
    private const SESSION_PREFIX = 'global_product_transferring';

    private \M2E\TikTokShop\Helper\Data\Session $session;

    public function __construct(\M2E\TikTokShop\Helper\Data\Session $session)
    {
        $this->session = $session;
    }

    private function setToSession(string $key, string $value): self
    {
        $key = self::SESSION_PREFIX . '_' . $key;

        $this->session->setValue($key, $value);

        return $this;
    }
}
