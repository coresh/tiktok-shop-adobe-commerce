<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\DataBuilder\VariantSku\Item;

class SalesAttribute
{
    private \M2E\TikTokShop\Model\Image\Repository $ttsImageRepository;
    private string $name;
    private string $valueName;
    private ?\M2E\TikTokShop\Model\Magento\Product\Image $image;

    private array $imagesCache = [];

    public function __construct(
        \M2E\TikTokShop\Model\Image\Repository $ttsImageRepository,
        string $name,
        string $valueName,
        ?\M2E\TikTokShop\Model\Magento\Product\Image $image = null
    ) {
        $this->ttsImageRepository = $ttsImageRepository;

        $this->name = $name;
        $this->valueName = $valueName;
        $this->image = $image;
    }

    public function hasImage(): bool
    {
        return $this->image !== null;
    }

    public function getImage(): ?\M2E\TikTokShop\Model\Magento\Product\Image
    {
        return $this->image;
    }

    public function removeImage()
    {
        $this->image = null;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getValueName(): string
    {
        return $this->valueName;
    }

    public function toArray(): array
    {
        $data = [
            'name' => $this->name,
            'value_name' => $this->valueName,
        ];

        if ($this->image !== null) {
            $data['sku_img'] = $this->getImageRequest($this->image);
        }

        return $data;
    }

    private function getImageRequest(\M2E\TikTokShop\Model\Magento\Product\Image $image): array
    {
        if (!array_key_exists($image->getHash(), $this->imagesCache)) {
            $this->imagesCache[$image->getHash()] = $this->ttsImageRepository->findByHashAndType(
                $image->getHash(),
                \M2E\TikTokShop\Model\Image::IMAGE_TYPE_VARIANT
            );
        }

        $ttsImage = $this->imagesCache[$image->getHash()];

        if ($ttsImage !== null) {
            return [
                'uri' => $ttsImage->getUri(),
            ];
        }

        return [
            'nick' => $image->getHash(),
            'url' => $image->getUrl(),
        ];
    }
}
