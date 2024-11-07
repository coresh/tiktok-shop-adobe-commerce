<?php

namespace M2E\TikTokShop\Model\Exception;

class Exception extends \Exception
{
    /** @var array */
    private $additionalData;

    public function __construct(string $message = '', array $additionalData = [], int $code = 0)
    {
        $this->additionalData = $additionalData;

        parent::__construct($message, $code, null);
    }

    public function getAdditionalData(): array
    {
        return $this->additionalData;
    }
}
