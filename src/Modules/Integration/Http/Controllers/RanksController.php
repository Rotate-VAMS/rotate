<?php

namespace Modules\Integration\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Rank;

class RanksController extends Controller
{
    public function jxCreateEditRank(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'rank_name' => 'required|string|max:255',
            'min_hours' => 'required|integer',
        ]);

        if ($validator->fails()) {
            $this->errorBag['hasErrors'] = true;
            $this->errorBag['errors'] = $validator->errors();
            return response()->json($this->errorBag);
        }

        $mode = $request->id ? 'edit' : 'create';
        if (!Rank::createEditRank($request->all(), $mode)) {
            $this->errorBag['hasErrors'] = true;
            $this->errorBag['errors'] = ['Failed to create rank'];
            return response()->json($this->errorBag);
        }

        return response()->json([
            'hasErrors' => false,
            'message' => $mode === 'create' ? 'Rank created successfully' : 'Rank updated successfully'
        ]);
    }

    public function jxFetchRanks(Request $request)
    {
        $ranks = Rank::all();
        return response()->json([
            'hasErrors' => false,
            'data' => $ranks
        ]);
    }

    public function jxDeleteRank(Request $request)
    {
        $rank = Rank::find($request->id);
        if (!$rank) {
            $this->errorBag['hasErrors'] = true;
            $this->errorBag['errors'] = ['Rank not found'];
            return response()->json($this->errorBag);
        }
    
        if (!$rank->delete()) {
            $this->errorBag['hasErrors'] = true;
            $this->errorBag['errors'] = ['Failed to delete rank'];
            return response()->json($this->errorBag);
        }
    
        return response()->json([
            'hasErrors' => false,
            'message' => 'Rank deleted successfully'
        ]);
    }
}
