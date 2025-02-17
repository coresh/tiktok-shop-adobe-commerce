<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Ui\Select;

use Magento\Framework\Data\OptionSourceInterface;
use M2E\TikTokShop\Model\Shop\Repository;

class Shop implements OptionSourceInterface
{
    private Repository $repository;

    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    public function toOptionArray(): array
    {
        $options = [];

        foreach ($this->repository->getAll() as $shop) {
            $options[] = [
                'label' => $shop->getShopName(),
                'value' => $shop->getId(),
            ];
        }

        return $options;
    }
}
