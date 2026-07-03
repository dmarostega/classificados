<script setup lang="ts">
import SeoHead from '@/components/SeoHead.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { SeoData } from '@/types';
import { Link } from '@inertiajs/vue3';

defineProps<{
  seo: SeoData;
  metrics: { total: number; published: number; drafts: number; views: number };
  latestListings: Array<{
    id: number;
    title: string;
    status: string;
    category: string | null;
    price: string;
    edit_url: string;
  }>;
}>();
</script>

<template>
  <SeoHead :seo="seo" />
  <AppLayout>
    <section class="space-y-8">
      <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
          <p class="text-sm font-semibold text-slate-500">Painel do anunciante</p>
          <h1 class="mt-1 text-3xl font-bold">Gerencie seus anuncios</h1>
        </div>
        <Link
          class="rounded-md bg-slate-900 px-4 py-2 font-medium text-white"
          href="/admin/anuncios/create"
        >
          Novo anuncio
        </Link>
      </div>

      <div class="grid gap-4 md:grid-cols-4">
        <div class="rounded-lg border bg-white p-5">
          <p class="text-sm text-slate-500">Total</p>
          <p class="mt-2 text-3xl font-bold">{{ metrics.total }}</p>
        </div>
        <div class="rounded-lg border bg-white p-5">
          <p class="text-sm text-slate-500">Publicados</p>
          <p class="mt-2 text-3xl font-bold">{{ metrics.published }}</p>
        </div>
        <div class="rounded-lg border bg-white p-5">
          <p class="text-sm text-slate-500">Rascunhos</p>
          <p class="mt-2 text-3xl font-bold">{{ metrics.drafts }}</p>
        </div>
        <div class="rounded-lg border bg-white p-5">
          <p class="text-sm text-slate-500">Visualizacoes</p>
          <p class="mt-2 text-3xl font-bold">{{ metrics.views }}</p>
        </div>
      </div>

      <div class="rounded-lg border bg-white">
        <div class="flex items-center justify-between border-b px-5 py-4">
          <h2 class="font-semibold">Ultimos anuncios</h2>
          <Link class="text-sm font-medium text-slate-700 underline" href="/admin/anuncios"
            >Ver todos</Link
          >
        </div>
        <div class="divide-y">
          <Link
            v-for="listing in latestListings"
            :key="listing.id"
            class="grid gap-2 px-5 py-4 hover:bg-slate-50 md:grid-cols-[1fr_140px_120px]"
            :href="listing.edit_url"
          >
            <span class="font-medium">{{ listing.title }}</span>
            <span class="text-sm text-slate-500">{{ listing.status }}</span>
            <span class="text-sm font-semibold">{{ listing.price }}</span>
          </Link>
          <p v-if="latestListings.length === 0" class="px-5 py-8 text-sm text-slate-500">
            Nenhum anuncio cadastrado ainda.
          </p>
        </div>
      </div>
    </section>
  </AppLayout>
</template>
