<?php

namespace App\Models\AmoCrm\Dto;

class ChangedField
{
    public string $fieldName;
    public string $value;

    /**
     * @param string $fieldName
     * @param string $value
     */
    public function __construct(string $fieldName, string $value)
    {
        $this->fieldName = $fieldName;
        $this->value = $value;
    }


}
