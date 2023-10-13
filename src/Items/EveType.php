<?php

namespace Seat\Services\Items;


use Seat\Services\Contracts\HasTypeID;

class EveType implements HasTypeID
{
    protected int $type_id;

    /**
     * @param int|HasTypeID $type_id
     */
    public function __construct(int | HasTypeID $type_id)
    {
        if($type_id instanceof HasTypeID){
            $type_id = $type_id->getTypeID();
        }

        $this->type_id = $type_id;
    }


    public function getTypeID(): int
    {
        return $this->type_id;
    }
}