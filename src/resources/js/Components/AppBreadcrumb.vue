<template>
  <nav class="text-sm text-gray-600" aria-label="Breadcrumb">
    <ol class="flex items-center space-x-2">
      <li>
        <Link :href="homeItem.route" class="flex items-center text-gray-800 hover:text-gray-900">
          <HomeIcon class="w-5 h-5 mr-2" /> {{ homeItem.title }}
        </Link>
      </li>

      <template v-for="(item, index) in items" :key="index">
        <li class="text-gray-400">›</li>
        <li>
          <template v-if="index !== items.length - 1 && item.route">
            <Link :href="item.route" class="font-semibold text-gray-800 hover:underline">
              {{ item.title }}
            </Link>
          </template>
          <template v-else>
            <span class="text-gray-400 font-medium">{{ item.title }}</span>
          </template>
        </li>
      </template>
    </ol>
  </nav>
</template>

<script setup>
import { defineProps } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { Home as HomeIcon } from 'lucide-vue-next'

const page = usePage();
const homeItem = {
  title: 'Dashboard',
  route: route('dashboard.index')
}

defineProps({
  items: {
    type: Array,
    required: true
  },
  homeItem: {
    type: Object,
    default: () => ({ title: 'Dashboard', route: route('dashboard.index') })
  }
})
</script>