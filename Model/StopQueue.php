<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model;

use M2E\TikTokShop\Model\ResourceModel\StopQueue as ResourceModel;

class StopQueue extends \M2E\TikTokShop\Model\ActiveRecord\AbstractModel
{
    public function _construct(): void
    {
        parent::_construct();
        $this->_init(ResourceModel::class);
    }

    public function create(string $account, string $shopId, string $ttsProductId): self
    {
        $this->setRequestData($account, $shopId, $ttsProductId);

        return $this;
    }

    public function setAsProcessed(): void
    {
        $this->setData(ResourceModel::COLUMN_IS_PROCESSED, 1);
    }

    public function getRequestData(): array
    {
        $data = $this->getData(ResourceModel::COLUMN_REQUEST_DATA);
        if ($data === null) {
            return [];
        }

        $data = json_decode($data, true);

        return [
            'account' => $data['account'],
            'shop_id' => $data['shop_id'],
            'tts_product_id' => $data['tts_product_id'],
        ];
    }

    private function setRequestData(string $account, string $shopId, string $ttsProductId): void
    {
        $this->setData(
            ResourceModel::COLUMN_REQUEST_DATA,
            json_encode([
                'account' => $account,
                'shop_id' => $shopId,
                'tts_product_id' => $ttsProductId,
            ], JSON_THROW_ON_ERROR)
        );
    }
}
