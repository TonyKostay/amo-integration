<?php

namespace App\Services\Api\AmoCrm\EntityHandlers;

use AmoCRM\Client\AmoCRMApiClient;

class EntityHandlerFactory
{
    private static array $handlersMap = [
        'leads' => LeadHandler::class,
        'contacts' => ContactHandler::class,
    ];

    private static array $actionsMap = [
        'add',
        'update'
    ];

    /**
     * @return array
     */
    public static function getHandlersMap(): array
    {
        return self::$handlersMap;
    }

    /**
     * @return array
     */
    public static function getActionsMap(): array
    {
        return self::$actionsMap;
    }

    /**
     * @param string $key
     * @param AmoCRMApiClient $client
     * @return HandlerInterface|null
     */
    public static function getHandler(
        string $key,
        AmoCRMApiClient $client,
    ): ?HandlerInterface
    {
        if (array_key_exists($key, self::$handlersMap)) {
            return new self::$handlersMap[$key]($client);
        }

        return null;
    }
}
