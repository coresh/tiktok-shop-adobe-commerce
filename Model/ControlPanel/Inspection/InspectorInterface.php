<?php

namespace M2E\TikTokShop\Model\ControlPanel\Inspection;

interface InspectorInterface
{
    /**
     * @return \M2E\TikTokShop\Model\ControlPanel\Inspection\Issue[]
     */
    public function process();
}
