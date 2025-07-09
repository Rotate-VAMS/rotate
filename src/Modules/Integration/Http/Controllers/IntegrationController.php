<?php

namespace Modules\Integration\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class IntegrationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $breadcrumbs = [
            [
                'title' => 'Integrations'
            ]
        ];
        return Inertia::render('Integration/Pages/Settings', ['breadcrumbs' => $breadcrumbs]);
    }
}
