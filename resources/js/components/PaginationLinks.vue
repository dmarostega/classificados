<script setup lang="ts">
import type { PaginationLink } from '@/types';
import { Link } from '@inertiajs/vue3';

defineProps<{ links: PaginationLink[] }>();

const paginationLabel = (label: string): string =>
  label.replace('&laquo;', '‹').replace('&raquo;', '›');
</script>

<template>
  <nav class="flex flex-wrap gap-2" aria-label="Paginacao">
    <component
      :is="link.url ? Link : 'span'"
      v-for="link in links"
      :key="link.label"
      :href="link.url || undefined"
      class="rounded-md border px-3 py-2 text-sm"
      :class="[
        link.active
          ? 'border-slate-900 bg-slate-900 text-white'
          : 'border-slate-200 bg-white text-slate-700',
        !link.url ? 'opacity-50' : 'hover:border-slate-400',
      ]"
    >
      {{ paginationLabel(link.label) }}
    </component>
  </nav>
</template>
