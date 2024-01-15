<?php

namespace App\Http\Controllers\Api\AmoCrm;

use App\Http\Controllers\Controller;
use App\Services\Api\AmoCrm\Auth\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    private AuthService $authService;

    /**
     * @param AuthService $authService
     */
    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * @param Request $request
     * @return void
     */
    public function __invoke(Request $request): void
    {
        $code = $request->get('code');
        if (empty($code)) {
            Log::info('Request code is empty');
            return;
        }

        $this->authService->setToken($code);
    }
}
