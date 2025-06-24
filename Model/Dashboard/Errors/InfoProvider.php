<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Dashboard\Errors;

class InfoProvider implements \M2E\Core\Model\Dashboard\Errors\InfoProviderInterface
{
    private \M2E\Core\Helper\Url $urlHelper;
    private \M2E\Core\Model\Dashboard\DateRangeFactory $dateRangeFactory;

    public function __construct(
        \M2E\Core\Helper\Url $urlHelper,
        \M2E\Core\Model\Dashboard\DateRangeFactory $dateRangeFactory
    ) {
        $this->urlHelper = $urlHelper;
        $this->dateRangeFactory = $dateRangeFactory;
    }

    public function getUrlForToday(): string
    {
        $dateRange = $this->dateRangeFactory->createForToday();

        return $this->getUrl([
            'create_date[from]' => \M2E\Core\Helper\Date::convertToLocalFormat($dateRange->dateStart),
            'create_date[to]' => \M2E\Core\Helper\Date::convertToLocalFormat($dateRange->dateEnd),
            'create_date[locale]' => \M2E\Core\Helper\Date::getLocaleResolver()->getLocale(),
            'type' => \M2E\TikTokShop\Model\Log\AbstractModel::TYPE_ERROR,
        ]);
    }

    public function getUrlForYesterday(): string
    {
        $dateRange = $this->dateRangeFactory->createForYesterday();

        return $this->getUrl([
            'create_date[from]' => \M2E\Core\Helper\Date::convertToLocalFormat($dateRange->dateStart),
            'create_date[to]' => \M2E\Core\Helper\Date::convertToLocalFormat($dateRange->dateEnd),
            'create_date[locale]' => \M2E\Core\Helper\Date::getLocaleResolver()->getLocale(),
            'type' => \M2E\TikTokShop\Model\Log\AbstractModel::TYPE_ERROR,
        ]);
    }

    public function getUrlFor2DaysAgo(): string
    {
        $dateRange = $this->dateRangeFactory->createFor2DaysAgo();

        return $this->getUrl([
            'create_date[from]' => \M2E\Core\Helper\Date::convertToLocalFormat($dateRange->dateStart),
            'create_date[to]' => \M2E\Core\Helper\Date::convertToLocalFormat($dateRange->dateEnd),
            'create_date[locale]' => \M2E\Core\Helper\Date::getLocaleResolver()->getLocale(),
            'type' => \M2E\TikTokShop\Model\Log\AbstractModel::TYPE_ERROR,
        ]);
    }

    public function getUrlForTotal(): string
    {
        return $this->getUrl([
            'type' => \M2E\TikTokShop\Model\Log\AbstractModel::TYPE_ERROR,
        ]);
    }

    private function getUrl(array $filterParams): string
    {
        return $this->urlHelper->getUrlWithFilter('m2e_tiktokshop/tiktokshop_log_listing_product/index', $filterParams, [
            'only_unique_messages' => 1,
            'view_mode' => 'separated',
        ]);
    }
}
