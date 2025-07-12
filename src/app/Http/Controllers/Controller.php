<?php

namespace App\Http\Controllers;

abstract class Controller
{
    public $errorBag;

    public function __construct()
    {
        $this->errorBag = ['hasErrors' => false, 'errors' => []];
    }
}
