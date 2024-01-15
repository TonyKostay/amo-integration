<?php

namespace App\Services\Api\AmoCrm\EntityHandlers;

use AmoCRM\Client\AmoCRMApiClient;
use AmoCRM\Filters\BaseEntityFilter;

interface HandlerInterface
{
    public function __construct(AmoCRMApiClient $client);
    public function add(array $entitiesData);
    public function update(array $entitiesData);
    public function getEntityType(): string;
    public function getFilter(): BaseEntityFilter;
}
