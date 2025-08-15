<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Facades\Hash;
use App\Models\Rank;
use App\Models\Pirep;
use Illuminate\Support\Facades\DB;
use App\Models\Traits\BelongsToTenant;
use App\Models\Tenant;

class User extends Authenticatable
{
    use HasApiTokens, BelongsToTenant;

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;
    use HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'callsign',
        'status',
        'rank_id',
        'flying_hours',
        'discord_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    const PILOT_STATUS_ACTIVE = '1';

    const PILOT_STATUS_INACTIVE = '0';

    public function rank()
    {
        return $this->belongsTo(Rank::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public static function createEditPilot($data, $mode)
    {
        if ($mode === 'create') {
            $pilot = new User();

            // Validate email
            $validateEmail = User::where('email', $data['email'])->where('tenant_id', app('currentTenant')->id)->first();
            if ($validateEmail) {
                return ['error' => 'Email already exists'];
            }

            // Validate callsign
            $validateCallsign = User::where('callsign', $data['callsign'])->where('tenant_id', app('currentTenant')->id)->first();
            if ($validateCallsign) {
                return ['error' => 'Callsign already exists'];
            }

            $pilot->flying_hours = 0;
            $pilot->status = 1;
            $pilot->password = Hash::make(env('DEFAULT_PASSWORD'));    
        } else {
            $pilot = User::find($data['id']);
            if (!$pilot) {
                return ['error' => 'Pilot not found'];
            }
        }
        $pilot->name = $data['name'];
        $pilot->callsign = $data['callsign'];
        $pilot->email = $data['email'];
        $pilot->rank_id = $data['rank_id'];
        $pilot->tenant_id = app('currentTenant')->id;
        $pilot->created_at = now();
        $pilot->updated_at = now();
        if (!$pilot->save()) {
            return ['error' => 'Failed to save pilot'];
        }

        // Handle custom fields
        if (isset($data['customData'])) {
            foreach ($data['customData'] as $field_key => $value) {
                CustomFieldValues::createCustomFieldValue(CustomFieldValues::SOURCE_TYPE_PILOTS, $pilot->id, $field_key, $value);
            }
        }

        // Handle roles assignment
        if (isset($data['role_id'])) {
            $pilot->assignRole($data['role_id']);
        }

        return $pilot;
    }

    public static function fetchAllPilots()
    {
        $pilots = User::where('tenant_id', app('currentTenant')->id)->get();
        $gridData = [];
        foreach ($pilots as $pilot) {
            $gridData[] = [
                'id' => $pilot->id,
                'name' => $pilot->name,
                'callsign' => $pilot->callsign,
                'email' => $pilot->email,
                'rank_id' => $pilot->rank_id,
                'rank' => Rank::find($pilot->rank_id)->name ?? 'Rank not assigned',
                'role' => $pilot->roles->pluck('name'),
                'role_id' => $pilot->roles->pluck('id')->first(),
                'flying_hours' => round($pilot->flying_hours/60, 2),
                'flights' => Pirep::where('user_id', $pilot->id)->count(),
                'recent_flights' => User::getPilotLatestFlightLogs($pilot->id),
                'status' => $pilot->status,
                'custom_fields' => CustomFieldValues::getAllCustomFieldValues(CustomFieldValues::SOURCE_TYPE_PILOTS, $pilot->id)
            ];
        }
        return $gridData;
    }

    public static function getPilotLatestFlightLogs($pilotId)
    {
        $flights = DB::table('pireps')
            ->leftJoin('routes', 'pireps.route_id', '=', 'routes.id')
            ->leftJoin('events', 'pireps.event_id', '=', 'events.id')
            ->select('routes.origin', 'routes.destination', 'events.event_name', 'events.origin as event_origin', 'events.destination as event_destination')
            ->where('pireps.user_id', $pilotId)
            ->orderBy('pireps.created_at', 'desc')
            ->take(5)
            ->get();
        foreach ($flights as $flight) {
            $flight->origin = $flight->origin ?? $flight->event_origin;
            $flight->destination = $flight->destination ?? $flight->event_destination;
        }
        return $flights;
    }
}
