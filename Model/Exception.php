<?php

namespace M2E\TikTokShop\Model;

class Exception extends \Exception
{
    /** @var array */
    private $additionalData;

    /**
     * @param string $message
     * @param array $additionalData
     * @param int $code
     */
    public function __construct(string $message = '', array $additionalData = [], int $code = 0)
    {
        $this->additionalData = $additionalData;

        parent::__construct($message, $code, null);
    }

    // ----------------------------------------

    /**
     * @return array
     */
    public function getAdditionalData(): array
    {
        return $this->additionalData;
    }
}
