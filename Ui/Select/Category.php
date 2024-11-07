<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Ui\Select;

class Category implements \Magento\Framework\Data\OptionSourceInterface
{
    private \M2E\TikTokShop\Model\Category\Dictionary\Repository $repository;

    public function __construct(\M2E\TikTokShop\Model\Category\Dictionary\Repository $repository)
    {
        $this->repository = $repository;
    }

    public function toOptionArray(): array
    {
        $options = [];
        foreach ($this->repository->getAllItems() as $dictionary) {
            $options[] = [
                'label' => $dictionary->getPath(),
                'value' => $dictionary->getId(),
            ];
        }

        return $options;
    }
}
