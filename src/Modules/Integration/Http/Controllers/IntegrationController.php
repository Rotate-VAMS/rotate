<?php

namespace Modules\Integration\Http\Controllers;

use App\Helpers\RotateConstants;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Validator;
use App\Models\CustomFieldConfiguration;
use App\Models\CustomFieldOptions;
use function App\Helpers\tenant_cache_remember;
use function App\Helpers\tenant_cache_forget;

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
            'source_type' => 'required|integer',
            'is_required' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            $this->errorBag['hasErrors'] = true;
            $this->errorBag['message'] = $validator->errors();
            return response()->json($this->errorBag);
        }
        $mode = $request->id ? 'edit' : 'create';
        if (!CustomFieldConfiguration::createCustomFieldConfiguration($request->all(), $mode)) {
            $this->errorBag['hasErrors'] = true;
            $this->errorBag['message'] = 'Failed to create custom field configuration';
            return response()->json($this->errorBag);
        }
        tenant_cache_forget('integration:custom_fields:all');

        return response()->json([
            'hasErrors' => false,
            'message' => $mode === 'create' ? 'Custom field configuration created successfully' : 'Custom field configuration updated successfully'
        ]);
    }

    public function jxFetchCustomFields(Request $request)
    {
        $customFieldConfigurations = tenant_cache_remember('integration:custom_fields:all', RotateConstants::SECONDS_IN_ONE_DAY, function () {
            return CustomFieldConfiguration::all();
        });
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
            $this->errorBag['message'] = 'Custom field configuration not found';
            return response()->json($this->errorBag);
        }

        if (!CustomFieldConfiguration::deleteCustomFieldConfiguration($request->id)) {
            $this->errorBag['hasErrors'] = true;
            $this->errorBag['message'] = 'Failed to delete custom field configuration';
            return response()->json($this->errorBag);
        }
        tenant_cache_forget('integration:custom_fields:all');

        return response()->json([
            'hasErrors' => false,
            'message' => 'Custom field configuration deleted successfully'
        ]);
    }

    public function jxCreateEditCustomFieldOptions(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'field_id' => 'required|integer',
            'dropdown_value_type' => 'required|integer',
            'options' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            $this->errorBag['hasErrors'] = true;
            $this->errorBag['message'] = $validator->errors();
            return response()->json($this->errorBag);
        }
        $mode = $request->id ? 'edit' : 'create';
        $cfc = CustomFieldConfiguration::find($request->field_id);
        if (!$cfc) {
            $this->errorBag['hasErrors'] = true;
            $this->errorBag['message'] = 'Custom field configuration not found';
            return response()->json($this->errorBag);
        }
        $cfc->dropdown_value_type = $request->dropdown_value_type;
        if (!$cfc->save()) {
            $this->errorBag['hasErrors'] = true;
            $this->errorBag['message'] = 'Failed to update custom field configuration';
            return response()->json($this->errorBag);
        }
        if ($request->dropdown_value_type == CustomFieldOptions::CUSTOM_VALUES_AS_CUSTOM_INPUT) {
            if (!CustomFieldOptions::createCustomFieldOption($request->all(), $mode)) {
                $this->errorBag['hasErrors'] = true;
                $this->errorBag['message'] = 'Failed to create custom field option';
                return response()->json($this->errorBag);
            }
        }
        $cacheKey = 'integration:custom_field_options:' . $request->field_id;
        tenant_cache_forget($cacheKey);

        return response()->json([
            'hasErrors' => false,
            'message' => $mode === 'create' ? 'Custom field option created successfully' : 'Custom field option updated successfully'
        ]);
    }

    public function jxFetchCustomFieldOptions(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'field_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            $this->errorBag['hasErrors'] = true;
            $this->errorBag['message'] = $validator->errors();
            return response()->json($this->errorBag);
        }

        $cacheKey = 'integration:custom_field_options:' . $request->field_id;
        $customFieldOptions = tenant_cache_remember($cacheKey, RotateConstants::SECONDS_IN_ONE_DAY, function () use ($request) {
            return CustomFieldOptions::fetchCustomFieldOptions($request->field_id);
        });
        return response()->json([
            'hasErrors' => false,
            'data' => $customFieldOptions
        ]);
    }

    public function jxDeleteCustomFieldOption(Request $request)
    {
        $customFieldOption = CustomFieldOptions::where('field_id', $request->field_id)->where('label', $request->value)->first();
        if (!$customFieldOption) {
            $this->errorBag['hasErrors'] = true;
            $this->errorBag['message'] = 'Custom field option not found';
            return response()->json($this->errorBag);
        }

        if (!$customFieldOption->delete()) {
            $this->errorBag['hasErrors'] = true;
            $this->errorBag['message'] = 'Failed to delete custom field option';
            return response()->json($this->errorBag);
        }
        $cacheKey = 'integration:custom_field_options:' . $request->field_id;
        tenant_cache_forget($cacheKey);

        return response()->json([
            'hasErrors' => false,
            'message' => 'Custom field option deleted successfully'
        ]);
    }
}
