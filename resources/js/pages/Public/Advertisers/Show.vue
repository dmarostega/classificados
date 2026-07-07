<script setup lang="ts">
import PaginationLinks from '@/components/PaginationLinks.vue';
import SeoHead from '@/components/SeoHead.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { ListingCard, Paginated, SeoData } from '@/types';
import { Link } from '@inertiajs/vue3';

defineProps<{
  seo: SeoData;
  advertiser: {
    id: number;
    name: string;
  };
  listings: Paginated<ListingCard>;
}>();
</script>

<template>
  <SeoHead :seo="seo" />
  <AppLayout>
    <section class="space-y-6">
      <div class="rounded-lg border bg-white p-6">
        <Link class="text-sm font-medium underline" href="/anuncios">Voltar aos anuncios</Link>
        <h1 class="mt-4 text-3xl font-bold">Anuncios de {{ advertiser.name }}</h1>
        <p class="mt-2 text-sm text-slate-600">Veja anuncios publicados por este anunciante.</p>
      </div>

      <div class="flex items-center justify-between">
        <p class="text-sm text-slate-500">{{ listings.total }} anuncios encontrados</p>
      </div>

      <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        <Link
          v-for="listing in listings.data"
          :key="listing.id"
          :href="listing.url"
          class="overflow-hidden rounded-lg border bg-white hover:border-slate-400"
        >
          <div class="aspect-[4/3] bg-slate-100">
            <img
              v-if="listing.cover_url"
              :src="listing.cover_url"
              :alt="listing.title"
              class="h-full w-full object-cover"
            />
            <div v-else class="flex h-full items-center justify-center text-sm text-slate-400">
              Sem imagem
            </div>
          </div>
          <div class="space-y-2 p-4">
            <p class="text-xs font-semibold text-slate-500 uppercase">{{ listing.category }}</p>
            <h2 class="line-clamp-2 min-h-12 font-semibold">{{ listing.title }}</h2>
            <p class="text-lg font-bold">{{ listing.price }}</p>
            <p class="text-sm text-slate-500">{{ listing.city }} / {{ listing.state }}</p>
          </div>
        </Link>
      </div>

      <p
        v-if="listings.data.length === 0"
        class="rounded-lg border bg-white p-8 text-center text-slate-500"
      >
        Nenhum anuncio publico encontrado para este anunciante.
      </p>

      <PaginationLinks v-if="listings.last_page > 1" :links="listings.links" />
    </section>
  </AppLayout>
</template>
