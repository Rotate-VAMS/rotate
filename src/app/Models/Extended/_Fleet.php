<?php

namespace App\Models\Extended;

use App\Models\Fleet;
use Illuminate\Database\Eloquent\Model;

class _Fleet extends Model
{
    const FLEET_DATA_FILE = 'public/asset/helpers/fleet_data.csv';

    public static function createEditFleet($data, $mode)
    {
        if ($mode === 'edit') {
            $fleet = Fleet::find($data['id']);
            if (!$fleet) {
                return ['hasErrors' => true, 'errors' => ['Fleet not found']];
            }
            $fleet->minimum_rank = $data['minimum_rank'];
            $fleet->save();
        } else {
            foreach ($data['selected_fleet'] as $selectedFleet) {
                $fleet = new Fleet();
                $fleetDetails = explode(' - ', $selectedFleet);
                $livery = $fleetDetails[0];
                $aircraft = $fleetDetails[1];
    
                $fleet->livery = $livery;
                $fleet->aircraft = $aircraft;
                if (isset($data['minimum_rank']) && $data['minimum_rank'] !== '') {
                    $fleet->minimum_rank = $data['minimum_rank'];
                } else {
                    $fleet->minimum_rank = null;
                }
                $fleet->save();
            }
        }

        return ['hasErrors' => false, 'message' => $mode === 'create' ? 'Fleet created successfully' : 'Fleet updated successfully'];
    }
}
