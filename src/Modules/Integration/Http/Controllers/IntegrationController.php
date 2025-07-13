<?php

namespace Modules\Integration\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Validator;
use App\Models\CustomFieldConfiguration;

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

    public function jxCreateEditCustomFields(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'field_name' => 'required|string|max:255',
            'field_description' => 'required|string|max:255',
            'data_type' => 'required|integer',
            'aggregation_type' => 'required|integer',
            'source_type' => 'required|integer',
            'is_required' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            $this->errorBag['hasErrors'] = true;
            $this->errorBag['errors'] = $validator->errors();
            return response()->json($this->errorBag);
        }
        $mode = $request->id ? 'edit' : 'create';
        if (!CustomFieldConfiguration::createCustomFieldConfiguration($request->all(), $mode)) {
            $this->errorBag['hasErrors'] = true;
            $this->errorBag['errors'] = ['Failed to create custom field configuration'];
            return response()->json($this->errorBag);
        }

        return response()->json([
            'hasErrors' => false,
            'message' => $mode === 'create' ? 'Custom field configuration created successfully' : 'Custom field configuration updated successfully'
        ]);
    }

    public function jxFetchCustomFields(Request $request)
    {
        $customFieldConfigurations = CustomFieldConfiguration::all();
        return response()->json([
            'hasErrors' => false,
            'data' => $customFieldConfigurations
        ]);
    }

    public function jxDeleteCustomField(Request $request)
    {
        $customFieldConfiguration = CustomFieldConfiguration::find($request->id);
        if (!$customFieldConfiguration) {
            $this->errorBag['hasErrors'] = true;
            $this->errorBag['errors'] = ['Custom field configuration not found'];
            return response()->json($this->errorBag);
        }

        if (!CustomFieldConfiguration::deleteCustomFieldConfiguration($request->id)) {
            $this->errorBag['hasErrors'] = true;
            $this->errorBag['errors'] = ['Failed to delete custom field configuration'];
            return response()->json($this->errorBag);
        }

        return response()->json([
            'hasErrors' => false,
            'message' => 'Custom field configuration deleted successfully'
        ]);
    }
}
