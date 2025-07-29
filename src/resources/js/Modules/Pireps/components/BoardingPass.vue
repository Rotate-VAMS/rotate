<template>
  <div class="rounded-2xl shadow-lg bg-white overflow-hidden w-full max-w-md mx-auto border border-gray-100">
    <!-- Header -->
    <div class="bg-gradient-to-r from-indigo-500 to-purple-500 text-white p-4 pb-3 relative">
      <div class="flex justify-between items-center">
        <div v-if="airline !== '-'" class="font-bold text-lg tracking-wide">{{ airline }}</div>
        <div v-else class="font-bold text-lg tracking-wide">{{ pirep.event_name || '-' }}</div>
      </div>
      <div v-if="flightNo !== '-'" class="text-sm text-white/80 mt-1">Flight <span class="font-bold">{{ flightNo }}</span></div>
      <div v-else class="text-sm text-white/80 mt-1"><span class="font-bold rounded-full bg-gradient-to-r from-yellow-200 via-yellow-400 to-yellow-200 px-2 py-1 text-xs text-gray-800 border border-yellow-400">Event PIREP</span></div>
    </div>

    <!-- Main Content -->
    <div class="p-6 pt-4 space-y-4">
      <!-- Airports & Route -->
      <div class="flex items-center justify-between">
        <div class="flex flex-col items-start">
          <div class="text-3xl font-extrabold tracking-wide text-gray-900">{{ from }}</div>
          <div class="text-xs text-gray-400 mt-1">{{ origin_city }}</div>
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
          <div class="text-3xl font-extrabold tracking-wide text-gray-900">{{ destination }}</div>
          <div class="text-xs text-gray-400 mt-1">{{ destination_city }}</div>
          <div class="text-xs text-gray-400 mt-1">{{ arrivalTime }}</div>
          <div class="text-xs text-gray-400 -mt-1">ARRIVAL</div>
        </div>
      </div>

      <!-- Pilot, Aircraft, Flight Type -->
      <div class="grid grid-cols-2 gap-4 text-sm text-gray-700 mt-2">
        <div class="flex items-center gap-2">
          <UserIcon class="w-4 h-4 text-gray-400" />
          <span class="font-medium">{{ pilot }}</span>
        </div>
        <div class="flex items-center gap-2">
          <ClockIcon class="w-4 h-4 text-gray-400" />
          <span>{{ flightTime }}</span>
        </div>
        <div class="flex items-center gap-2 col-span-2">
          <PlaneIcon class="w-4 h-4 text-gray-400" />
          <span class="font-medium bg-gradient-to-r from-indigo-500 to-purple-500 text-white px-2 py-1 rounded-md">{{ flightType }}</span>
        </div>
      </div>

      <!-- Custom Fields -->
      <div v-if="customFields.length" class="grid grid-cols-2 gap-4 text-sm text-gray-700 mt-2">
        <div v-for="customField in customFields" :key="customField.id" class="flex items-center gap-2">
          <span class="text-xs text-gray-400">{{ customField.field_name }}:</span>
          <span class="font-medium">{{ getCustomFieldValue(pirep.value, customField.field_key) }}</span>
        </div>
      </div>

      <!-- Score, Multiplier, Computed -->
      <div class="flex justify-between items-center border-t pt-4 mt-2">
        <div class="flex flex-col items-center flex-1">
          <div class="text-xs text-gray-400">MULTIPLIER</div>
          <div class="text-purple-600 font-bold text-lg mt-1">{{ multiplier }}x</div>
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
        <span>{{ createdAt }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { User as UserIcon, Plane as PlaneIcon, Clock as ClockIcon, Calendar as CalendarIcon, Fuel as FuelIcon } from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, toRefs } from 'vue';

const props = defineProps({
  pirep: {
    type: Object,
    required: true,
  },
  customFields: {
    type: Array,
    default: () => []
  }
});

// Use toRefs to ensure proper reactivity
const { pirep } = toRefs(props);

// Helper to format flight time (e.g., 125 -> 2h 5m)
const formatFlightTime = (totalMinutes) => {
  if (totalMinutes === null || totalMinutes === undefined) {
    return '-';
  }
  const hours = Math.floor(totalMinutes / 60);
  const minutes = totalMinutes % 60;
  return `${hours}h ${minutes}m`;
};

// Helper to get custom field value
const getCustomFieldValue = (pirepData, fieldKey) => {
  if (pirepData.custom_fields && Array.isArray(pirepData.custom_fields)) {
    const customField = pirepData.custom_fields.find(field => {
      const fieldDefinition = props.customFields.find(cf => cf.id === field.field_id)
      return fieldDefinition && fieldDefinition.field_key === fieldKey
    })
    if (customField) {
      return customField.value_display
    }
  }
  if (pirepData[fieldKey]) {
    return pirepData[fieldKey]
  }
  return '-'
}

const flightNo = computed(() => pirep.value.flight_number || '-');
const from = computed(() => pirep.value.origin || pirep.value.from || '-');
const destination = computed(() => pirep.value.destination || pirep.value.to || '-');
const origin_city = computed(() => pirep.value.origin_city || pirep.value.origin || '-');
const destination_city = computed(() => pirep.value.destination_city || pirep.value.destination || '-');
const departureTime = computed(() => pirep.value.departure_time || '-');
const arrivalTime = computed(() => pirep.value.arrival_time || '-');
const distance = computed(() => (pirep.value.distance ? `${pirep.value.distance} NM` : '-'));
const pilot = computed(() => pirep.value.pilot_name || '-');
const flightTime = computed(() => formatFlightTime(pirep.value.flight_time));
const multiplier = computed(() => pirep.value.multiplier ?? '-');
const computedTime = computed(() => formatFlightTime(pirep.value.computed_flight_time) ?? '-');
const barcode = computed(() => pirep.value.barcode || pirep.value.id || '-');
const createdAt = computed(() => pirep.value.created_at || '-');
const flightType = computed(() => pirep.value.flight_type_name || '-');
const airline = computed(() => pirep.value.airline || '-');
</script>