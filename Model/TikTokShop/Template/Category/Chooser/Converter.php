<?php

namespace M2E\TikTokShop\Model\TikTokShop\Template\Category\Chooser;

use M2E\TikTokShop\Model\TikTokShop\Template\Category as TemplateCategory;

class Converter extends \M2E\TikTokShop\Model\AbstractModel
{
    private int $shopId;
    private int $accountId;
    private array $categoriesData = [];

    public function setCategoryDataFromTemplate(array $data)
    {
        $converted = [
            'category_mode' => $data['category_mode'],
            'category_id' => $data['category_id'],
            'category_attribute' => $data['category_attribute'],
            'category_path' => $data['category_path'],
            'template_id' => $data['id'],
            'is_custom_template' => null,
            'specific' => $data['specific'] ?? [],
        ];

        $this->categoriesData = $converted;

        return $this;
    }

    public function setCategoryDataFromChooser(array $data)
    {
        if (empty($data)) {
            return $this;
        }

        $converted = [
            'category_mode' => $data['mode'],
            'category_id' => $data['mode'] == TemplateCategory::CATEGORY_MODE_TTS ? $data['value'] : null,
            'category_attribute' => $data['mode'] == TemplateCategory::CATEGORY_MODE_ATTRIBUTE ? $data['value'] : null,
            'category_path' => $data['path'] ?? null,
            'template_id' => $data['template_id'] ?? null,
            'is_custom_template' => $data['is_custom_template'] ?? null,
            'specific' => $data['specific'] ?? [],
        ];

        $this->categoriesData = $converted;

        return $this;
    }

    //----------------------------------------

    public function getCategoryDataForChooser()
    {
        if (empty($this->categoriesData)) {
            return null;
        }

        $part = $this->categoriesData;

        return [
            'mode' => $part['category_mode'],
            'value' => $part['category_mode'] == TemplateCategory::CATEGORY_MODE_TTS
                ? $part['category_id'] : $part['category_attribute'],
            'path' => $part['category_path'],
            'template_id' => $part['template_id'],
            'is_custom_template' => $part['is_custom_template'],
        ];
    }

    public function getCategoryDataForTemplate()
    {
        if (empty($this->categoriesData)) {
            return [];
        }

        $part = $this->categoriesData;
        $part['account_id'] = $this->accountId;
        $part['shop_id'] = $this->shopId;

        return $part;
    }

    public function setShopId(int $shopId)
    {
        $this->shopId = $shopId;

        return $this;
    }

    public function setAccountId($accountId)
    {
        $this->accountId = $accountId;

        return $this;
    }
}
