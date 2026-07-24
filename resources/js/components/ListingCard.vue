<script setup lang="ts">
import type { ListingCard as ListingCardData } from '@/types';
import { Link } from '@inertiajs/vue3';
import { Heart } from '@lucide/vue';

defineProps<{ listing: ListingCardData }>();
</script>

<template>
  <Link
    :href="listing.url || '#'"
    class="group relative flex flex-col overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-zinc-200 transition-all hover:-translate-y-0.5 hover:shadow-md hover:ring-zinc-300"
  >
    <!-- imagem full-bleed -->
    <div class="relative aspect-[4/3] overflow-hidden bg-zinc-100">
      <img
        v-if="listing.cover_url"
        :src="listing.cover_url"
        :alt="listing.title"
        class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
      />
      <div v-else class="flex h-full items-center justify-center text-sm text-zinc-400">
        Sem imagem
      </div>

      <!-- badge categoria topo-esquerda -->
      <span
        v-if="listing.category"
        class="absolute top-2.5 left-2.5 rounded-full bg-zinc-950/75 px-2.5 py-1 text-[11px] font-semibold tracking-wide text-white uppercase backdrop-blur-sm"
      >
        {{ listing.category }}
      </span>

      <!-- destaque topo-direita -->
      <span
        v-if="listing.is_featured"
        class="bg-brand-500 absolute top-2.5 right-2.5 rounded-full px-2.5 py-1 text-[11px] font-bold tracking-wide text-white uppercase"
      >
        Destaque
      </span>

      <!-- favorito topo-direita quando não há destaque -->
      <span
        v-else-if="listing.is_favorited"
        class="absolute top-2.5 right-2.5 inline-flex h-7 w-7 items-center justify-center rounded-full bg-white/90 text-rose-600 shadow"
      >
        <Heart class="h-3.5 w-3.5 fill-current" />
      </span>

      <!-- preço no rodapé da imagem -->
      <div
        class="absolute right-0 bottom-0 left-0 bg-gradient-to-t from-zinc-950/80 to-transparent px-3 pt-6 pb-3"
      >
        <p class="text-base font-bold text-white">{{ listing.price }}</p>
      </div>
    </div>

    <!-- corpo do card -->
    <div class="flex flex-1 flex-col gap-1.5 p-3">
      <h2 class="line-clamp-2 text-sm leading-snug font-semibold text-zinc-900">
        {{ listing.title }}
      </h2>

      <div v-if="listing.commercial_badges?.length" class="flex flex-wrap gap-1">
        <span
          v-for="badge in listing.commercial_badges"
          :key="badge"
          class="rounded-full px-2 py-0.5 text-[11px] font-medium"
          :class="
            badge === 'Reservado'
              ? 'bg-amber-100 text-amber-800 ring-1 ring-amber-300'
              : 'bg-brand-50 text-brand-700 ring-brand-200 ring-1'
          "
        >
          {{ badge }}
        </span>
      </div>

      <p class="mt-auto text-xs text-zinc-400">{{ listing.city }} / {{ listing.state }}</p>
    </div>
  </Link>
</template>
