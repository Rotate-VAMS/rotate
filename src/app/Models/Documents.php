<?php

namespace App\Models;

use App\Models\Extended\_Documents;
use App\Models\Traits\BelongsToTenant;

class Documents extends _Documents
{
    use BelongsToTenant;

    protected $table = 'documents';

    protected $fillable = [
        'document_name',
        'document_path',
        'document_type',
        'entity_type',
        'entity_id',
        'is_active',
    ];
}