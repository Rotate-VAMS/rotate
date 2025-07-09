<template>
  <div class="rounded-2xl shadow-lg bg-white overflow-hidden w-full max-w-md mx-auto border border-gray-100">
    <!-- Header -->
    <div class="bg-gradient-to-r from-indigo-500 to-purple-500 text-white p-4 pb-3 relative">
      <div class="flex justify-between items-center">
        <div class="font-bold text-lg tracking-wide">ROTATE AIRLINES</div>
        <span
          v-if="status && status.toLowerCase() === 'completed'"
          class="uppercase text-xs font-bold bg-green-100 text-green-700 px-3 py-1 rounded-full shadow-sm"
        >
          COMPLETED
        </span>
        <span
          v-else-if="status && status.toLowerCase() === 'in-progress'"
          class="uppercase text-xs font-bold bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full shadow-sm"
        >
          IN-PROGRESS
        </span>
      </div>
      <div class="text-sm text-white/80 mt-1">Flight {{ flightNo }}</div>
    </div>

    <!-- Main Content -->
    <div class="p-6 pt-4 space-y-4">
      <!-- Airports & Route -->
      <div class="flex items-center justify-between">
        <div class="flex flex-col items-start">
          <div class="text-3xl font-extrabold tracking-wide text-gray-900">{{ from }}</div>
          <div class="text-xs text-gray-400 mt-1">{{ departureTime }}</div>
          <div class="text-xs text-gray-400 -mt-1">DEPARTURE</div>
        </div>
        <div class="flex flex-col items-center flex-1 mx-2">
          <div class="flex items-center gap-1">
            <span class="h-0.5 w-6 bg-gray-300 rounded-full"></span>
            <PlaneIcon class="w-4 h-4 text-purple-500" />
            <span class="h-0.5 w-6 bg-gray-300 rounded-full"></span>
          </div>
          <div class="text-xs text-gray-500 mt-1">{{ distance }}</div>
        </div>
        <div class="flex flex-col items-end">
          <div class="text-3xl font-extrabold tracking-wide text-gray-900">{{ to }}</div>
          <div class="text-xs text-gray-400 mt-1">{{ arrivalTime }}</div>
          <div class="text-xs text-gray-400 -mt-1">ARRIVAL</div>
        </div>
      </div>

      <!-- Pilot & Aircraft -->
      <div class="grid grid-cols-2 gap-4 text-sm text-gray-700 mt-2">
        <div class="flex items-center gap-2">
          <UserIcon class="w-4 h-4 text-gray-400" />
          <span class="font-medium">{{ pilot }}</span>
        </div>
        <div class="flex items-center gap-2">
          <PlaneIcon class="w-4 h-4 text-gray-400" />
          <span class="font-medium">{{ aircraft }}</span>
        </div>
        <div class="flex items-center gap-2">
          <ClockIcon class="w-4 h-4 text-gray-400" />
          <span>{{ flightTime }}</span>
        </div>
        <div class="flex items-center gap-2">
          <FuelIcon class="w-4 h-4 text-gray-400" />
          <span>{{ fuelUsed }}</span>
        </div>
      </div>

      <!-- Score, Multiplier, Computed -->
      <div class="flex justify-between items-center border-t pt-4 mt-2">
        <div class="flex flex-col items-center flex-1">
          <div class="text-xs text-gray-400">SCORE</div>
          <div class="bg-green-100 text-green-700 font-bold rounded-md px-3 py-1 text-lg mt-1">{{ score }}</div>
        </div>
        <div class="flex flex-col items-center flex-1">
          <div class="text-xs text-gray-400">MULTIPLIER</div>
          <div class="text-purple-600 font-bold text-lg mt-1">{{ multiplier }}</div>
        </div>
        <div class="flex flex-col items-center flex-1">
          <div class="text-xs text-gray-400">COMPUTED</div>
          <div class="font-bold text-lg mt-1">{{ computedTime }}</div>
        </div>
      </div>

      <!-- Barcode & Footer -->
      <div class="flex flex-col items-center mt-4">
        <!-- Barcode Placeholder -->
        <svg width="80" height="32" viewBox="0 0 80 32" fill="none" xmlns="http://www.w3.org/2000/svg">
          <rect x="2" y="2" width="2" height="28" fill="#222"/>
          <rect x="6" y="2" width="1" height="28" fill="#222"/>
          <rect x="10" y="2" width="3" height="28" fill="#222"/>
          <rect x="15" y="2" width="1" height="28" fill="#222"/>
          <rect x="18" y="2" width="2" height="28" fill="#222"/>
          <rect x="22" y="2" width="1" height="28" fill="#222"/>
          <rect x="25" y="2" width="2" height="28" fill="#222"/>
          <rect x="29" y="2" width="1" height="28" fill="#222"/>
          <rect x="32" y="2" width="3" height="28" fill="#222"/>
          <rect x="37" y="2" width="1" height="28" fill="#222"/>
          <rect x="40" y="2" width="2" height="28" fill="#222"/>
          <rect x="44" y="2" width="1" height="28" fill="#222"/>
          <rect x="47" y="2" width="2" height="28" fill="#222"/>
          <rect x="51" y="2" width="1" height="28" fill="#222"/>
          <rect x="54" y="2" width="3" height="28" fill="#222"/>
          <rect x="59" y="2" width="1" height="28" fill="#222"/>
          <rect x="62" y="2" width="2" height="28" fill="#222"/>
          <rect x="66" y="2" width="1" height="28" fill="#222"/>
          <rect x="69" y="2" width="2" height="28" fill="#222"/>
          <rect x="73" y="2" width="1" height="28" fill="#222"/>
        </svg>
        <div class="font-mono text-xs text-gray-500 mt-1 tracking-widest">{{ barcode }}</div>
      </div>

      <!-- Footer: Time Ago -->
      <div class="flex justify-end items-center mt-2 text-xs text-gray-400">
        <CalendarIcon class="w-4 h-4 mr-1" />
        <span>{{ timeAgo }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { User as UserIcon, Plane as PlaneIcon, Clock as ClockIcon, Calendar as CalendarIcon, Fuel as FuelIcon } from 'lucide-vue-next';
const props = defineProps({
  flightNo: String,
  from: String,
  to: String,
  distance: [String, Number],
  pilot: String,
  aircraft: String,
  flightTime: String,
  fuelUsed: String,
  score: [String, Number],
  multiplier: String,
  computedTime: String,
  status: String,
  id: [String, Number],
  timeAgo: String,
  barcode: String,
  departureTime: String,
  arrivalTime: String,
});
</script>