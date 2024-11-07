<?php

namespace M2E\TikTokShop\Model\Issue;

use Magento\Framework\Message\MessageInterface;

class DataObject
{
    /** @var string|int */
    private $type;
    /** @var string */
    private $title;
    /** @var string */
    private $text;
    /** @var string */
    private $url;

    /**
     * @param string|int $type
     * @param string $title
     * @param string|null $text
     * @param string|null $url
     */
    public function __construct(
        $type,
        string $title,
        ?string $text,
        ?string $url
    ) {
        $this->type = $type;
        $this->title = $title;
        $this->text = $text;
        $this->url = $url;
    }

    /**
     * @return string|int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string|null
     */
    public function getText(): ?string
    {
        return $this->text;
    }

    /**
     * @return string|null
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * @return bool
     */
    public function isNotice(): bool
    {
        return $this->getType() === MessageInterface::TYPE_NOTICE;
    }

    /**
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->getType() === MessageInterface::TYPE_SUCCESS;
    }

    /**
     * @return bool
     */
    public function isError(): bool
    {
        return $this->getType() === MessageInterface::TYPE_ERROR;
    }

    /**
     * @return bool
     */
    public function isWarning(): bool
    {
        return $this->getType() === MessageInterface::TYPE_WARNING;
    }
}
