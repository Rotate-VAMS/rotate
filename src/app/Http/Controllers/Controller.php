<?php

namespace App\Http\Controllers;

abstract class Controller
{
    public $errorBag;
    public $currentTenant;

    public function __construct()
    {
        $this->errorBag = ['hasErrors' => false, 'message' => ''];
        $this->currentTenant = app('currentTenant');
    }
}
