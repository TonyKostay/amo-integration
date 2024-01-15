<?php

namespace App\Services\Api\AmoCrm\Auth;

use AmoCRM\Client\AmoCRMApiClient;
use App\Exceptions\AmoCrmApi\NotFoundTokenModel;
use App\Models\AmoCrm\AmoApiToken;
use Illuminate\Support\Facades\Log;
use League\OAuth2\Client\Token\AccessToken;

class AuthService
{
    private AmoCRMApiClient $amoCRMApiClient;

    public function __construct()
    {
        $this->init();
    }

    /**
     * @return void
     */
    private function init(): void
    {
        $clientId = env('AMO_CRM_CLIENT_ID');
        $clientSecret = env('AMO_CRM_CLIENT_SECRET');
        $redirectUri = env('AMO_CRM_REDIRECT_URI');
        $accountDomain = env('AMO_CRM_ACCOUNT_DOMAIN');
        $this->amoCRMApiClient = new AmoCRMApiClient($clientId, $clientSecret, $redirectUri);
        $this->amoCRMApiClient->setAccountBaseDomain($accountDomain);
    }

    /**
     * @return AmoCRMApiClient
     * @throws NotFoundTokenModel
     */
    public function getClient(): AmoCRMApiClient
    {
        $currentToken = AmoApiToken::getLastToken();
        if (empty($currentToken)) {
            throw new NotFoundTokenModel();
        }
        $token = new AccessToken($currentToken->toArray());
        $this->amoCRMApiClient->setAccessToken($token);

        return $this->amoCRMApiClient;
    }

    /**
     * @param string $code
     * @return bool
     */
    public function setToken(string $code): bool
    {
        try {
            $token = $this->amoCRMApiClient->getOAuthClient()->getAccessTokenByCode($code);
            $tokenModel = new AmoApiToken([
                'access_token' => $token->getToken(),
                'refresh_token' => $token->getRefreshToken(),
                'token_type' => $token->jsonSerialize()['token_type'],
                'expires' => $token->getExpires(),
            ]);
            $tokenModel->save();

            return true;

        } catch (\Throwable $throwable) {
            Log::info($throwable->getMessage());
            Log::info($throwable->getTraceAsString());

            return false;
        }
    }

    /**
     * @return void
     * @throws \AmoCRM\Exceptions\AmoCRMoAuthApiException
     */
    public function refreshToken(): void
    {
        $currentToken = AmoApiToken::getLastToken();
        if (empty($currentToken)) {
            Log::info('Refresh token failed. Token model is empty.');
            return;
        }
        $token = new AccessToken($currentToken->toArray());
        $newTokenData = $this->amoCRMApiClient->getOAuthClient()->getAccessTokenByRefreshToken($token);
        $tokenModel = new AmoApiToken([
            'access_token' => $newTokenData->getToken(),
            'refresh_token' => $newTokenData->getRefreshToken(),
            'token_type' => $newTokenData->jsonSerialize()['token_type'],
            'expires' => $newTokenData->getExpires(),
        ]);
        $tokenModel->save();
    }
}
