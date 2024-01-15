<?php

namespace App\Helpers\AmoCrm;

use AmoCRM\EntitiesServices\CustomFields;
use AmoCRM\Exceptions\AmoCRMApiException;
use AmoCRM\Exceptions\AmoCRMoAuthApiException;
use App\Models\AmoCrm\Dto\ChangedField;
use function Laravel\Prompts\select;

class FieldsHelper
{
    private CustomFields $customFieldService;
    private static array $fieldNamesMap = [
        'company_name' => 'Компания',
        'name' => 'Название',
        'sale' => 'Бюджет',
    ];

    private static array $untraceableFields = [
        'note'
    ];

    /**
     * @param CustomFields $customFieldService
     */
    public function __construct(CustomFields $customFieldService)
    {
        $this->customFieldService = $customFieldService;
    }


    /**
     * Здесь должна быть более детальная логика обработки полей.
     * Не могу себе позволить выделить больше времени на это.
     *
     * @param array $eventFieldData
     * @return ChangedField|null
     * @throws AmoCRMApiException
     * @throws AmoCRMoAuthApiException
     */
    public function getFieldData(array $eventFieldData): ?ChangedField
    {
        $value = reset($eventFieldData);
        if (array_key_exists('custom_field_value', $value)) {
            $field = $this->customFieldService->getOne($value['custom_field_value']['field_id']);
            $fieldName = $field->getName();
            $fieldValue = reset($value)['text'];

            return new ChangedField($fieldName, $fieldValue);
        }
        $type =  array_key_first($value);
        if (in_array($type, self::$untraceableFields)) {
            return null;
        }
        $fieldValueArray = reset($value);
        $fieldCode = array_key_first($fieldValueArray);

        $fieldName = self::$fieldNamesMap[$fieldCode] ?? 'null';
        $fieldValue = $fieldValueArray[$fieldCode];

        return new ChangedField($fieldName, $fieldValue);

    }
}
