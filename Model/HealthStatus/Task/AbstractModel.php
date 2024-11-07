<?php

namespace M2E\TikTokShop\Model\HealthStatus\Task;

/**
 * Class \M2E\TikTokShop\Model\HealthStatus\Task\AbstractModel
 */
abstract class AbstractModel extends \M2E\TikTokShop\Model\AbstractModel
{
    //########################################

    public function mustBeShownIfSuccess()
    {
        return true;
    }

    //########################################

    /**
     * @return string
     */
    abstract public function getType();

    /**
     * @return Result
     */
    abstract public function process();

    //########################################
}
