<template>
  <div class="max-w-4xl mx-auto p-3 sm:p-6">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 sm:mb-8">
      <div class="flex items-center gap-2 sm:gap-3 mb-4 sm:mb-0">
        <div class="w-8 h-8 sm:w-10 sm:h-10 bg-gradient-to-r from-indigo-500 to-purple-500 rounded-lg flex items-center justify-center">
          <BotIcon class="w-5 h-5 sm:w-6 sm:h-6 text-white" />
        </div>
        <div>
          <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-900">Discord Integration</h1>
          <p class="text-gray-600 text-xs sm:text-sm lg:text-base">Connect your Discord server to file PIREPs and receive event notifications.</p>
        </div>
      </div>
    </div>

    <!-- Connect Discord Server Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6 mb-6 sm:mb-8">
      <div class="flex flex-col sm:flex-row sm:items-center gap-4">
        <div class="flex-1">
          <h2 class="text-lg sm:text-xl font-bold text-gray-900 mb-2">Connect Discord Server</h2>
          <p class="text-gray-600 text-sm sm:text-base mb-4">File PIREPs and receive event notifications from your Discord server! Streamline your operations with seamless integration.</p>
          <button 
            @click="openDiscordInvite"
            class="w-full sm:w-auto btn-primary bg-gradient-to-r from-indigo-500 to-purple-500 hover:from-indigo-600 hover:to-purple-600 text-white text-bold rounded-md px-4 py-3 sm:py-2 flex items-center justify-center sm:justify-start gap-2"
          >
            <BotIcon class="w-5 h-5" />
            <span class="text-sm sm:text-base">Invite Rotate Bot to your server</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Event Notifications Configuration -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6 mb-6 sm:mb-8">
      <div class="flex items-center gap-2 sm:gap-3 mb-4 sm:mb-6">
        <BellIcon class="w-5 h-5 sm:w-6 sm:h-6 text-green-600" />
        <h2 class="text-lg sm:text-xl font-bold text-gray-900">Enable/Disable Event Notifications</h2>
      </div>

      <div class="space-y-4 mb-4">
        <div>
          <label for="channelId" class="block text-sm font-medium text-gray-700 mb-2">
            Enable/Disable Event Notifications
          </label>
          <div class="flex gap-2">
            <button
              @click="toggleDiscordBotEventActivity"
              :disabled="testing"
              class="w-full sm:w-auto px-4 py-3 sm:py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center sm:justify-start gap-2 text-sm sm:text-base"
            >
              <span v-if="discordBotEventActivity == 1">Disable</span>
              <span v-else>Enable</span>
            </button>
          </div>
        </div>
      </div>

      <div class="space-y-4" v-if="discordBotEventActivity == 1">
        <div>
          <label for="channelId" class="block text-sm font-medium text-gray-700 mb-2">
            Discord Channel ID for Event Notifications
          </label>
          <div class="flex flex-col sm:flex-row gap-2">
            <input
              id="channelId"
              v-model="eventNotificationChannelId"
              type="text"
              placeholder="Enter Discord channel ID (e.g., 1234567890123456789)"
              class="flex-1 px-3 py-3 sm:py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm sm:text-base"
            />
            <button
              @click="testConnection"
              :disabled="!eventNotificationChannelId || testing"
              class="w-full sm:w-auto px-4 py-3 sm:py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center sm:justify-start gap-2 text-sm sm:text-base"
            >
              <span v-if="testing">Testing...</span>
              <span v-else>Test Connection</span>
            </button>
          </div>
          <p class="text-xs sm:text-sm text-gray-500 mt-2">
            Right-click on the channel in Discord and select "Copy Channel ID" to get the channel ID.
          </p>
        </div>
        
        <div class="flex gap-2">
          <button
            @click="saveSettings"
            :disabled="saving"
            class="w-full sm:w-auto px-4 py-3 sm:py-2 bg-green-600 text-white rounded-md hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center sm:justify-start gap-2 text-sm sm:text-base"
          >
            <span v-if="saving">Saving...</span>
            <span v-else>Save Settings</span>
          </button>
        </div>
      </div>
    </div>

    <!-- How to use Section -->
    <div class="mb-6 sm:mb-8">
      <div class="flex items-center gap-2 mb-4 sm:mb-6">
        <SettingsIcon class="w-5 h-5 text-gray-500" />
        <h2 class="text-lg sm:text-xl font-bold text-gray-900">How to use</h2>
      </div>
      <p class="text-gray-600 text-sm sm:text-base mb-4 sm:mb-6">Follow these simple steps to get your Discord integration up and running.</p>
      
      <!-- Steps -->
      <div class="space-y-3 sm:space-y-4">
        <!-- Step 1 -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6">
          <div class="flex items-start gap-3 sm:gap-4">
            <div class="w-6 h-6 sm:w-8 sm:h-8 bg-gray-200 rounded-full flex items-center justify-center flex-shrink-0 mt-1">
              <span class="text-gray-700 font-semibold text-xs sm:text-sm">1</span>
            </div>
            <div class="flex-1">
              <div class="flex items-center gap-2 mb-2">
                <BotIcon class="w-4 h-4 sm:w-5 sm:h-5 text-purple-600" />
                <h3 class="font-semibold text-gray-900 text-sm sm:text-base">Invite the bot to your server</h3>
              </div>
              <p class="text-gray-600 text-xs sm:text-sm">Click the button above to add Rotate Bot to your Discord server with the necessary permissions.</p>
            </div>
          </div>
        </div>

        <!-- Step 2 -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6">
          <div class="flex items-start gap-3 sm:gap-4">
            <div class="w-6 h-6 sm:w-8 sm:h-8 bg-gray-200 rounded-full flex items-center justify-center flex-shrink-0 mt-1">
              <span class="text-gray-700 font-semibold text-xs sm:text-sm">2</span>
            </div>
            <div class="flex-1">
              <div class="flex items-center gap-2 mb-2">
                <MessageSquareIcon class="w-4 h-4 sm:w-5 sm:h-5 text-green-600" />
                <h3 class="font-semibold text-gray-900 text-sm sm:text-base">Make sure all users have registered Discord ID</h3>
              </div>
              <p class="text-gray-600 text-xs sm:text-sm">In order to use the bot, all users must have registered their Discord ID in the VA System. Make sure users have registered their Discord ID by going to their Manage Profile page.</p>
            </div>
          </div>
        </div>

        <!-- Step 3 -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6">
          <div class="flex items-start gap-3 sm:gap-4">
            <div class="w-6 h-6 sm:w-8 sm:h-8 bg-gray-200 rounded-full flex items-center justify-center flex-shrink-0 mt-1">
              <span class="text-gray-700 font-semibold text-xs sm:text-sm">3</span>
            </div>
            <div class="flex-1">
              <div class="flex items-center gap-2 mb-2">
                <UsersIcon class="w-4 h-4 sm:w-5 sm:h-5 text-purple-600" />
                <h3 class="font-semibold text-gray-900 text-sm sm:text-base">Use the commands to file PIREPs</h3>
              </div>
              <p class="text-gray-600 text-xs sm:text-sm">Start using !pirep in your Discord channel to file PIREPs!</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, inject } from 'vue';
