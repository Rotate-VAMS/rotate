<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WikiController extends Controller
{
    public function getWiki()
    {
        return response()->json(config('wiki'));
    }
}
