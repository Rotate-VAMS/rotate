<?php

namespace Modules\Pirep\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PirepController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $breadcrumbs = [
            [
                'title' => 'Pireps'
            ]
        ];
        return Inertia::render('Pireps/Pages/Pireps', ['breadcrumbs' => $breadcrumbs]);
    }

    public function jxFetchPireps()
    {
        return response()->json(['pireps' => []]);
    }
}
