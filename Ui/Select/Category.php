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
                'label' => $this->formatLabel($dictionary),
                'value' => $dictionary->getId(),
            ];
        }

        return $options;
    }

    private function formatLabel(\M2E\TikTokShop\Model\Category\Dictionary $dictionary): string
    {
        $path = $dictionary->getPath();
        $parts = array_map('trim', explode('>', $path));

        $shortPath = $path;
        if (count($parts) > 2) {
            $shortPath = sprintf('%s > ... > %s', reset($parts), end($parts));
        }

        return sprintf(
            '%s (%s)',
            $shortPath,
            $dictionary->getCategoryId()
        );
    }
}
