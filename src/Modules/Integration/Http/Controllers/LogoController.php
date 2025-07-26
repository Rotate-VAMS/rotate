<?php

namespace Modules\Integration\Http\Controllers;

use App\Helpers\RotateConstants;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Documents;
use function App\Helpers\tenant_cache_remember;
use function App\Helpers\tenant_cache_forget;

class LogoController extends Controller
{
    public function jxCreateEditLogo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'logo' => 'required',
            'logo.document_name' => 'required|string',
            'logo.document_type' => 'required|integer',
            'logo.document_data' => 'required|string',
        ]);

        if ($validator->fails()) {
            $this->errorBag['hasErrors'] = true;
            $this->errorBag['message'] = $validator->errors()->first();
            return response()->json($this->errorBag);
        }

        $logo = Documents::createEditDocument(Documents::DOCUMENT_TYPE_LOGO, RotateConstants::CONSTANT_FOR_ONE, $request->logo);
        if (isset($logo['error'])) {
            $this->errorBag['hasErrors'] = true;
            $this->errorBag['message'] = $logo['error'];
            return response()->json($this->errorBag);
        }
        tenant_cache_forget('integration:logo');
        return response()->json(['hasErrors' => false, 'message' => 'Logo created successfully']);
    }

    public function jxFetchLogo(Request $request)
    {
        $default = false;
        $logo = tenant_cache_remember('integration:logo', 1800, function () {
            $logo = Documents::fetchDocument(Documents::DOCUMENT_TYPE_LOGO, RotateConstants::CONSTANT_FOR_ONE);
            return $logo;
        });
        if (isset($logo['error'])) {
            $logo = null;
            $default = true;
        }
        return response()->json(['hasErrors' => false, 'default' => $default, 'data' => $logo]);
    }

    public function jxDeleteLogo(Request $request)
    {
        $deletedLogo = Documents::deleteDocument(Documents::DOCUMENT_TYPE_LOGO, RotateConstants::CONSTANT_FOR_ONE);
        if (isset($deletedLogo['error'])) {
            $this->errorBag['hasErrors'] = true;
            $this->errorBag['message'] = $deletedLogo['error'];
            return response()->json($this->errorBag);
        }
        tenant_cache_forget('integration:logo');
        return response()->json(['hasErrors' => false, 'message' => 'Logo deleted successfully']);
    }
}