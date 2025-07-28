<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use App\Models\Traits\BelongsToTenant;

class DiscordEventMessage extends Model
{
    use BelongsToTenant;

    protected $table = 'discord_event_messages';
    
    protected $fillable = [
        'event_id',
        'discord_message_id',
        'discord_channel_id'
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Store Discord message mapping for current tenant
     */
    public static function storeMessageMapping(int $eventId, string $messageId, string $channelId): bool
    {
        try {
            return self::create([
                'event_id' => $eventId,
                'discord_message_id' => $messageId,
                'discord_channel_id' => $channelId,
                'tenant_id' => app('currentTenant')->id
            ])->save();
        } catch (\Exception $e) {
            Log::error('Failed to store Discord message mapping: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Find event by Discord message ID for current tenant
     */
    public static function findEventByMessageId(string $messageId): ?Event
    {
        $mapping = self::where('discord_message_id', $messageId)
            ->where('tenant_id', app('currentTenant')->id)
            ->first();
        return $mapping ? $mapping->event : null;
    }
} 