<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Controller\Adminhtml\Listing;

class ClearLog extends \M2E\TikTokShop\Controller\Adminhtml\AbstractListing
{
    private \M2E\TikTokShop\Model\Listing\Log\Repository $repository;
    private \M2E\Core\Helper\Url $urlHelper;

    public function __construct(
        \M2E\TikTokShop\Model\Listing\Log\Repository $repository,
        \M2E\Core\Helper\Url $urlHelper
    ) {
        parent::__construct();

        $this->repository = $repository;
        $this->urlHelper = $urlHelper;
    }

    public function execute()
    {
        $ids = $this->getRequestIds();

        if (count($ids) === 0) {
            $this->getMessageManager()->addError(__('Please select Item(s) to clear.'));
            $this->_redirect('*/*/index');

            return;
        }

        foreach ($ids as $id) {
            $this->repository->removeForListing((int)$id);
        }

        $this->getMessageManager()->addSuccess(__('The Listing(s) Log was cleared.'));
        $this->_redirect($this->urlHelper->getBackUrl('list'));
    }
}
