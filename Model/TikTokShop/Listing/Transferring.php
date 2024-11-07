<?php

namespace M2E\TikTokShop\Model\TikTokShop\Listing;

class Transferring
{
    public const PARAM_LISTING_ID_DESTINATION_CREATE_NEW = 'create-new';

    private string $sessionPrefix = 'listing_transferring';

    private \M2E\TikTokShop\Model\Listing $listing;
    private \M2E\TikTokShop\Helper\Data\Session $sessionHelper;

    public function __construct(\M2E\TikTokShop\Helper\Data\Session $sessionHelper)
    {
        $this->sessionHelper = $sessionHelper;
    }

    public function setListing(\M2E\TikTokShop\Model\Listing $listing)
    {
        $this->listing = $listing;
    }

    public function getListing(): \M2E\TikTokShop\Model\Listing
    {
        return $this->listing;
    }

    //########################################

    public function setProductsIds($products)
    {
        $this->setSessionValue('products_ids', $products);

        return $this;
    }

    public function setTargetListingId($listingId)
    {
        $this->setSessionValue('to_listing_id', $listingId);

        return $this;
    }

    public function setErrorsCount($count)
    {
        $this->setSessionValue('errors_count', $count);

        return $this;
    }

    //----------------------------------------

    public function getProductsIds()
    {
        return $this->getSessionValue('products_ids');
    }

    public function getTargetListingId()
    {
        return $this->getSessionValue('to_listing_id');
    }

    public function getErrorsCount()
    {
        return (int)$this->getSessionValue('errors_count');
    }

    public function isTargetListingNew()
    {
        return $this->getTargetListingId() === self::PARAM_LISTING_ID_DESTINATION_CREATE_NEW;
    }

    //########################################

    public function setSessionValue($key, $value)
    {
        $sessionData = $this->getSessionValue();

        if ($key === null) {
            $sessionData = $value;
        } else {
            $sessionData[$key] = $value;
        }

        $this->sessionHelper->setValue($this->sessionPrefix . $this->listing->getId(), $sessionData);

        return $this;
    }

    public function getSessionValue($key = null)
    {
        $sessionData = $this->sessionHelper->getValue($this->sessionPrefix . $this->listing->getId());

        if ($sessionData === null) {
            $sessionData = [];
        }

        if ($key === null) {
            return $sessionData;
        }

        return isset($sessionData[$key]) ? $sessionData[$key] : null;
    }

    public function clearSession()
    {
        $this->sessionHelper->getValue($this->sessionPrefix . $this->listing->getId(), true);
    }
}
