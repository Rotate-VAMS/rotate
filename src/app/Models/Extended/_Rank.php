<?php

namespace App\Models\Extended;

use App\Models\Rank;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class _Rank extends Model
{
    use SoftDeletes;

    public static function createEditRank($data, $mode)
    {
        if ($mode === 'create') {
            $rank = new Rank();
        } else {
            $rank = Rank::find($data['id']);
            if (!$rank) {
                return false;
            }
        }
        $rank->name = $data['rank_name'];
        $rank->min_hours = $data['min_hours'];
        $rank->created_at = now();
        $rank->updated_at = now();
        if (!$rank->save()) {
            return false;
        }
        return $rank;
    }

}
