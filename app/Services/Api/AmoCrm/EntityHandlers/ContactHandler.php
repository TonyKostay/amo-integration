<?php

namespace App\Services\Api\AmoCrm\EntityHandlers;

use AmoCRM\EntitiesServices\BaseEntity;
use AmoCRM\Exceptions\AmoCRMMissedTokenException;
use AmoCRM\Filters\BaseEntityFilter;
use AmoCRM\Filters\ContactsFilter;
use AmoCRM\Helpers\EntityTypesInterface;

class ContactHandler extends BaseEntityHandler
{
    /**
     * @return BaseEntity
     * @throws AmoCRMMissedTokenException
     */
    function getEntityService(): BaseEntity
    {
        return $this->amoCrmClient->contacts();
    }

    /**
     * @return string
     */
    function getEntityType(): string
    {
        return EntityTypesInterface::CONTACTS;
    }

    /**
     * @return BaseEntityFilter
     */
    public function getFilter(): BaseEntityFilter
    {
        return new ContactsFilter();
    }
}
