<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\DataBuilder\VariantSku;

class Collection
{
    public const METADATA_KEY_ONLINE_IDENTIFIER = 'online_identifier';
    public const METADATA_KEY_ONLINE_IDENTIFIER_ID = 'online_identifier_id';
    public const METADATA_KEY_ONLINE_IDENTIFIER_TYPE = 'online_identifier_type';

    /** @var Item[] */
    private array $items = [];

    public function toArray(): array
    {
        return array_map(
            fn(Item $item) => $item->toArray(),
            $this->items
        );
    }

    public function addItem(Item $item)
    {
        $this->items[] = $item;
    }

    public function selectBaseSalesAttributeForImage()
    {
        $tmp = [];
        foreach ($this->items as $item) {
            foreach ($item->getSalesAttributes() as $salesAttribute) {
                $imageHash = '';
                if ($salesAttribute->hasImage()) {
                    $imageHash = $salesAttribute->getImage()->getHash();
                }

                $key = json_encode([
                    'hash' => $imageHash,
                    'name' => $salesAttribute->getName(),
                    'value' => $salesAttribute->getValueName(),
                ]);

                $tmp[$key] = ($tmp[$key] ?? 0) + 1;
            }
        }

        if (empty($tmp)) {
            return;
        }

        arsort($tmp);

        $baseAttribute = json_decode(array_key_first($tmp), true)['name'] ?? null;

        if ($baseAttribute === null) {
            return;
        }

        foreach ($this->items as $item) {
            foreach ($item->getSalesAttributes() as $salesAttribute) {
                if (!$salesAttribute->hasImage()) {
                    continue;
                }

                if ($salesAttribute->getName() === $baseAttribute) {
                    continue;
                }

                $salesAttribute->removeImage();
            }
        }
    }

    public function collectOnlineData(): array
    {
        $onlineData = [];
        foreach ($this->items as $item) {
            $itemTotalQty = 0;
            foreach ($item->getInventories() as $inventory) {
                $itemTotalQty += $inventory->getQuantity();
            }

            $imageHash = '';
            foreach ($item->getSalesAttributes() as $salesAttribute) {
                if (!$salesAttribute->hasImage()) {
                    continue;
                }

                $imageHash = $salesAttribute->getImage()->getHash();
            }

            $onlineData[$item->getSellerSku()] = [
                'online_sku' => $item->getSellerSku(),
                'online_price' => $item->getPrice()->getAmount(),
                'online_qty' => $itemTotalQty,
                'online_image' => $imageHash,
            ];

            if ($item->getIdentifier() !== null) {
                $onlineData[$item->getSellerSku()][self::METADATA_KEY_ONLINE_IDENTIFIER] = [
                    self::METADATA_KEY_ONLINE_IDENTIFIER_ID => $item->getIdentifier()->getCode(),
                    self::METADATA_KEY_ONLINE_IDENTIFIER_TYPE => $item->getIdentifier()->getType(),
                ];
            }
        }

        return $onlineData;
    }
}
