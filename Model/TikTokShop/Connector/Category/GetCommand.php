<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Connector\Category;

class GetCommand implements \M2E\TikTokShop\Model\Connector\CommandInterface
{
    private string $accountHash;
    private string $shopId;

    private static array $allowedPermissionStatuses = [
        Category::PERMISSION_STATUSES_AVAILABLE,
        Category::PERMISSION_STATUSES_INVITE_ONLY,
        Category::PERMISSION_STATUSES_NON_MAIN_CATEGORY,
    ];

    public function __construct(string $accountHash, string $shopId)
    {
        $this->accountHash = $accountHash;
        $this->shopId = $shopId;
    }

    public function getCommand(): array
    {
        return ['category', 'get', 'list'];
    }

    public function getRequestData(): array
    {
        return [
            'account' => $this->accountHash,
            'shop_id' => $this->shopId,
        ];
    }

    public function parseResponse(\M2E\TikTokShop\Model\Connector\Response $response): Get\Response
    {
        $result = new Get\Response();
        foreach ($response->getResponseData() as $categoryData) {
            $permissionStatuses = $this->sanitizePermissionStatuses($categoryData['permission_statuses']);
            if (empty($permissionStatuses)) {
                continue;
            }

            $result->addCategory(
                new Category(
                    $categoryData['id'],
                    $categoryData['parent_id'],
                    $categoryData['local_name'],
                    $categoryData['is_leaf'],
                    $permissionStatuses,
                )
            );
        }

        return $result;
    }

    /**
     * @param string[] $permissionStatuses
     *
     * @return string[]
     */
    private function sanitizePermissionStatuses(array $permissionStatuses): array
    {
        $result = [];
        foreach ($permissionStatuses as $status) {
            if (in_array($status, self::$allowedPermissionStatuses)) {
                $result[] = $status;
            }
        }

        return $result;
    }
}
