<script setup lang="ts">
import ListingCardItem from '@/components/ListingCard.vue';
import PaginationLinks from '@/components/PaginationLinks.vue';
import SearchSelect from '@/components/SearchSelect.vue';
import SeoHead from '@/components/SeoHead.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { ListingCard, Paginated, SelectOption, SeoData } from '@/types';
import { Link, router } from '@inertiajs/vue3';
import { Search, SlidersHorizontal, X } from '@lucide/vue';
import { computed, reactive, ref, watch } from 'vue';

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

const showFilters = ref(false);

const search = (): void => {
  router.get('/anuncios', filterForm, { preserveState: true, replace: true });
};

const clearFilters = (): void => {
  filterForm.q = '';
  filterForm.category = '';
  filterForm.state = '';
  filterForm.city = '';
  search();
};

const hasActiveFilters = computed(
  () => filterForm.q || filterForm.category || filterForm.state || filterForm.city,
);

const categoryOptions = computed(() =>
  props.categories.map((category) => ({
    value: category.slug || '',
    label: category.name || '',
  })),
);

const topCategories = computed(() => categoryOptions.value.slice(0, 8));

const cityOptions = computed(() =>
  filterForm.state ? props.cities.filter((city) => city.state_code === filterForm.state) : [],
);

const selectCategory = (slug: string): void => {
  filterForm.category = filterForm.category === slug ? '' : slug;
  search();
};

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
    <!-- Hero strip -->
    <section class="-mx-6 -mt-8 mb-8 bg-zinc-950 px-6 py-10">
      <div class="mx-auto max-w-3xl text-center">
        <p class="text-brand-400 text-sm font-semibold tracking-widest uppercase">
          {{ listings.total }} anuncios publicados
        </p>
        <h1 class="mt-2 text-3xl font-extrabold tracking-tight text-white sm:text-4xl">
          Encontre o que voce procura
        </h1>

        <!-- barra de busca principal -->
        <form
          class="ring-brand-500/40 focus-within:ring-brand-500 mt-6 flex overflow-hidden rounded-xl bg-white ring-2"
          @submit.prevent="search"
        >
          <input
            v-model="filterForm.q"
            class="flex-1 bg-transparent px-4 py-3 text-sm text-zinc-900 placeholder-zinc-400 focus:outline-none"
            placeholder="O que voce esta procurando?"
            type="search"
          />
          <button
            class="bg-brand-500 hover:bg-brand-600 flex items-center gap-2 px-5 py-3 text-sm font-bold text-white transition-colors"
          >
            <Search class="h-4 w-4" />
            Buscar
          </button>
        </form>

        <!-- chips de categoria -->
        <div v-if="topCategories.length" class="mt-4 flex flex-wrap justify-center gap-2">
          <button
            v-for="cat in topCategories"
            :key="cat.value"
            type="button"
            class="rounded-full px-3 py-1 text-xs font-semibold transition-colors"
            :class="
              filterForm.category === cat.value
                ? 'bg-brand-500 text-white'
                : 'bg-zinc-800 text-zinc-300 hover:bg-zinc-700 hover:text-white'
            "
            @click="selectCategory(cat.value)"
          >
            {{ cat.label }}
          </button>
        </div>
      </div>
    </section>

    <!-- barra de filtros avancados -->
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
      <p class="text-sm text-zinc-500">
        <span class="font-semibold text-zinc-900">{{ listings.total }}</span> anuncios encontrados
      </p>

      <div class="flex items-center gap-2">
        <button
          v-if="hasActiveFilters"
          class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-200 bg-white px-3 py-1.5 text-xs font-medium text-zinc-600 hover:bg-zinc-50"
          type="button"
          @click="clearFilters"
        >
          <X class="h-3.5 w-3.5" />
          Limpar filtros
        </button>
        <button
          class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-200 bg-white px-3 py-1.5 text-xs font-medium text-zinc-700 hover:bg-zinc-50"
          type="button"
          @click="showFilters = !showFilters"
        >
          <SlidersHorizontal class="h-3.5 w-3.5" />
          Filtros avancados
        </button>
        <Link
          class="bg-brand-500 hover:bg-brand-600 inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-bold text-white"
          href="/admin/anuncios/create"
        >
          + Publicar anuncio
        </Link>
      </div>
    </div>

    <!-- painel de filtros avancados (colapsável) -->
    <div v-if="showFilters" class="mb-6 rounded-xl border border-zinc-200 bg-white p-5 shadow-sm">
      <form class="grid gap-4 sm:grid-cols-[1fr_1fr_80px_1fr_auto]" @submit.prevent="search">
        <div>
          <label class="mb-1 block text-xs font-semibold text-zinc-500 uppercase" for="q"
            >Busca</label
          >
          <input
            id="q"
            v-model="filterForm.q"
            class="focus:border-brand-400 focus:ring-brand-400/30 w-full rounded-lg border border-zinc-200 px-3 py-2 text-sm focus:ring-2 focus:outline-none"
            type="search"
          />
        </div>
        <div>
          <label class="mb-1 block text-xs font-semibold text-zinc-500 uppercase" for="category"
            >Categoria</label
          >
          <SearchSelect
            id="category"
            v-model="filterForm.category"
            clearable
            :options="categoryOptions"
            placeholder="Todas"
            search-placeholder="Buscar categoria"
          />
        </div>
        <div>
          <label class="mb-1 block text-xs font-semibold text-zinc-500 uppercase" for="state"
            >UF</label
          >
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
          <label class="mb-1 block text-xs font-semibold text-zinc-500 uppercase" for="city"
            >Cidade</label
          >
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
        <div class="flex items-end">
          <button
            class="bg-brand-500 hover:bg-brand-600 inline-flex w-full items-center justify-center gap-2 rounded-lg px-4 py-2 text-sm font-bold text-white"
          >
            <Search class="h-4 w-4" />
            Buscar
          </button>
        </div>
      </form>
    </div>

    <!-- grade de anuncios (4 colunas) -->
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
      <ListingCardItem v-for="listing in listings.data" :key="listing.id" :listing="listing" />
    </div>

    <p
      v-if="listings.data.length === 0"
      class="mt-8 rounded-xl border border-zinc-200 bg-white p-10 text-center text-zinc-500"
    >
      Nenhum anuncio encontrado para os filtros selecionados.
    </p>

    <div v-if="listings.last_page > 1" class="mt-8">
      <PaginationLinks :links="listings.links" />
    </div>
  </AppLayout>
</template>