import { BotIcon, MessageSquareIcon, SettingsIcon, UsersIcon, BellIcon } from 'lucide-vue-next'
import rotateDataService from '@/rotate.js';

const showToast = inject('showToast')

const discordBotInviteUrl = ref('https://discord.com/oauth2/authorize?client_id=1234567890&permissions=8&scope=bot');

const openDiscordInvite = () => {
  window.open(discordBotInviteUrl.value, '_blank');
};

const eventNotificationChannelId = ref('');
const testing = ref(false);
const saving = ref(false);
const discordBotEventActivity = ref();

const fetchSettings = async () => {
  try {
    const response = await rotateDataService('/discord/jxGetDiscordSettings');
    if (response.hasErrors) {
      showToast(response.message || 'Error occurred while fetching Discord settings', 'error')
      return;
    }
    eventNotificationChannelId.value = response.data.event_notification_channel_id || '';
    discordBotEventActivity.value = response.data.discord_bot_event_activity == 1 ? true : false;
  } catch (error) {
    console.error('Error fetching Discord settings:', error);
    showToast('Failed to fetch Discord settings', 'error');
  }
};

const toggleDiscordBotEventActivity = async () => {
  try {
    const response = await rotateDataService('/discord/jxToggleDiscordBotEventActivity');
    if (response.hasErrors) {
      showToast(response.message || 'Error occurred while toggling Discord bot event activity', 'error')
    } else {
      showToast(response.message || 'Discord bot event activity toggled successfully!', 'success')
    }
  } catch (error) {
    console.error('Error toggling Discord bot event activity:', error);
    showToast('Failed to toggle Discord bot event activity', 'error');
  }
  discordBotEventActivity.value = !discordBotEventActivity.value;
};

const testConnection = async () => {
  if (!eventNotificationChannelId.value.trim()) {
    showToast('Please enter a channel ID first', 'error');
    return;
  }
  
  testing.value = true;
  try {
    const response = await rotateDataService('/discord/jxTestDiscordConnection', {
      event_notification_channel_id: eventNotificationChannelId.value
    });
    if (response.hasErrors) {
      showToast(response.message || 'Error occurred while testing Discord connection', 'error')
    } else {
      showToast(response.message || 'Discord connection test successful!', 'success')
    }
  } catch (error) {
    console.error('Error testing Discord connection:', error);
    showToast('Failed to test Discord connection', 'error');
  } finally {
    testing.value = false;
  }
};

const saveSettings = async () => {
  saving.value = true;
  try {
    const response = await rotateDataService('/discord/jxUpdateDiscordSettings', {
      event_notification_channel_id: eventNotificationChannelId.value
    });
    if (response.hasErrors) {
      showToast(response.message || 'Error occurred while saving Discord settings', 'error')
    } else {
      showToast(response.message || 'Discord settings saved successfully!', 'success')
    }
  } catch (error) {
    console.error('Error saving Discord settings:', error);
    showToast('Failed to save Discord settings', 'error');
  } finally {
    saving.value = false;
  }
};

onMounted(() => {
  fetchSettings();
});
</script>