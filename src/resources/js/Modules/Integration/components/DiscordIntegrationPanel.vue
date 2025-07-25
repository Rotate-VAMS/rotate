<template>
  <div class="max-w-4xl mx-auto p-4 sm:p-6">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8">
      <div class="flex items-center gap-3 mb-4 sm:mb-0">
        <div class="w-10 h-10 bg-gradient-to-r from-indigo-500 to-purple-500 rounded-lg flex items-center justify-center">
          <BotIcon class="w-6 h-6 text-white" />
        </div>
        <div>
          <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Discord Integration</h1>
          <p class="text-gray-600 text-sm sm:text-base">Connect your Discord server to file PIREPs and manage operations.</p>
        </div>
      </div>
    </div>

    <!-- Connect Discord Server Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
      <div class="flex flex-col sm:flex-row sm:items-center gap-4">
        <div class="flex-1">
          <h2 class="text-xl font-bold text-gray-900 mb-2">Connect Discord Server</h2>
          <p class="text-gray-600 mb-4">File PIREPs and more from your Discord server! Streamline your operations with seamless integration.</p>
          <button 
            @click="openDiscordInvite"
            class="btn-primary bg-gradient-to-r from-indigo-500 to-purple-500 hover:from-indigo-600 hover:to-purple-600 text-white text-bold rounded-md px-4 py-2 flex items-center gap-2"
          >
            <BotIcon class="w-5 h-5" />
            <span>Invite Rotate Bot to your server</span>
          </button>
        </div>
      </div>
    </div>

    <!-- How to use Section -->
    <div class="mb-8">
      <div class="flex items-center gap-2 mb-6">
        <SettingsIcon class="w-5 h-5 text-gray-500" />
        <h2 class="text-xl font-bold text-gray-900">How to use</h2>
      </div>
      <p class="text-gray-600 mb-6">Follow these simple steps to get your Discord integration up and running.</p>
      
      <!-- Steps -->
      <div class="space-y-4">
        <!-- Step 1 -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
          <div class="flex items-start gap-4">
            <div class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center flex-shrink-0 mt-1">
              <span class="text-gray-700 font-semibold text-sm">1</span>
            </div>
            <div class="flex-1">
              <div class="flex items-center gap-2 mb-2">
                <BotIcon class="w-5 h-5 text-purple-600" />
                <h3 class="font-semibold text-gray-900">Invite the bot to your server</h3>
              </div>
              <p class="text-gray-600 text-sm">Click the button above to add Rotate Bot to your Discord server with the necessary permissions.</p>
            </div>
          </div>
        </div>

        <!-- Step 2 -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
          <div class="flex items-start gap-4">
            <div class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center flex-shrink-0 mt-1">
              <span class="text-gray-700 font-semibold text-sm">2</span>
            </div>
            <div class="flex-1">
              <div class="flex items-center gap-2 mb-2">
                <MessageSquareIcon class="w-5 h-5 text-green-600" />
                <h3 class="font-semibold text-gray-900">Make sure all users have registered Discord ID</h3>
              </div>
              <p class="text-gray-600 text-sm">In order to use the bot, all users must have registered their Discord ID in the VA System. Make sure users have registered their Discord ID by going to their Manage Profile page.</p>
            </div>
          </div>
        </div>

        <!-- Step 3 -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
          <div class="flex items-start gap-4">
            <div class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center flex-shrink-0 mt-1">
              <span class="text-gray-700 font-semibold text-sm">3</span>
            </div>
            <div class="flex-1">
              <div class="flex items-center gap-2 mb-2">
                <UsersIcon class="w-5 h-5 text-purple-600" />
                <h3 class="font-semibold text-gray-900">Use the commands to file PIREPs</h3>
              </div>
              <p class="text-gray-600 text-sm">Start using !pirep in your Discord channel to file PIREPs!</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { BotIcon, MessageSquareIcon, SettingsIcon, UsersIcon } from 'lucide-vue-next'

const discordBotInviteUrl = ref('https://discord.com/oauth2/authorize?client_id=1234567890&permissions=8&scope=bot');

const openDiscordInvite = () => {
  window.open(discordBotInviteUrl.value, '_blank');
};
</script>