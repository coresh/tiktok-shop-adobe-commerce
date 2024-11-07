<?php

namespace M2E\TikTokShop\Model\Issue;

interface LocatorInterface
{
    /**
     * @return DataObject[]
     */
    public function getIssues(): array;
}
