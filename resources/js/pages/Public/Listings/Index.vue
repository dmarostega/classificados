<script setup lang="ts">
import ListingCardItem from '@/components/ListingCard.vue';
import PaginationLinks from '@/components/PaginationLinks.vue';
import SearchSelect from '@/components/SearchSelect.vue';
import SeoHead from '@/components/SeoHead.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { ListingCard, Paginated, SelectOption, SeoData } from '@/types';
import { Link, router } from '@inertiajs/vue3';
import { Search } from '@lucide/vue';
import { computed, reactive, watch } from 'vue';

const props = defineProps<{
  seo: SeoData;
  categories: SelectOption[];
  cities: SelectOption[];
  filters: { category?: string; city?: string; state?: string; q?: string };
  listings: Paginated<ListingCard>;
  states: SelectOption[];
}>();

const filterForm = reactive({
  q: props.filters.q || '',
  category: props.filters.category || '',
  state: props.filters.state || '',
  city: props.filters.state ? props.filters.city || '' : '',
});

const search = (): void => {
  router.get('/anuncios', filterForm, { preserveState: true, replace: true });
};

const categoryOptions = computed(() =>
  props.categories.map((category) => ({
    value: category.slug || '',
    label: category.name || '',
  })),
);

const cityOptions = computed(() =>
  filterForm.state ? props.cities.filter((city) => city.state_code === filterForm.state) : [],
);

watch(
  () => filterForm.state,
  () => {
    if (filterForm.city && !cityOptions.value.some((city) => city.value === filterForm.city)) {
      filterForm.city = '';
    }
  },
);
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
            <SearchSelect
              id="category"
              v-model="filterForm.category"
              clearable
              :options="categoryOptions"
              placeholder="Todas"
              search-placeholder="Buscar categoria"
            />
          </div>
          <div class="grid grid-cols-[80px_1fr] gap-3">
            <div>
              <label class="mb-1 block text-sm font-medium" for="state">UF</label>
              <SearchSelect
                id="state"
                v-model="filterForm.state"
                clearable
                :options="states"
                placeholder="UF"
                search-placeholder="Buscar UF"
              />
            </div>
            <div>
              <label class="mb-1 block text-sm font-medium" for="city">Cidade</label>
              <SearchSelect
                id="city"
                v-model="filterForm.city"
                clearable
                :disabled="!filterForm.state"
                :options="cityOptions"
                :placeholder="filterForm.state ? 'Todas' : 'Selecione UF'"
                search-placeholder="Buscar cidade"
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
          <ListingCardItem
            v-for="listing in listings.data"
            :key="listing.id"
            :listing="listing"
          />
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
