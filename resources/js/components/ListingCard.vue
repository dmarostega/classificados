<script setup lang="ts">
import type { ListingCard as ListingCardData } from '@/types';
import { Link } from '@inertiajs/vue3';
import { Heart } from '@lucide/vue';

defineProps<{ listing: ListingCardData }>();
</script>

<template>
  <Link
    :href="listing.url || '#'"
    class="group relative overflow-hidden rounded-xl border border-slate-200 bg-white transition-shadow hover:shadow-md"
  >
    <span
      v-if="listing.is_featured"
      class="absolute top-3 left-3 z-10 rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-800 ring-1 ring-amber-300"
    >
      Destaque
    </span>
    <span
      class="absolute top-3 right-3 z-10 inline-flex h-8 w-8 items-center justify-center rounded-full bg-white/95 shadow-sm"
      :class="listing.is_favorited ? 'text-rose-600' : 'text-slate-400'"
    >
      <Heart class="h-4 w-4" :class="{ 'fill-current': listing.is_favorited }" />
    </span>
    <div class="aspect-[4/3] bg-slate-50 p-2">
      <img
        v-if="listing.cover_url"
        :src="listing.cover_url"
        :alt="listing.title"
        class="h-full w-full object-contain transition-transform group-hover:scale-[1.02]"
      />
      <div v-else class="flex h-full items-center justify-center text-sm text-slate-400">
        Sem imagem
      </div>
    </div>
    <div class="space-y-2 p-4">
      <p v-if="listing.category" class="text-brand-700 text-xs font-semibold uppercase">
        {{ listing.category }}
      </p>
      <h2 class="line-clamp-2 min-h-12 font-semibold text-slate-900">{{ listing.title }}</h2>
      <p class="text-lg font-bold text-slate-900">{{ listing.price }}</p>
      <div v-if="listing.commercial_badges?.length" class="flex flex-wrap gap-1.5">
        <span
          v-for="badge in listing.commercial_badges"
          :key="badge"
          class="rounded-full px-2 py-1 text-xs font-medium"
          :class="
            badge === 'Reservado'
              ? 'bg-amber-100 text-amber-900 ring-1 ring-amber-300'
              : 'bg-brand-50 text-brand-800'
          "
        >
          {{ badge }}
        </span>
      </div>
      <p class="flex items-center gap-1 text-sm text-slate-500">
        {{ listing.city }} / {{ listing.state }}
      </p>
    </div>
  </Link>
</template>
