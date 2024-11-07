<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Helper\Client;

class MemoryLimit
{
    public function set(int $maxSize): void
    {
        $minSize = 32;
        $currentMemoryLimit = $this->get();

        if ($currentMemoryLimit <= 0 || $maxSize < $minSize || $currentMemoryLimit >= $maxSize) {
            return;
        }

        // @codingStandardsIgnoreStart
        $i = max($minSize, $currentMemoryLimit);
        do {
            $i *= 2;
            $k = min($i, $maxSize);

            if (ini_set('memory_limit', "{$k}M") === false) {
                return;
            }
        } while ($i < $maxSize);
        // @codingStandardsIgnoreEnd
    }

    public function get(): int
    {
        $memoryLimit = trim(ini_get('memory_limit'));

        if ($memoryLimit === '') {
            return 0;
        }

        $lastMemoryLimitLetter = strtolower(substr($memoryLimit, -1));
        $memoryLimit = (int)$memoryLimit;

        switch ($lastMemoryLimitLetter) {
            case 'g':
                $memoryLimit *= 1024;
            // no break

            case 'm':
                $memoryLimit *= 1024;
            // no break

            case 'k':
                $memoryLimit *= 1024;
        }

        if ($memoryLimit > 0) {
            $memoryLimit /= 1024 * 1024;
        }

        return (int)$memoryLimit;
    }
}
