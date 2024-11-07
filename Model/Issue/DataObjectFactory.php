<?php

namespace M2E\TikTokShop\Model\Issue;

use Magento\Framework\Message\MessageInterface as Message;

class DataObjectFactory
{
    /**
     * @param string $title
     * @param string|null $text
     * @param string|null $url
     *
     * @return \M2E\TikTokShop\Model\Issue\DataObject
     */
    public function createErrorDataObject(string $title, ?string $text, ?string $url): DataObject
    {
        return $this->create(Message::TYPE_ERROR, $title, $text, $url);
    }

    /**
     * @param string $title
     * @param string|null $text
     * @param string|null $url
     *
     * @return \M2E\TikTokShop\Model\Issue\DataObject
     */
    public function createNoticeDataObject(string $title, ?string $text, ?string $url): DataObject
    {
        return $this->create(Message::TYPE_NOTICE, $title, $text, $url);
    }

    /**
     * @param string $title
     * @param string|null $text
     * @param string|null $url
     *
     * @return \M2E\TikTokShop\Model\Issue\DataObject
     */
    public function createWarningDataObject(string $title, ?string $text, ?string $url): DataObject
    {
        return $this->create(Message::TYPE_WARNING, $title, $text, $url);
    }

    /**
     * @param string $title
     * @param string|null $text
     * @param string|null $url
     *
     * @return \M2E\TikTokShop\Model\Issue\DataObject
     */
    public function createSuccessDataObject(string $title, ?string $text, ?string $url): DataObject
    {
        return $this->create(Message::TYPE_SUCCESS, $title, $text, $url);
    }

    /**
     * Create class instance with specified parameters
     *
     * @param string|int $type
     * @param string $title
     * @param string|null $text
     * @param string|null $url
     *
     * @return \M2E\TikTokShop\Model\Issue\DataObject
     */
    public function create($type, string $title, ?string $text, ?string $url): DataObject
    {
        return new DataObject($type, $title, $text, $url);
    }
}
