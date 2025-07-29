<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class Tenant extends Model
{
    protected $fillable = ['name', 'domain'];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function roles()
    {
        return $this->hasMany(Role::class);
    }

    public function permissions()
    {
        return $this->hasMany(Permission::class);
    }

    public function flightTypes()
    {
        return $this->hasMany(FlightType::class);
    }

    public function discordSettings()
    {
        return $this->hasMany(DiscordSettings::class);
    }

    /**
     * Get the base URL for this tenant
     */
    public function getBaseUrl(): string
    {
        $scheme = request()->secure() ? 'https' : 'http';
        $port = request()->getPort();
        
        $url = $scheme . '://' . $this->domain;
        
        // Add port if it's not the default (80 for HTTP, 443 for HTTPS)
        if (($scheme === 'http' && $port !== 80) || ($scheme === 'https' && $port !== 443)) {
            $url .= ':' . $port;
        }
        
        return $url;
    }

    /**
     * Get the storage URL for this tenant
     */
    public function getStorageUrl(string $path = ''): string
    {
        $baseUrl = $this->getBaseUrl();
        $storagePath = '/storage';
        
        if (!empty($path)) {
            $storagePath .= '/' . ltrim($path, '/');
        }
        
        return $baseUrl . $storagePath;
    }
}
