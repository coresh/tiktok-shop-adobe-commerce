<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Connector;

class ResponseParser
{
    public static function parse(string $data): Response
    {
        $response = json_decode($data, true);

        if (
            !is_array($response)
            || !isset($response['data'])
            || !is_array($response['data'])
            || !isset($response['response']['result']['messages'])
            || !is_array($response['response']['result']['messages'])
            || !isset($response['response']['result']['type'])
        ) {
            throw new \M2E\TikTokShop\Model\Exception\Connection\InvalidResponse(
                'Invalid Response Format.',
                ['response' => $data],
            );
        }

        $messages = [];
        foreach ($response['response']['result']['messages'] as $rawMessage) {
            $message = new \M2E\TikTokShop\Model\Connector\Response\Message();
            $message->initFromResponseData($rawMessage);

            $messages[] = $message;
        }

        $messageCollection = new \M2E\TikTokShop\Model\Connector\Response\MessageCollection($messages);

        return new Response(
            $response['data'],
            $messageCollection,
            $response['response']['result']['type'],
        );
    }
}
