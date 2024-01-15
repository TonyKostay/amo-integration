<?php

namespace App\Http\Controllers\Api\AmoCrm;

use App\Http\Controllers\Controller;
use App\Services\Api\AmoCrm\WebhookProcessor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ApiController extends Controller
{
    private WebhookProcessor $webhookProcessor;

    /**
     * @param WebhookProcessor $webhookProcessor
     */
    public function __construct(WebhookProcessor $webhookProcessor)
    {
        $this->webhookProcessor = $webhookProcessor;
    }

    /**
     * @param Request $request
     * @return void
     */
    public function webhookListener(Request $request): void
    {
        $this->webhookProcessor->process($request->post());
    }
}
