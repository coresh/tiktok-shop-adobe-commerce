<?php

namespace M2E\TikTokShop\Model\Template\SellingFormat;

class Repository
{
    private \M2E\TikTokShop\Model\ResourceModel\Template\SellingFormat $resource;
    private \M2E\TikTokShop\Model\Template\SellingFormatFactory $sellingFormatFactory;
    private \M2E\TikTokShop\Model\ResourceModel\Template\SellingFormat\CollectionFactory $collectionFactory;

    public function __construct(
        \M2E\TikTokShop\Model\ResourceModel\Template\SellingFormat $resource,
        \M2E\TikTokShop\Model\ResourceModel\Template\SellingFormat\CollectionFactory $collectionFactory,
        \M2E\TikTokShop\Model\Template\SellingFormatFactory $sellingFormatFactory
    ) {
        $this->resource = $resource;
        $this->sellingFormatFactory = $sellingFormatFactory;
        $this->collectionFactory = $collectionFactory;
    }

    public function find(int $id): ?\M2E\TikTokShop\Model\Template\SellingFormat
    {
        $model = $this->sellingFormatFactory->create();
        $this->resource->load($model, $id);

        if ($model->isObjectNew()) {
            return null;
        }

        return $model;
    }

    /**
     * @throws \M2E\TikTokShop\Model\Exception\Logic
     */
    public function get(int $id): \M2E\TikTokShop\Model\Template\SellingFormat
    {
        $template = $this->find($id);
        if ($template === null) {
            throw new \M2E\TikTokShop\Model\Exception\Logic('Synchronization not found');
        }

        return $template;
    }

    public function delete(\M2E\TikTokShop\Model\Template\SellingFormat $template)
    {
        $this->resource->delete($template);
    }

    public function create(\M2E\TikTokShop\Model\Template\SellingFormat $template)
    {
        $this->resource->save($template);
    }

    public function save(\M2E\TikTokShop\Model\Template\SellingFormat $template)
    {
        $this->resource->save($template);
    }

    /**
     * @return \M2E\TikTokShop\Model\Template\SellingFormat[]
     */
    public function getAll(): array
    {
        $collection = $this->collectionFactory->create();

        return array_values($collection->getItems());
    }
}
