<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Template\Description;

use M2E\TikTokShop\Model\Template\Description as DescriptionTemplate;
use M2E\TikTokShop\Model\ResourceModel\Template\Description as DescriptionResource;

class Builder extends \M2E\TikTokShop\Model\TikTokShop\Template\AbstractBuilder
{
    protected function prepareData(): array
    {
        $data = parent::prepareData();

        $defaultData = $this->getDefaultData();

        $data = \M2E\Core\Helper\Data::arrayReplaceRecursive($defaultData, $data);

        if (isset($this->rawData[DescriptionResource::COLUMN_TITLE_MODE])) {
            $data[DescriptionResource::COLUMN_TITLE_MODE]
                = (int)$this->rawData[DescriptionResource::COLUMN_TITLE_MODE];
        }

        if (isset($this->rawData[DescriptionResource::COLUMN_TITLE_TEMPLATE])) {
            $data[DescriptionResource::COLUMN_TITLE_TEMPLATE]
                = $this->rawData[DescriptionResource::COLUMN_TITLE_TEMPLATE];
        }

        if (isset($this->rawData[DescriptionResource::COLUMN_DESCRIPTION_MODE])) {
            $data[DescriptionResource::COLUMN_DESCRIPTION_MODE]
                = (int)$this->rawData[DescriptionResource::COLUMN_DESCRIPTION_MODE];
        }

        if (isset($this->rawData[DescriptionResource::COLUMN_DESCRIPTION_TEMPLATE])) {
            $data[DescriptionResource::COLUMN_DESCRIPTION_TEMPLATE]
                = $this->rawData[DescriptionResource::COLUMN_DESCRIPTION_TEMPLATE];
        }

        if (isset($this->rawData[DescriptionResource::COLUMN_IMAGE_MAIN_MODE])) {
            $data[DescriptionResource::COLUMN_IMAGE_MAIN_MODE]
                = (int)$this->rawData[DescriptionResource::COLUMN_IMAGE_MAIN_MODE];
        }

        if (isset($this->rawData[DescriptionResource::COLUMN_IMAGE_MAIN_ATTRIBUTE])) {
            $data[DescriptionResource::COLUMN_IMAGE_MAIN_ATTRIBUTE]
                = $this->rawData[DescriptionResource::COLUMN_IMAGE_MAIN_ATTRIBUTE];
        }

        if (isset($this->rawData[DescriptionResource::COLUMN_GALLERY_IMAGES_MODE])) {
            $data[DescriptionResource::COLUMN_GALLERY_IMAGES_MODE]
                = (int)$this->rawData[DescriptionResource::COLUMN_GALLERY_IMAGES_MODE];
        }

        if (isset($this->rawData[DescriptionResource::COLUMN_GALLERY_IMAGES_LIMIT])) {
            $data[DescriptionResource::COLUMN_GALLERY_IMAGES_LIMIT]
                = (int)$this->rawData[DescriptionResource::COLUMN_GALLERY_IMAGES_LIMIT];
        }

        if (isset($this->rawData[DescriptionResource::COLUMN_GALLERY_IMAGES_ATTRIBUTE])) {
            $data[DescriptionResource::COLUMN_GALLERY_IMAGES_ATTRIBUTE]
                = $this->rawData[DescriptionResource::COLUMN_GALLERY_IMAGES_ATTRIBUTE];
        }

        if (isset($this->rawData[DescriptionResource::COLUMN_RESIZE_IMAGE])) {
            $data[DescriptionResource::COLUMN_RESIZE_IMAGE]
                = $this->rawData[DescriptionResource::COLUMN_RESIZE_IMAGE];
        }

        return $data;
    }

    // ----------------------------------------

    public function getDefaultData(): array
    {
        return [
            DescriptionResource::COLUMN_TITLE_MODE => DescriptionTemplate::TITLE_MODE_PRODUCT,
            DescriptionResource::COLUMN_TITLE_TEMPLATE => '',

            DescriptionResource::COLUMN_DESCRIPTION_MODE => '',
            DescriptionResource::COLUMN_DESCRIPTION_TEMPLATE => '',

            DescriptionResource::COLUMN_IMAGE_MAIN_MODE => DescriptionTemplate::IMAGE_MAIN_MODE_PRODUCT,
            DescriptionResource::COLUMN_IMAGE_MAIN_ATTRIBUTE => '',

            DescriptionResource::COLUMN_GALLERY_IMAGES_MODE => DescriptionTemplate::GALLERY_IMAGES_MODE_NONE,
            DescriptionResource::COLUMN_GALLERY_IMAGES_LIMIT => 0,
            DescriptionResource::COLUMN_GALLERY_IMAGES_ATTRIBUTE => '',

            DescriptionResource::COLUMN_RESIZE_IMAGE => DescriptionTemplate::RESIZE_IMAGE_YES,
        ];
    }
}
