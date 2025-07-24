<?php

namespace App\Services;

use Discord\Parts\Channel\Message;
use App\Services\RotateDiscordBotSessionManager;
use App\Models\User;
use App\Models\Route;
use App\Models\FlightType;
use App\Models\CustomFieldConfiguration;
use Illuminate\Support\Facades\Auth;

class RotateDiscordBotService
{
    public function handle(Message $message): void
    {
        $userId = $message->author->id;
        $content = trim($message->content);

        // Only respond to !pirep in public
        if ($content === '!pirep' && !$message->channel->is_private) {
            $user = User::where('discord_id', $userId)->first();
            if (!$user) {
                $message->author->sendMessage("❌ Your Discord account is not linked to a Rotate account. Please link your account first.");
                return;
            }
            $message->author->sendMessage("🛫 Let's start filing your PIREP.\nPlease enter your **origin ICAO code** (e.g., VABB):");
            RotateDiscordBotSessionManager::start($userId);
            return;
        }

        // Handle DM step-by-step session
        if ($message->channel->is_private && RotateDiscordBotSessionManager::exists($userId)) {
            $this->handlePirepSession($message, $userId, $content);
            return;
        }

        // Optional: allow !ping in public or private
        if ($content === '!ping') {
            $message->channel->sendMessage('🏓 Pong!');
        }
    }

    protected function handlePirepSession(Message $message, string $userId, string $content): void
    {
        $user = User::where('discord_id', $userId)->first();
        if (!$user) {
            $message->channel->sendMessage("❌ Your Discord account is not linked to a Rotate account. Please link your account first.");
            RotateDiscordBotSessionManager::cancel($userId);
            return;
        }
        Auth::login($user);
        $step = RotateDiscordBotSessionManager::getStep($userId);
        $data = RotateDiscordBotSessionManager::getData($userId);

        switch ($step) {
            case 'origin':
                RotateDiscordBotSessionManager::update($userId, 'origin', strtoupper($content), 'destination');
                $message->channel->sendMessage("✅ Got it.\nPlease enter your **destination ICAO code**:");
                break;

            case 'destination':
                RotateDiscordBotSessionManager::update($userId, 'destination', strtoupper($content), 'route');
                $origin = $data['origin'] ?? strtoupper($content);
                $destination = strtoupper($content);
                $eligibleRoutes = Route::where('origin', $origin)
                    ->where('destination', $destination)
                    ->where('status', 1)
                    ->where('min_rank_id', '<=', $user->rank_id)
                    ->get();
                if ($eligibleRoutes->isEmpty() || $eligibleRoutes->count() == 0) {
                    $message->channel->sendMessage("❌ No eligible routes found for this origin/destination and your rank. Session cancelled.");
                    RotateDiscordBotSessionManager::cancel($userId);
                    return;
                }
                $routeList = "Please select a route by number:\n";
                $routeIds = [];
                foreach ($eligibleRoutes as $i => $route) {
                    $routeList .= ($i+1) . ". **{$route->flight_number}** ({$route->origin} → {$route->destination})\n";
                    $routeIds[] = (string)$route->id;
                }
                RotateDiscordBotSessionManager::update($userId, 'eligible_routes', json_encode($routeIds), 'route');
                $message->channel->sendMessage($routeList);
                break;

            case 'route':
                $eligibleRoutes = json_decode($data['eligible_routes'] ?? '[]', true);
                $routeIndex = (int)$content - 1;
                if (!isset($eligibleRoutes[$routeIndex])) {
                    $message->channel->sendMessage("❌ Invalid selection. Please enter a valid route number.");
                    return;
                }
                $routeId = $eligibleRoutes[$routeIndex];
                RotateDiscordBotSessionManager::update($userId, 'route_id', $routeId, 'hours');
                $message->channel->sendMessage("✈️ Great.\nEnter **flight time hours** (e.g., 2):");
                break;

            case 'hours':
                if (!is_numeric($content) || (int)$content < 0 || (int)$content > 23) {
                    $message->channel->sendMessage("❌ Please enter a valid number of hours (0–23).");
                    return;
                }
                RotateDiscordBotSessionManager::update($userId, 'hours', $content, 'minutes');
                $message->channel->sendMessage("🕓 Now enter **flight time minutes** (e.g., 30):");
                break;

            case 'minutes':
                if (!is_numeric($content) || (int)$content < 0 || (int)$content > 59) {
                    $message->channel->sendMessage("❌ Please enter a valid number of minutes (0–59).");
                    return;
                }
                RotateDiscordBotSessionManager::update($userId, 'minutes', $content, 'flight_type');
                $flightTypes = FlightType::all();
                $ftList = "Please select a flight type by number:\n";
                $ftIds = [];
                foreach ($flightTypes as $i => $ft) {
                    $ftList .= ($i+1) . ". **{$ft->flight_type}**\n";
                    $ftIds[] = (string)$ft->id;
                }
                RotateDiscordBotSessionManager::update($userId, 'flight_types', json_encode($ftIds), 'flight_type');
                $message->channel->sendMessage($ftList);
                break;

            case 'flight_type':
                $flightTypes = json_decode($data['flight_types'] ?? '[]', true);
                $ftIndex = (int)$content - 1;
                if (!isset($flightTypes[$ftIndex])) {
                    $message->channel->sendMessage("❌ Invalid selection. Please enter a valid flight type number.");
                    return;
                }
                $flightTypeId = $flightTypes[$ftIndex];
                RotateDiscordBotSessionManager::update($userId, 'flight_type_id', $flightTypeId, 'custom_fields');
                // Prepare custom fields
                $customFields = CustomFieldConfiguration::getCustomFields(CustomFieldConfiguration::SOURCE_TYPE_PIREPS);
                $requiredFields = [];
                foreach ($customFields as $field) {
                    if ($field->is_required) {
                        $requiredFields[] = [
                            'id' => (string)$field->id,
                            'field_key' => (string)$field->field_key,
                            'field_name' => (string)$field->field_name,
                            'data_type' => (string)$field->data_type,
                            'options' => isset($field->options) ? json_encode($field->options) : null
                        ];
                    }
                }
                if (empty($requiredFields)) {
                    RotateDiscordBotSessionManager::update($userId, 'custom_fields', json_encode([]), 'complete');
                    $this->finalizePirep($message, $userId);
                    return;
                }
                RotateDiscordBotSessionManager::update($userId, 'custom_fields', json_encode($requiredFields), 'custom_field_0');
                $this->promptCustomField($message, $userId, 0);
                break;

            default:
                if (strpos($step, 'custom_field_') === 0) {
                    $index = (int)str_replace('custom_field_', '', $step);
                    $customFields = json_decode($data['custom_fields'] ?? '[]', true);
                    if (!isset($customFields[$index])) {
                        // All custom fields done
                        RotateDiscordBotSessionManager::update($userId, 'custom_fields_data', json_encode($data['custom_fields_data'] ?? []), 'complete');
                        $this->finalizePirep($message, $userId);
                        return;
                    }
                    $field = $customFields[$index];
                    // Validate and store input
                    $customFieldsData = json_decode($data['custom_fields_data'] ?? '{}', true);
                    $value = $content;
                    if ($field['data_type'] == CustomFieldConfiguration::DATA_TYPE_DROPDOWN) {
                        $options = isset($field['options']) ? json_decode($field['options'], true) : [];
                        $optIndex = (int)$content - 1;
                        if (!isset($options[$optIndex])) {
                            $msg = "❌ Invalid selection. Please enter a valid option number.\n";
                            foreach ($options as $i => $opt) {
                                $msg .= ($i+1) . ". {$opt}\n";
                            }
                            $message->channel->sendMessage($msg);
                            return;
                        }
                        $value = $options[$optIndex];
                    }
                    $customFieldsData[$field['field_key']] = $value;
                    RotateDiscordBotSessionManager::update($userId, 'custom_fields_data', json_encode($customFieldsData), 'custom_field_' . ($index + 1));
                    $this->promptCustomField($message, $userId, $index + 1);
                }
                break;
        }
    }

