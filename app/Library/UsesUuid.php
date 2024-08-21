<?php

namespace App\Library;

use Ramsey\Uuid\Uuid;

trait UsesUuid
{
    /**
     * @return string
     */
    public function getKeyName()
    {
        return 'id';  // return 'uuid' if want to use 'uuid' as a foreign key.
    }

    /**
     * @return string
     */
    public function getKeyType()
    {
        return 'int'; // return 'string' if return 'uuid' from the previous method getKeyName
    }

    /**
     * @return false
     */
    public function getIncrementing()
    {
        return true; // return true when returning 'id' from the method getKeyName, Otherwise false;
    }

    /**
     * @param $query
     * @param $uuid
     * @return mixed
     */
    public function scopeUuid($query, $uuid)
    {
        return $query->where($this->getUuidName(), $uuid);
    }

    /**
     * @return string
     */
    public function getUuidName()
    {
        return property_exists($this, 'uuidName') ? $this->uuidName : 'uuid';
    }

    /**
     * @return string
     */
    public function getRouteKeyName()
    {
        return property_exists($this, 'uuidName') ? $this->uuidName : 'uuid';
    }

    /**
     *
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->{$model->getUuidName()} = Uuid::uuid4()->toString();
        });
    }
}
