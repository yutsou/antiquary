<?php

namespace App\CustomFacades;

use Illuminate\Support\Facades\Facade;

class CustomClassFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'customClass';
    }
}
