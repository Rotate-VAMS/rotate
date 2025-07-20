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

class User extends Authenticatable
{
    use HasApiTokens;

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

    public function rank()
    {
        return $this->belongsTo(Rank::class);
    }

    public static function createEditPilot($data, $mode)
    {
        if ($mode === 'create') {
            $pilot = new User();

            // Validate email
            $validateEmail = User::where('email', $data['email'])->first();
            if ($validateEmail) {
                return ['error' => 'Email already exists'];
            }

            // Validate callsign
            $validateCallsign = User::where('callsign', $data['callsign'])->first();
            if ($validateCallsign) {
                return ['error' => 'Callsign already exists'];
            }
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
        $pilot->flying_hours = 0;
        $pilot->status = 1;
        $pilot->password = Hash::make(env('DEFAULT_PASSWORD'));
        $pilot->created_at = now();
        $pilot->updated_at = now();
        $pilot->save();

        // Handle custom fields
        if (isset($data['customData'])) {
            foreach ($data['customData'] as $field_key => $value) {
                CustomFieldValues::createCustomFieldValue(CustomFieldValues::SOURCE_TYPE_PILOTS, $pilot->id, $field_key, $value);
            }
        }

        return $pilot;
    }

    public static function fetchAllPilots()
    {
        $pilots = User::all();
        $gridData = [];
        foreach ($pilots as $pilot) {
            $gridData[] = [
                'id' => $pilot->id,
                'name' => $pilot->name,
                'callsign' => $pilot->callsign,
                'email' => $pilot->email,
                'rank_id' => $pilot->rank_id,
                'rank' => Rank::find($pilot->rank_id)->name,
                'flying_hours' => $pilot->flying_hours,
                'flights' => Pirep::where('user_id', $pilot->id)->count(),
                'recent_flights' => Pirep::where('user_id', $pilot->id)->orderBy('created_at', 'desc')->take(5)->get(),
                'status' => $pilot->status,
                'custom_fields' => CustomFieldValues::getAllCustomFieldValues(CustomFieldValues::SOURCE_TYPE_PILOTS, $pilot->id)
            ];
        }
        return $gridData;
    }
}
