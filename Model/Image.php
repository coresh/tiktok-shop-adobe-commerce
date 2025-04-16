<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model;

use M2E\TikTokShop\Model\ResourceModel\Image as ImageResource;

class Image extends \M2E\TikTokShop\Model\ActiveRecord\AbstractModel
{
    public const IMAGE_TYPE_PRODUCT = 'product';
    public const IMAGE_TYPE_VARIANT = 'variant';
    public const IMAGE_TYPE_SIZE_CHART = 'size-chart';
    public const IMAGE_TYPE_CERTIFICATE = 'certificate';

    public function _construct(): void
    {
        parent::_construct();
        $this->_init(ImageResource::class);
    }

    public function create(
        string $type,
        string $url,
        string $hash,
        string $uri
    ): self {
        $this->setType($type);
        $this->setUrl($url);
        $this->setUri($uri);
        $this->setHash($hash);

        return $this;
    }

    public function setType(string $type)
    {
        $this->setData(ImageResource::COLUMN_TYPE, $type);
    }

    public function getUrl(): string
    {
        return $this->getData(ImageResource::COLUMN_URL);
    }

    public function setUrl(string $url): void
    {
        $this->setData(ImageResource::COLUMN_URL, $url);
    }

    public function getHash(): string
    {
        return $this->getData(ImageResource::COLUMN_HASH);
    }

    public function setHash(string $hash): void
    {
        $this->setData(ImageResource::COLUMN_HASH, $hash);
    }

    public function getUri(): ?string
    {
        $imageId = $this->getData(ImageResource::COLUMN_URI);
        if ($imageId === null) {
            return null;
        }

        return $imageId;
    }

    public function setUri(string $uri): void
    {
        $this->setData(ImageResource::COLUMN_URI, $uri);
    }

    public function getUpdateDate(): \DateTime
    {
        return \M2E\Core\Helper\Date::createDateGmt(
            $this->getData(ImageResource::COLUMN_UPDATE_DATE)
        );
    }

    public function setUpdateDate(\DateTime $createDate): void
    {
        $timeZone = new \DateTimeZone(\M2E\Core\Helper\Date::getTimezone()->getDefaultTimezone());
        $createDate->setTimezone($timeZone);
        $this->setData(ImageResource::COLUMN_UPDATE_DATE, $createDate->format('Y-m-d H:i:s'));
    }

    public function getCreateDate(): \DateTime
    {
        return \M2E\Core\Helper\Date::createDateGmt(
            $this->getData(ImageResource::COLUMN_CREATE_DATE)
        );
    }

    public function setCreateDate(\DateTime $createDate): void
    {
        $this->setData(ImageResource::COLUMN_CREATE_DATE, $createDate->format('Y-m-d H:i:s'));
    }
}
