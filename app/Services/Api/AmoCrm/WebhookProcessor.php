<?php

namespace App\Services\Api\AmoCrm;

use AmoCRM\Client\AmoCRMApiClient;
use App\Exceptions\AmoCrmApi\NotFoundActionException;
use App\Exceptions\AmoCrmApi\NotFoundHandlerException;
use App\Exceptions\AmoCrmApi\NotFoundTokenModel;
use App\Services\Api\AmoCrm\Auth\AuthService;
use App\Services\Api\AmoCrm\EntityHandlers\EntityHandlerFactory;
use Illuminate\Support\Facades\Log;

class WebhookProcessor
{
    private AuthService $authService;

    /**
     * @param AuthService $authService
     */
    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }


    public function process(array $data): void
    {
        try {
            $client = $this->authService->getClient();
            $this->handle($data, $client);

        } catch (NotFoundHandlerException $foundHandlerException) {
            /** TODO something */
            Log::info($foundHandlerException->getMessage());

        } catch (NotFoundActionException $actionException) {
            /** TODO something */
            Log::info($actionException->getMessage());

        } catch (NotFoundTokenModel $notFoundTokenModel) {
            /** TODO something */
            Log::info($notFoundTokenModel->getMessage());

        } catch (\Throwable $throwable) {
            /** TODO something */
            Log::info($throwable->getTraceAsString());
            Log::info($throwable->getMessage());
        }
    }

    /**
     * @throws NotFoundHandlerException
     * @throws NotFoundActionException
     */
    public function handle(array $data, AmoCRMApiClient $client): void
    {
        foreach ($data as $key => $value) {
            if (array_key_exists($key, EntityHandlerFactory::getHandlersMap())) {
                foreach ($value as $action => $actionData) {
                    if (in_array($action, EntityHandlerFactory::getActionsMap())) {
                        $handler = EntityHandlerFactory::getHandler($key, $client);
                        $handler->$action($actionData);

                        return;
                    }
                }
                throw new NotFoundActionException();
            }
        }
        throw new NotFoundHandlerException();
    }
}
