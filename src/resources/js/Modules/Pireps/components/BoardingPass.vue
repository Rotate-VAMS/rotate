<template>
  <div class="rounded-2xl shadow-lg bg-white overflow-hidden w-full max-w-md mx-auto border border-gray-100">
    <!-- Header -->
    <div class="bg-gradient-to-r from-indigo-500 to-purple-500 text-white p-4 pb-3 relative">
      <div class="flex justify-between items-center">
        <div class="font-bold text-lg tracking-wide">{{ airline }}</div>
      </div>
      <div class="text-sm text-white/80 mt-1">Flight <span class="font-bold">{{ flightNo }}</span></div>
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
          <span class="font-medium">{{ getCustomFieldValue(props.pirep, customField.field_key) }}</span>
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
import { computed } from 'vue';

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
const getCustomFieldValue = (pirep, fieldKey) => {
  if (pirep.custom_fields && Array.isArray(pirep.custom_fields)) {
    const customField = pirep.custom_fields.find(field => {
      const fieldDefinition = props.customFields.find(cf => cf.id === field.field_id)
      return fieldDefinition && fieldDefinition.field_key === fieldKey
    })
    if (customField) {
      return customField.value_display
    }
  }
  if (pirep[fieldKey]) {
    return pirep[fieldKey]
  }
  return '-'
}

const flightNo = computed(() => props.pirep.flight_number || '-');
const from = computed(() => props.pirep.origin || props.pirep.from || '-');
const destination = computed(() => props.pirep.destination || props.pirep.to || '-');
const origin_city = computed(() => props.pirep.origin_city || props.pirep.origin || '-');
const destination_city = computed(() => props.pirep.destination_city || props.pirep.destination || '-');
const departureTime = computed(() => props.pirep.departure_time || '-');
const arrivalTime = computed(() => props.pirep.arrival_time || '-');
const distance = computed(() => (props.pirep.distance ? `${props.pirep.distance} NM` : '-'));
const pilot = computed(() => props.pirep.pilot_name || '-');
const flightTime = computed(() => formatFlightTime(props.pirep.flight_time));
const multiplier = computed(() => props.pirep.multiplier ?? '-');
const computedTime = computed(() => formatFlightTime(props.pirep.computed_flight_time) ?? '-');
const barcode = computed(() => props.pirep.barcode || props.pirep.id || '-');
const createdAt = computed(() => props.pirep.created_at || '-');
const flightType = computed(() => props.pirep.flight_type_name || '-');
const airline = computed(() => props.pirep.airline || '-');
</script>