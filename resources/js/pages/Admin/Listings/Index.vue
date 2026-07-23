<script setup lang="ts">
import PaginationLinks from '@/components/PaginationLinks.vue';
import SearchSelect from '@/components/SearchSelect.vue';
import SeoHead from '@/components/SeoHead.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { ListingCard, Paginated, SelectOption, SeoData } from '@/types';
import { Link, router } from '@inertiajs/vue3';
import { Pencil, Plus, Send, Search, Star, Trash2 } from '@lucide/vue';
import { reactive } from 'vue';

const props = defineProps<{
  seo: SeoData;
  filters: { status?: string; q?: string };
  statuses: SelectOption[];
  listings: Paginated<ListingCard>;
}>();

const filterForm = reactive({ q: props.filters.q || '', status: props.filters.status || '' });
const search = (): void =>
  router.get('/admin/anuncios', filterForm, { preserveState: true, replace: true });
const destroyListing = (id: number): void => {
  if (window.confirm('Remover este anuncio?')) {
    router.delete(`/admin/anuncios/${id}`);
  }
};
const toggleFeatured = (listing: ListingCard): void => {
  router[listing.is_featured ? 'delete' : 'post'](`/admin/anuncios/${listing.id}/destaque`);
};
</script>

<template>
  <SeoHead :seo="seo" />
  <AppLayout>
    <section class="space-y-6">
      <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
          <p class="text-sm font-semibold text-slate-500">Admin</p>
          <h1 class="text-3xl font-bold">Meus anuncios</h1>
        </div>
        <Link
          class="inline-flex items-center gap-2 rounded-md bg-slate-900 px-4 py-2 font-medium text-white"
          href="/admin/anuncios/create"
        >
          <Plus class="h-4 w-4" />
          Novo anuncio
        </Link>
      </div>

      <form
        class="grid gap-3 rounded-lg border bg-white p-4 md:grid-cols-[1fr_200px_auto]"
        @submit.prevent="search"
      >
        <input
          v-model="filterForm.q"
          class="rounded-md border px-3 py-2"
          placeholder="Buscar por titulo ou descricao"
          type="search"
        />
        <SearchSelect
          v-model="filterForm.status"
          clearable
          :options="statuses"
          placeholder="Todos os status"
          search-placeholder="Buscar status"
        />
        <button class="inline-flex items-center justify-center gap-2 rounded-md border px-4 py-2">
          <Search class="h-4 w-4" />
          Filtrar
        </button>
      </form>

      <div class="overflow-hidden rounded-lg border bg-white">
        <div class="divide-y">
          <div
            v-for="listing in listings.data"
            :key="listing.id"
            class="grid gap-4 p-4 md:grid-cols-[96px_1fr_auto]"
          >
            <div class="aspect-[4/3] overflow-hidden rounded-md bg-slate-100">
              <img
                v-if="listing.cover_url"
                :src="listing.cover_url"
                :alt="listing.title"
                class="h-full w-full object-cover"
              />
            </div>
            <div>
              <p class="text-xs font-semibold text-slate-500 uppercase">
                {{ listing.status_label }}
              </p>
              <h2 class="mt-1 font-semibold">{{ listing.title }}</h2>
              <p class="mt-1 text-sm text-slate-500">
                {{ listing.category }} - {{ listing.city }} / {{ listing.state }}
              </p>
              <p class="mt-2 font-bold">{{ listing.price }}</p>
              <p v-if="listing.is_featured" class="mt-2 text-sm font-semibold text-amber-700">
                Anuncio em destaque
              </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
              <Link
                class="inline-flex items-center gap-2 rounded-md border px-3 py-2 text-sm"
                :href="listing.publish_url || '#'"
              >
                <Send class="h-4 w-4" />
                Preparar para marketplaces
              </Link>
              <button
                v-if="listing.public_url"
                class="inline-flex items-center gap-2 rounded-md border px-3 py-2 text-sm"
                type="button"
                @click="toggleFeatured(listing)"
              >
                <Star
                  class="h-4 w-4"
                  :class="listing.is_featured ? 'fill-current text-amber-500' : ''"
                />
                {{ listing.is_featured ? 'Remover destaque' : 'Definir destaque' }}
              </button>
              <Link
                class="inline-flex items-center gap-2 rounded-md border px-3 py-2 text-sm"
                :href="listing.edit_url || '#'"
              >
                <Pencil class="h-4 w-4" />
                Editar
              </Link>
              <button
                class="inline-flex items-center gap-2 rounded-md border px-3 py-2 text-sm text-red-700"
                type="button"
                @click="destroyListing(listing.id)"
              >
                <Trash2 class="h-4 w-4" />
                Excluir
              </button>
            </div>
          </div>
          <p v-if="listings.data.length === 0" class="p-8 text-center text-sm text-slate-500">
            Voce ainda nao cadastrou anuncios.
          </p>
        </div>
      </div>

      <PaginationLinks v-if="listings.last_page > 1" :links="listings.links" />
    </section>
  </AppLayout>
</template>
