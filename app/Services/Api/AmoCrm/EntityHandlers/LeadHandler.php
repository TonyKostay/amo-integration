<?php

namespace App\Services\Api\AmoCrm\EntityHandlers;

use AmoCRM\EntitiesServices\BaseEntity;
use AmoCRM\Exceptions\AmoCRMMissedTokenException;
use AmoCRM\Filters\BaseEntityFilter;
use AmoCRM\Filters\LeadsFilter;
use AmoCRM\Helpers\EntityTypesInterface;

class LeadHandler extends BaseEntityHandler
{
    /**
     * @return BaseEntity
     * @throws AmoCRMMissedTokenException
     */
    function getEntityService(): BaseEntity
    {
        return $this->amoCrmClient->leads();
    }

    /**
     * @return string
     */
    function getEntityType(): string
    {
        return EntityTypesInterface::LEADS;
    }

    /**
     * @return BaseEntityFilter
     */
    public function getFilter(): BaseEntityFilter
    {
        return new LeadsFilter();
    }
}
