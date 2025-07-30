<?php

namespace App\Http\Controllers;

abstract class Controller
{
    public $errorBag;
    public $currentTenant;

    public function __construct()
    {
        $this->errorBag = ['hasErrors' => false, 'message' => ''];
        if (strpos(request()->getUri(), '/api/') === false) {
            $this->currentTenant = app('currentTenant');
        }
    }
}
