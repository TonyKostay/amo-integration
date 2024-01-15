<?php

namespace App\Services\Api\AmoCrm\EntityHandlers;

use AmoCRM\Client\AmoCRMApiClient;
use AmoCRM\Collections\BaseApiCollection;
use AmoCRM\Collections\NotesCollection;
use AmoCRM\EntitiesServices\BaseEntity;
use AmoCRM\EntitiesServices\CustomFields;
use AmoCRM\EntitiesServices\Events;
use AmoCRM\Exceptions\AmoCRMApiException;
use AmoCRM\Exceptions\AmoCRMMissedTokenException;
use AmoCRM\Exceptions\AmoCRMoAuthApiException;
use AmoCRM\Exceptions\InvalidArgumentException;
use AmoCRM\Filters\EventsFilter;
use AmoCRM\Models\EventModel;
use AmoCRM\Models\NoteType\CommonNote;
use AmoCRM\Models\NoteType\ServiceMessageNote;
use AmoCRM\Models\UserModel;
use App\Exceptions\AmoCrmApi\InvalidRequestEntityData;
use App\Helpers\AmoCrm\FieldsHelper;
use App\Models\AmoCrm\Dto\ChangedField;
use App\Models\AmoCrm\Dto\WebHookEventData;
use Illuminate\Support\Facades\Log;

abstract class BaseEntityHandler implements HandlerInterface
{
    protected AmoCRMApiClient $amoCrmClient;
    protected BaseEntity $entityService;
    protected CustomFields $customFieldService;
    protected FieldsHelper $fieldsHelper;

    /**
     * @return BaseEntity
     */
    abstract function getEntityService(): BaseEntity;
    /**
     * @param AmoCRMApiClient $amoCrmClient
     */
    public function __construct(AmoCRMApiClient $amoCrmClient)
    {
        $this->amoCrmClient = $amoCrmClient;
        $this->init();
    }

    /**
     * @return void
     * @throws AmoCRMMissedTokenException
     * @throws InvalidArgumentException
     */
    protected function init(): void
    {
        $this->entityService = $this->getEntityService();
        $this->customFieldService = $this->getCustomFieldsService();
        $this->fieldsHelper = new FieldsHelper($this->customFieldService);
    }

    /**
     * @throws AmoCRMMissedTokenException
     */
    public function getEventService(): Events
    {
        return $this->amoCrmClient->events();
    }

    /**
     * @return CustomFields
     * @throws AmoCRMMissedTokenException
     * @throws InvalidArgumentException
     */
    public function getCustomFieldsService(): CustomFields
    {
        return $this->amoCrmClient->customFields($this->getEntityType());
    }

    /**
     * @param array $entitiesData
     * @return bool
     */
    public function add(array $entitiesData): bool
    {
        $entityIds = $this->getEntityIdsByHook($entitiesData);
        $webHookEntitiesMap = WebHookEventData::createDataMap($entitiesData);
        $entityCollection = $this->getEntitiesByIds($entityIds);

        if (empty($entityCollection)) {
            return false;
        }
        $notesCollection = new NotesCollection();
        foreach ($entityCollection->toArray() as $entity) {
            /**@var $user UserModel */
            $user = $this->amoCrmClient->users()->getOne($entity['responsible_user_id']);
            /**@var $webhookEventData WebHookEventData */
            $webhookEventData = $webHookEntitiesMap[$entity['id']];
            $updateDateValue = date('H:m:s d.m.Y', $webhookEventData->createdAt);
            $message = "Название:{$entity['name']}\nОтветственный: {$user->getName()}\n$updateDateValue";
            $serviceMessageNote = $this->createMessageNote($entity['id'],$message, $webhookEventData->responsibleUserId);
            $notesCollection->add($serviceMessageNote);
        }
        if ($notesCollection->count() > 0) {
            $this->addNotes($notesCollection);
        }
        return true;
    }

