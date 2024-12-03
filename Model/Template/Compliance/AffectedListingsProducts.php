<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Template\Compliance;

use M2E\TikTokShop\Model\TikTokShop\Template\AffectedListingsProducts\AffectedListingsProductsAbstract;

class AffectedListingsProducts extends AffectedListingsProductsAbstract
{
    public function getTemplateNick(): string
    {
        return \M2E\TikTokShop\Model\TikTokShop\Template\Manager::TEMPLATE_COMPLIANCE;
    }
}
