<?php

namespace Modules\Users\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\CustomFieldConfiguration;
use App\Models\CustomFieldValues;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Exporters\RotatePilotsExporter;
use App\Models\Pirep;
use App\Helpers\RotateConstants;
use function App\Helpers\tenant_cache_remember;
use function App\Helpers\tenant_cache_forget;

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
        $pilots = tenant_cache_remember('users:pilots:all', RotateConstants::SECONDS_IN_ONE_DAY, function () {
            return User::fetchAllPilots();
        });
        $analyticsUsers = tenant_cache_remember('users:pilots:analytics', RotateConstants::SECONDS_IN_ONE_DAY, function () {
            $analyticsUsers = User::where('tenant_id', app('currentTenant')->id)->get();
            return [
                'totalPilots' => $analyticsUsers->count(),
                'activePilots' => $analyticsUsers->where('status', User::PILOT_STATUS_ACTIVE)->count(),
                'totalFlyingHours' => $analyticsUsers->sum('flying_hours'),
                'totalFlyingDistance' => Pirep::getTotalFlyingDistance(),
            ];
        });
        return response()->json([
            'hasErrors' => false,
            'data' => $pilots,
            'analytics' => $analyticsUsers
        ]);
    }

    public function jxCreateEditPilot(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'callsign' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'rank_id' => 'required|integer',
            'role_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            $this->errorBag['hasErrors'] = true;
            $this->errorBag['message'] = $validator->errors()->first();
            return response()->json($this->errorBag);
        }

        $mode = $request->id ? 'edit' : 'create';
        $user = User::createEditPilot($request->all(), $mode);
        if (isset($user['error'])) {
            $this->errorBag['hasErrors'] = true;
            $this->errorBag['message'] = $user['error'];
            return response()->json($this->errorBag);
        }
        tenant_cache_forget('users:pilots:all');
        tenant_cache_forget('users:pilots:analytics');
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
            $this->errorBag['message'] = 'Pilot not found';
            return response()->json($this->errorBag);
        }
        
        if (!$pilot->delete()) {
            $this->errorBag['hasErrors'] = true;
            $this->errorBag['message'] = 'Failed to delete pilot';
            return response()->json($this->errorBag);
        }

        if (!CustomFieldValues::deleteCustomFieldValues(CustomFieldValues::SOURCE_TYPE_PILOTS, $pilot->id)) {
            $this->errorBag['hasErrors'] = true;
            $this->errorBag['message'] = 'Failed to delete custom field values';
            return response()->json($this->errorBag);
        }
        tenant_cache_forget('users:pilots:all');
        tenant_cache_forget('users:pilots:analytics');
        return response()->json([
            'hasErrors' => false,
            'message' => 'Pilot deleted successfully'
        ]);
    }

    public function jxGetUserCustomFields()
    {
        $customFields = CustomFieldConfiguration::getCustomFields(CustomFieldConfiguration::SOURCE_TYPE_PILOTS);
        return response()->json([
            'hasErrors' => false,
            'data' => $customFields
        ]);
    }

    public function jxTogglePilotStatus(Request $request)
    {
        $pilot = User::find($request->id);
        if (!$pilot) {
            $this->errorBag['hasErrors'] = true;
            $this->errorBag['message'] = 'Pilot not found';
            return response()->json($this->errorBag);
        }
        if ($pilot->status == User::PILOT_STATUS_ACTIVE) {
            $pilot->status = User::PILOT_STATUS_INACTIVE;
            $message = 'Pilot deactivated successfully';
        } else {
            $pilot->status = User::PILOT_STATUS_ACTIVE;
            $message = 'Pilot activated successfully';
        }
        if (!$pilot->save()) {
            $this->errorBag['hasErrors'] = true;
            $this->errorBag['message'] = 'Failed to toggle pilot status';
            return response()->json($this->errorBag);
        }
        tenant_cache_forget('users:pilots:all');
        tenant_cache_forget('users:pilots:analytics');
        return response()->json([
            'hasErrors' => false,
            'message' => $message
        ]);
    }

    public function manageProfile()
    {
        $breadcrumbs = [
            [
                'title' => 'Manage Profile'
            ]
        ];
        return Inertia::render('Users/Pages/ManageProfile', ['breadcrumbs' => $breadcrumbs]);
    }

    public function jxUpdatePersonalInfo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
        ]);

        if ($validator->fails()) {
            $this->errorBag['hasErrors'] = true;
            $this->errorBag['message'] = $validator->errors()->first();
            return response()->json($this->errorBag);
        }

        $user = User::find(Auth::user()->id);
        $user->name = $request->full_name;
        $user->email = $request->email;
        if (!$user->save()) {
            $this->errorBag['hasErrors'] = true;
            $this->errorBag['message'] = 'Failed to update personal information';
            return response()->json($this->errorBag);
        }

        return response()->json([
            'hasErrors' => false,
            'message' => 'Personal information updated successfully',
            'data' => [
                'name' => $user->name,
                'email' => $user->email
            ]
        ]);
    }

    public function jxUpdatePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string|max:255',
            'new_password' => 'required|string|max:255',
            'new_password_confirmation' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            $this->errorBag['hasErrors'] = true;
            $this->errorBag['message'] = $validator->errors()->first();
            return response()->json($this->errorBag);
        }

        $user = User::find(Auth::user()->id);
        if (!Hash::check($request->current_password, $user->password)) {
            $this->errorBag['hasErrors'] = true;
            $this->errorBag['message'] = 'Current password is incorrect';
            return response()->json($this->errorBag);
        }

        $user->password = Hash::make($request->new_password);
        if (!$user->save()) {
            $this->errorBag['hasErrors'] = true;
            $this->errorBag['message'] = 'Failed to update password';
            return response()->json($this->errorBag);
        }

        return response()->json([
            'hasErrors' => false,
            'message' => 'Password updated successfully'
        ]);
    }

    public function jxUpdateDiscordInfo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'discord_id' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            $this->errorBag['hasErrors'] = true;
            $this->errorBag['message'] = $validator->errors()->first();
            return response()->json($this->errorBag);
        }

        $user = User::find(Auth::user()->id);
        $user->discord_id = $request->discord_id;
        if (!$user->save()) {
            $this->errorBag['hasErrors'] = true;
            $this->errorBag['message'] = 'Failed to update Discord information';
            return response()->json($this->errorBag);
        }

        return response()->json([
            'hasErrors' => false,
            'message' => 'Discord information updated successfully'
        ]);
    }

    public function jxExportPilots()
    {
        $exporter = new RotatePilotsExporter();
        return $exporter->export();
    }
}