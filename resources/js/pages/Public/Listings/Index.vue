<script setup lang="ts">
import PaginationLinks from '@/components/PaginationLinks.vue';
import SeoHead from '@/components/SeoHead.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { ListingCard, Paginated, SelectOption, SeoData } from '@/types';
import { Link, router } from '@inertiajs/vue3';
import { Search } from '@lucide/vue';
import { reactive } from 'vue';

const props = defineProps<{
  seo: SeoData;
  categories: SelectOption[];
  filters: { category?: string; city?: string; state?: string; q?: string };
  listings: Paginated<ListingCard>;
}>();

const filterForm = reactive({
  q: props.filters.q || '',
  category: props.filters.category || '',
  city: props.filters.city || '',
  state: props.filters.state || '',
});

const search = (): void => {
  router.get('/anuncios', filterForm, { preserveState: true, replace: true });
};
</script>

<template>
  <SeoHead :seo="seo" />
  <AppLayout>
    <section class="grid gap-8 lg:grid-cols-[320px_1fr]">
      <aside class="h-fit rounded-lg border bg-white p-5">
        <h1 class="text-2xl font-bold">Classificados</h1>
        <p class="mt-2 text-sm text-slate-600">
          Busque anuncios por termo, categoria e localizacao.
        </p>
        <form class="mt-5 space-y-4" @submit.prevent="search">
          <div>
            <label class="mb-1 block text-sm font-medium" for="q">Busca</label>
            <input
              id="q"
              v-model="filterForm.q"
              class="w-full rounded-md border px-3 py-2"
              type="search"
            />
          </div>
          <div>
            <label class="mb-1 block text-sm font-medium" for="category">Categoria</label>
            <select
              id="category"
              v-model="filterForm.category"
              class="w-full rounded-md border px-3 py-2"
            >
              <option value="">Todas</option>
              <option v-for="category in categories" :key="category.slug" :value="category.slug">
                {{ category.name }}
              </option>
            </select>
          </div>
          <div class="grid grid-cols-[1fr_80px] gap-3">
            <div>
              <label class="mb-1 block text-sm font-medium" for="city">Cidade</label>
              <input
                id="city"
                v-model="filterForm.city"
                class="w-full rounded-md border px-3 py-2"
                type="text"
              />
            </div>
            <div>
              <label class="mb-1 block text-sm font-medium" for="state">UF</label>
              <input
                id="state"
                v-model="filterForm.state"
                class="w-full rounded-md border px-3 py-2 uppercase"
                maxlength="2"
              />
            </div>
          </div>
          <button
            class="inline-flex w-full items-center justify-center gap-2 rounded-md bg-slate-900 px-4 py-2 font-medium text-white"
          >
            <Search class="h-4 w-4" />
            Buscar
          </button>
        </form>
      </aside>

      <div class="space-y-5">
        <div class="flex items-center justify-between">
          <p class="text-sm text-slate-500">{{ listings.total }} anuncios encontrados</p>
          <Link class="text-sm font-medium underline" href="/admin/anuncios/create"
            >Publicar anuncio</Link
          >
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
          Nenhum anuncio encontrado.
        </p>
        <PaginationLinks v-if="listings.last_page > 1" :links="listings.links" />
      </div>
    </section>
  </AppLayout>
</template>
