<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use App\Models\Traits\BelongsToTenant;

class DiscordPirepMessage extends Model
{
    use BelongsToTenant;

    protected $table = 'discord_pirep_messages';
    
    protected $fillable = [
        'pirep_id',
        'discord_message_id',
        'discord_channel_id'
    ];

    public function pirep()
    {
        return $this->belongsTo(Pirep::class);
    }

    /**
     * Store Discord message mapping for current tenant
     */
    public static function storeMessageMapping(int $pirepId, string $messageId, string $channelId): bool
    {
        try {
            return self::create([
                'pirep_id' => $pirepId,
                'discord_message_id' => $messageId,
                'discord_channel_id' => $channelId,
                'tenant_id' => app('currentTenant')->id
            ])->save();
        } catch (\Exception $e) {
            Log::error('Failed to store Discord PIREP message mapping: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Find pirep by Discord message ID for current tenant
     */
    public static function findPirepByMessageId(string $messageId): ?Pirep
    {
        $mapping = self::where('discord_message_id', $messageId)
            ->where('tenant_id', app('currentTenant')->id)
            ->first();
        return $mapping ? $mapping->pirep : null;
    }
}
