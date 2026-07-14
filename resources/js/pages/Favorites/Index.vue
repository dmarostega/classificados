<script setup lang="ts">
import ListingCard from '@/components/ListingCard.vue';
import PaginationLinks from '@/components/PaginationLinks.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { ListingCard as ListingCardData, Paginated } from '@/types';
import { Link } from '@inertiajs/vue3';
import { Heart } from '@lucide/vue';

defineProps<{ listings: Paginated<ListingCardData> }>();
</script>

<template>
  <AppLayout>
    <section class="space-y-6">
      <header>
        <div class="flex items-center gap-3">
          <Heart class="h-6 w-6 text-rose-700" />
          <h1 class="text-2xl font-bold">Meus favoritos</h1>
        </div>
        <p class="mt-2 text-sm text-slate-600">
          Acompanhe os anuncios que voce salvou.
        </p>
      </header>

      <div v-if="listings.data.length" class="grid gap-4 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        <ListingCard v-for="listing in listings.data" :key="listing.id" :listing="listing" />
      </div>

      <div v-else class="rounded-lg border bg-white p-8 text-center">
        <p class="text-slate-600">Voce ainda nao salvou nenhum anuncio.</p>
        <Link class="mt-3 inline-flex font-medium underline" href="/anuncios">
          Explorar anuncios
        </Link>
      </div>

      <PaginationLinks v-if="listings.last_page > 1" :links="listings.links" />
    </section>
  </AppLayout>
</template>
