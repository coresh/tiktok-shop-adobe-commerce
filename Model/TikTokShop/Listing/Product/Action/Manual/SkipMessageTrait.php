<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Manual;

trait SkipMessageTrait
{
    private function createSkipListMessage(): string
    {
        return (string)__(
            'Item(s) were not listed. The List rules set in Synchronization Policy are not met.'
        );
    }

    private function createSkipReviseMessage(): string
    {
        return (string)__(
            'Item(s) were not revised. No relevant product changes were detected to be updated on the channel.'
        );
    }

    private function createSkipRelistMessage(): string
    {
        return (string)__(
            'Item(s) were not relisted. The Relist rules set in Synchronization Policy are not met.'
        );
    }

    private function createSkipStopMessage(): string
    {
        return (string)__(
            'Item(s) were not stopped. The Stop rules set in Synchronization Policy are not met.'
        );
    }
}
