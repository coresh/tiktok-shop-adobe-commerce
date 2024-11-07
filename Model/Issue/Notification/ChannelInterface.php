<?php

namespace M2E\TikTokShop\Model\Issue\Notification;

use M2E\TikTokShop\Model\Issue\DataObject;

interface ChannelInterface
{
    /**
     * @param DataObject $message
     *
     * @return void
     */
    public function addMessage(DataObject $message): void;
}
