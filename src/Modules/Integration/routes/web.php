<?php

use Illuminate\Support\Facades\Route;
use Modules\Integration\Http\Controllers\IntegrationController;
use Modules\Integration\Http\Controllers\RanksController;
use Modules\Integration\Http\Controllers\FleetsController;
use Modules\Integration\Http\Controllers\FlightTypesController;
use Modules\Integration\Http\Controllers\RolesController;

Route::middleware(['auth', 'verified', 'pilot.active'])->group(function () {
    // Custom Fields
    Route::group(['prefix' => 'settings', 'controller' => IntegrationController::class], function () {
        Route::get('/', 'index')->name('integrations.settings');
        Route::post('jxCreateEditCustomFields', 'jxCreateEditCustomFields')->name('integrations.settings.jxCreateEditCustomFields');
        Route::get('jxFetchCustomFields', 'jxFetchCustomFields')->name('integrations.settings.jxFetchCustomFields');
        Route::post('jxDeleteCustomField', 'jxDeleteCustomField')->name('integrations.settings.jxDeleteCustomField');
        
        // Custom Field Options
        Route::post('jxCreateEditCustomFieldOptions', 'jxCreateEditCustomFieldOptions')->name('integrations.settings.jxCreateEditCustomFieldOptions');
        Route::get('jxFetchCustomFieldOptions', 'jxFetchCustomFieldOptions')->name('integrations.settings.jxFetchCustomFieldOptions');
        Route::post('jxDeleteCustomFieldOption', 'jxDeleteCustomFieldOption')->name('integrations.settings.jxDeleteCustomFieldOption');
    });

    // Ranks
    Route::group(['prefix' => 'settings', 'controller' => RanksController::class], function () {
        Route::post('jxCreateEditRank', 'jxCreateEditRank')->name('integrations.settings.jxCreateEditRank');
        Route::get('jxFetchRanks', 'jxFetchRanks')->name('integrations.settings.jxFetchRanks');
        Route::post('jxDeleteRank', 'jxDeleteRank')->name('integrations.settings.jxDeleteRank');
    });

    // Fleets
    Route::group(['prefix' => 'settings', 'controller' => FleetsController::class], function () {
        Route::post('jxCreateEditFleet', 'jxCreateEditFleet')->name('integrations.settings.jxCreateEditFleet');
        Route::get('jxFetchFleets', 'jxFetchFleets')->name('integrations.settings.jxFetchFleets');
        Route::post('jxDeleteFleet', 'jxDeleteFleet')->name('integrations.settings.jxDeleteFleet');
        Route::get('jxFetchAllFleets', 'jxFetchAllFleets')->name('integrations.settings.jxFetchAllFleets');
    });

    // Flight Types
    Route::group(['prefix' => 'settings', 'controller' => FlightTypesController::class], function () {
        Route::post('jxCreateEditFlightType', 'jxCreateEditFlightType')->name('integrations.settings.jxCreateEditFlightType');
        Route::get('jxFetchFlightTypes', 'jxFetchFlightTypes')->name('integrations.settings.jxFetchFlightTypes');
        Route::post('jxDeleteFlightType', 'jxDeleteFlightType')->name('integrations.settings.jxDeleteFlightType');
    });

    // Roles and RBAC
    Route::group(['prefix' => 'settings', 'controller' => RolesController::class], function () {
        // Roles
        Route::post('jxCreateEditRole', 'jxCreateEditRole')->name('integrations.settings.jxCreateEditRole');
        Route::get('jxFetchRoles', 'jxFetchRoles')->name('integrations.settings.jxFetchRoles');
        Route::post('jxDeleteRole', 'jxDeleteRole')->name('integrations.settings.jxDeleteRole');

        // Permissions
        Route::get('jxFetchPermissions', 'jxFetchPermissions')->name('integrations.settings.jxFetchPermissions');
        Route::post('jxGiveRolePermissions', 'jxGiveRolePermissions')->name('integrations.settings.jxGiveRolePermissions');
        Route::post('jxRevokeRolePermissions', 'jxRevokeRolePermissions')->name('integrations.settings.jxRevokeRolePermissions');
    });
});        