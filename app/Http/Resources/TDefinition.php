<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TDefinition extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        // return parent::toArray($request);
        return [
            'id'                => $this->id,
            'tableName'         => $this->tables->table_name,
            'tableNameAlias'    => $this->tables->table_name_alias,
            'columnName'        => $this->column_name,
            'dataType'          => $this->data_type,
            'length'            => $this->length,
            'maximumNumber'     => $this->maximum_number,
            'decimalPart'       => $this->decimal_part,
            'validation'        => $this->validation,
            'createdAt'         => $this->created_at,
            'updatedAt'         => $this->updated_at,
        ];
    }
}
