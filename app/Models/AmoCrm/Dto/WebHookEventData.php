<?php

namespace App\Models\AmoCrm\Dto;

class WebHookEventData
{
    public int $createdAt;
    public int $updatedAt;
    public int $entityId;
    public int $lastModified;
    public int $responsibleUserId;
    public ?array $customFields;
    public array $fullData;

    /**
     * @param $requestEntityData
     */
    public function __construct($requestEntityData)
    {
        $this->createdAt = $requestEntityData['created_at'];
        $this->updatedAt = $requestEntityData['updated_at'];
        $this->entityId = $requestEntityData['id'];
        $this->lastModified = $requestEntityData['last_modified'];
        $this->responsibleUserId = $requestEntityData['responsible_user_id'];
        $this->customFields = $requestEntityData['custom_fields'] ?? null;
        $this->fullData = $requestEntityData;
    }

    /**
     * @param array $requestEntities
     * @return array
     */
    public static function createDataMap(array $requestEntities): array
    {
        $entitiesMap = [];
        foreach ($requestEntities as $entityData) {
            $entitiesMap[$entityData['id']] = new WebHookEventData($entityData);
        }

        return $entitiesMap;
    }


}
