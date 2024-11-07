<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Helper\Magento;

class Assets
{
    private \Magento\Framework\View\Asset\Repository $assetsRepo;
    private \Magento\Framework\App\RequestInterface $request;

    public function __construct(
        \Magento\Framework\View\Asset\Repository $assetsRepo,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->assetsRepo = $assetsRepo;
        $this->request = $request;
    }

    public function getViewFileUrl(string $fileName): string
    {
        return $this->assetsRepo->getUrlWithParams($fileName, ['_secure' => $this->request->isSecure()]);
    }
}
