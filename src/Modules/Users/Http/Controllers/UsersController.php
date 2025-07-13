<?php

namespace Modules\Users\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\CustomFieldConfiguration;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class UsersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Breadcrumbs
        $breadcrumbs = [
            [
                'title' => 'Pilots'
            ]
        ];
        return Inertia::render('Users/Pages/Pilots', ['breadcrumbs' => $breadcrumbs]);
    }

    public function jxFetchPilots()
    {
        $pilots = User::fetchAllPilots();
        return response()->json([
            'hasErrors' => false,
            'data' => $pilots
        ]);
    }

    public function jxCreateEditPilot(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'callsign' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'rank_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            $this->errorBag['hasErrors'] = true;
            $this->errorBag['errors'] = $validator->errors();
            return response()->json($this->errorBag);
        }

        $mode = $request->id ? 'edit' : 'create';
        $user = User::createEditPilot($request->all(), $mode);
        if (isset($user['error'])) {
            $this->errorBag['hasErrors'] = true;
            $this->errorBag['errors'] = $user['error'];
            return response()->json($this->errorBag);
        }
        return response()->json([
            'hasErrors' => false,
            'message' => $mode === 'create' ? 'Pilot created successfully' : 'Pilot updated successfully'
        ]);
    }

    public function jxDeletePilot(Request $request)
    {
        $pilot = User::find($request->id);
        if (!$pilot) {
            $this->errorBag['hasErrors'] = true;
            $this->errorBag['errors'] = ['Pilot not found'];
            return response()->json($this->errorBag);
        }
        
        if (!$pilot->delete()) {
            $this->errorBag['hasErrors'] = true;
            $this->errorBag['errors'] = ['Failed to delete pilot'];
            return response()->json($this->errorBag);
        }

        return response()->json([
            'hasErrors' => false,
            'message' => 'Pilot deleted successfully'
        ]);
    }

    public function jxGetUserCustomFields()
    {
        $customFields = CustomFieldConfiguration::getUserCustomFields();
        return response()->json([
            'hasErrors' => false,
            'data' => $customFields
        ]);
    }
}