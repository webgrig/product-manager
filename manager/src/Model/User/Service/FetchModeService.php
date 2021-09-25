<?php

namespace App\Model\User\Service;

class FetchModeService
{
    private function __construct() {}
    public static function getNetworks(string $viewer, array $data)
    {
        $networks = [];
        foreach ($data as $value)
        {
            $viewer = new $viewer;
            foreach ($value as $k => $v)
            {
                $viewer->$k = $v;
            }
            $networks[] = $viewer;
        }
        return $networks;
    }

}