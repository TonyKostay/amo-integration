<?php

namespace App\Console\Commands\AmoCrm;

use App\Services\Api\AmoCrm\Auth\AuthService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TokenRefresh extends Command
{
    protected $signature = 'amocrm:refresh-token';
    private AuthService $authService;
    public function __construct(AuthService $authService)
    {
        parent::__construct();
        $this->authService = $authService;
    }

    /**
     * @return void
     */
    public function handle(): void
    {
        try {
            $this->authService->refreshToken();
            $this->info('Success');
        } catch (\Throwable $throwable) {
            Log::info($throwable->getMessage());
            $this->info('Error');
        }

    }
}
