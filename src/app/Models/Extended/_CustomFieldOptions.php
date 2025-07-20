<?php

namespace App\Models\Extended;

use Illuminate\Database\Eloquent\Model;

class _CustomFieldOptions extends Model
{
    const CUSTOM_VALUES_AS_ROUTE = 1;

    const CUSTOM_VALUES_AS_USERS = 2;

    const CUSTOM_VALUES_AS_EVENTS = 3;

    const CUSTOM_VALUES_AS_FLEET = 4;

    const CUSTOM_VALUES_AS_PIREPS = 5;

    const CUSTOM_VALUES_AS_RANKS = 6;

    const CUSTOM_VALUES_AS_CUSTOM_INPUT = 7;
}
