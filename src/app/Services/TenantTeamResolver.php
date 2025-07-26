<?php

namespace App\Services;

use Spatie\Permission\Contracts\PermissionsTeamResolver;

class TenantTeamResolver implements PermissionsTeamResolver
{
    protected int|string|null $teamId = null;

    /**
     * Get the team id for teams/groups support, this id is used when querying permissions/roles
     */
    public function getPermissionsTeamId(): int|string|null
    {
        // If a team ID is explicitly set, use it
        if ($this->teamId !== null) {
            return $this->teamId;
        }

        // Get the current tenant ID from the application container
        if (app()->bound('currentTenant')) {
            return app('currentTenant')->id;
        }

        // Fallback to null if no tenant is identified
        return null;
    }

    /**
     * Set the team id for teams/groups support, this id is used when querying permissions/roles
     *
     * @param  int|string|\Illuminate\Database\Eloquent\Model|null  $id
     */
    public function setPermissionsTeamId($id): void
    {
        if ($id instanceof \Illuminate\Database\Eloquent\Model) {
            $id = $id->getKey();
        }
        $this->teamId = $id;
    }
} 