<?php

namespace M2E\TikTokShop\Model\Connector\Response;

class Message extends \M2E\TikTokShop\Model\Response\Message
{
    public const SENDER_KEY = 'sender';
    public const CODE_KEY = 'code';

    public const SENDER_SYSTEM = 'system';
    public const SENDER_COMPONENT = 'component';

    /** @var null */
    protected $sender = null;
    /** @var null */
    protected $code = null;

    public function initFromResponseData(array $responseData): void
    {
        parent::initFromResponseData($responseData);

        $this->sender = $responseData[self::SENDER_KEY];
        $this->code = $responseData[self::CODE_KEY];
    }

    public function initFromPreparedData($text, $type, $sender = null, $code = null): void
    {
        parent::initFromPreparedData($text, $type);

        $this->sender = $sender;
        $this->code = $code;
    }

    public function asArray(): array
    {
        return array_merge(parent::asArray(), [
            self::SENDER_KEY => $this->sender,
            self::CODE_KEY => $this->code,
        ]);
    }

    public function isSenderSystem(): bool
    {
        return $this->sender === self::SENDER_SYSTEM;
    }

    public function isSenderComponent(): bool
    {
        return $this->sender === self::SENDER_COMPONENT;
    }

    public function getCode()
    {
        return $this->code;
    }
}
