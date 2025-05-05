<?php

namespace App\Support\Statuses;

abstract class AbstractStatuses
{
    public static function statuses()
    {
        return static::$statuses;
    }

    /**
     * Returns an array of statuses to populate dropdown list.
     *
     * @return array
     */
    public static function lists()
    {
        $statuses = static::$statuses;

        unset($statuses[0]);

        foreach ($statuses as $key => $status) {
            $statuses[$key] = trans('ip.' . $status);
        }

        return $statuses;
    }

    public static function listsAllFlat()
    {
        $statuses = [];

        foreach (static::$statuses as $status) {
            $statuses[$status] = trans('ip.' . $status);
        }

        return $statuses;
    }

    /**
     * Returns the status key.
     *
     * @param string $value
     *
     * @return int
     */
    public static function getStatusId($value)
    {
        return array_search($value, static::$statuses);
    }
}