    /**
     * @param array $entitiesData
     * @return bool
     * @throws AmoCRMApiException
     * @throws AmoCRMoAuthApiException
     */
    public function update(array $entitiesData): bool
    {
        $eventService = $this->getEventService();
        $entityIds = $this->getEntityIdsByHook($entitiesData);
        $webHookEntitiesMap = WebHookEventData::createDataMap($entitiesData);
        $entityCollection = $this->getEntitiesByIds($entityIds);

        if (empty($entityCollection)) {
            return false;
        }
        $notesCollection = new NotesCollection();
        foreach ($entityCollection->toArray() as $entity) {
            /**@var $webhookEventData WebHookEventData */
            $webhookEventData = $webHookEntitiesMap[$entity['id']];
            $filter = new EventsFilter();
            $filter = $filter->setEntityIds([$entity['id']]);
            $filter = $filter->setEntity([$this->getEntityType()]);
            $filter = $filter->setCreatedAt([$webhookEventData->updatedAt]);
            $eventsCollection = $eventService->get($filter);

            $changedInfo = [];
            foreach ($eventsCollection->getIterator() as $event) {
                /** @var $event EventModel */
                $valuesAfter = $event->getValueAfter();
                if (empty($valuesAfter)) {
                    continue;
                }
                $changedFieldModel = $this->fieldsHelper->getFieldData($valuesAfter);
                if (empty($changedFieldModel)) {
                    continue;
                }
                $changedInfo[] = $changedFieldModel;
            }
            if (empty($changedInfo)) {
                continue;
            }
            $changedFieldsText = '';
            foreach ($changedInfo as $changedField) {
                $changedFieldsText .= "{$changedField->fieldName} : {$changedField->value}\n";
            }
            $updateDateValue = date('H:m:s d.m.Y', $webhookEventData->lastModified);
            $message = "Изменены поля: \n {$changedFieldsText} Время изменения: {$updateDateValue}\n";
            $serviceMessageNote = $this->createMessageNote($entity['id'],$message, $webhookEventData->responsibleUserId);
            $notesCollection->add($serviceMessageNote);
        }
        if ($notesCollection->count() > 0) {
            $this->addNotes($notesCollection);
        }

        return true;
    }


    /**
     * @param NotesCollection $notesCollection
     * @return void
     * @throws AmoCRMApiException
     * @throws AmoCRMMissedTokenException
     * @throws AmoCRMoAuthApiException
     * @throws InvalidArgumentException
     */
    public function addNotes(NotesCollection $notesCollection): void
    {
        $entityNotesService = $this->amoCrmClient->notes($this->getEntityType());
        $entityNotesService->add($notesCollection);
    }

    /**
     * @param int $entityId
     * @param string $text
     * @param int $createdBy
     * @param string $service
     * @return CommonNote
     */
    public function createMessageNote(int    $entityId,
                                      string $text,
                                      int    $createdBy = 10520458
    ): CommonNote
    {

        $serviceMessageNote = new CommonNote();
        $serviceMessageNote->setEntityId($entityId)
            ->setText($text)
            ->setCreatedBy($createdBy);

        return $serviceMessageNote;
    }

    /**
     * @param array $entitiesData
     * @return array
     */
    public function getEntityIdsByHook(array $entitiesData): array
    {
        $entityIds = [];
        foreach ($entitiesData as $entityData) {
            try {
                $id = $entityData['id'];
                if (empty($id)) {
                    throw new InvalidRequestEntityData();
                }
                $entityIds[] = $id;

            } catch (InvalidRequestEntityData $exception) {
                $invalidData = json_encode($entityData, JSON_UNESCAPED_UNICODE);
                Log::info("{$exception->getMessage()}: $invalidData");
            }
        }

        return $entityIds;
    }

    /**
     * @param array $ids
     * @return BaseApiCollection|null
     * @throws AmoCRMApiException
     * @throws AmoCRMoAuthApiException
     */
    public function getEntitiesByIds(array $ids): ?BaseApiCollection
    {
        $filter = $this->getFilter()->setIds($ids);
        return $this->entityService->get($filter);
    }
}
