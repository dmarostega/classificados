<script setup lang="ts">
import type { PaginationLink } from '@/types';
import { Link } from '@inertiajs/vue3';

defineProps<{ links: PaginationLink[] }>();

const paginationLabel = (label: string): string =>
  label.replace('&laquo;', '‹').replace('&raquo;', '›');
</script>

<template>
  <nav class="flex flex-wrap gap-1.5" aria-label="Paginacao">
    <component
      :is="link.url ? Link : 'span'"
      v-for="link in links"
      :key="link.label"
      :href="link.url || undefined"
      class="inline-flex h-9 min-w-9 items-center justify-center rounded-lg px-3 text-sm font-semibold transition-colors"
      :class="[
        link.active
          ? 'bg-brand-500 text-white'
          : link.url
            ? 'hover:border-brand-400 hover:text-brand-600 border border-zinc-200 bg-white text-zinc-700'
            : 'border border-zinc-100 bg-zinc-50 text-zinc-300',
      ]"
    >
      {{ paginationLabel(link.label) }}
    </component>
  </nav>
</template>