    protected function promptCustomField(Message $message, string $userId, int $index): void
    {
        $data = RotateDiscordBotSessionManager::getData($userId);
        $customFields = json_decode($data['custom_fields'] ?? '[]', true);
        if (!isset($customFields[$index])) {
            // All custom fields done
            RotateDiscordBotSessionManager::update($userId, 'custom_fields_data', $data['custom_fields_data'] ?? json_encode([]), 'complete');
            $this->finalizePirep($message, $userId);
            return;
        }
        $field = $customFields[$index];
        $prompt = "Please enter **{$field['field_name']}**";
        if ($field['data_type'] == CustomFieldConfiguration::DATA_TYPE_DROPDOWN && !empty($field['options'])) {
            $options = json_decode($field['options'], true);
            $prompt .= " (select by number):\n";
            foreach ($options as $i => $opt) {
                $prompt .= ($i+1) . ". {$opt}\n";
            }
        }
        $message->channel->sendMessage($prompt);
    }

    protected function finalizePirep(Message $message, string $userId): void
    {
        $data = RotateDiscordBotSessionManager::getData($userId);
        $user = User::where('discord_id', $userId)->first();
        if (!$user) {
            $message->channel->sendMessage("❌ User not found. Session cancelled.");
            RotateDiscordBotSessionManager::cancel($userId);
            return;
        }
        Auth::login($user);
        $pirepData = [
            'route_id' => $data['route_id'],
            'flight_time_hours' => $data['hours'],
            'flight_time_minutes' => $data['minutes'],
            'flight_type_id' => $data['flight_type_id'],
            'customData' => json_decode($data['custom_fields_data'] ?? '{}', true),
        ];
        $result = \App\Models\Pirep::createEditPirep($pirepData, 'create');
        if (isset($result['error'])) {
            $message->channel->sendMessage("❌ Failed to save PIREP: " . $result['error']);
        } else {
            $message->channel->sendMessage("✅ Your PIREP has been recorded successfully!");
        }
        RotateDiscordBotSessionManager::cancel($userId);
    }
}