<script setup lang="ts">
import SeoHead from '@/components/SeoHead.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import ListingForm from '@/pages/Admin/Listings/components/ListingForm.vue';
import type { SelectOption, SeoData } from '@/types';

defineProps<{
  seo: SeoData;
  categories: SelectOption[];
  cities: SelectOption[];
  states: SelectOption[];
  statuses: SelectOption[];
}>();
</script>

<template>
  <SeoHead :seo="seo" />
  <AppLayout>
    <section class="space-y-6">
      <div>
        <p class="text-sm font-semibold text-slate-500">Novo anuncio</p>
        <h1 class="text-3xl font-bold">Cadastrar anuncio</h1>
      </div>
      <ListingForm
        :categories="categories"
        :cities="cities"
        :states="states"
        :statuses="statuses"
        submit-url="/admin/anuncios"
        method="post"
      />
    </section>
  </AppLayout>
</template>
