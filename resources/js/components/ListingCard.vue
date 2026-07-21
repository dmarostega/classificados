<script setup lang="ts">
import type { ListingCard as ListingCardData } from '@/types';
import { Link } from '@inertiajs/vue3';
import { Heart } from '@lucide/vue';

defineProps<{ listing: ListingCardData }>();
</script>

<template>
  <Link
    :href="listing.url || '#'"
    class="relative overflow-hidden rounded-lg border bg-white hover:border-slate-400"
  >
    <span
      v-if="listing.is_favorited"
      class="absolute top-3 right-3 z-10 inline-flex items-center gap-1 rounded-full bg-white/95 px-2 py-1 text-xs font-semibold text-rose-700 shadow-sm"
    >
      <Heart class="h-3.5 w-3.5 fill-current" />
      Favorito
    </span>
    <div class="aspect-[4/3] bg-slate-100 p-2">
      <img
        v-if="listing.cover_url"
        :src="listing.cover_url"
        :alt="listing.title"
        class="h-full w-full object-contain"
      />
      <div v-else class="flex h-full items-center justify-center text-sm text-slate-400">
        Sem imagem
      </div>
    </div>
    <div class="space-y-2 p-4">
      <p class="text-xs font-semibold text-slate-500 uppercase">{{ listing.category }}</p>
      <h2 class="line-clamp-2 min-h-12 font-semibold">{{ listing.title }}</h2>
      <p class="text-lg font-bold">{{ listing.price }}</p>
      <div v-if="listing.commercial_badges?.length" class="flex flex-wrap gap-1.5">
        <span
          v-for="badge in listing.commercial_badges"
          :key="badge"
          class="rounded-full bg-slate-100 px-2 py-1 text-xs font-medium text-slate-700"
        >
          {{ badge }}
        </span>
      </div>
      <p class="text-sm text-slate-500">{{ listing.city }} / {{ listing.state }}</p>
    </div>
  </Link>
</template>
