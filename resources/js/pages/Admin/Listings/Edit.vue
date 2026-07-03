<script setup lang="ts">
import SeoHead from '@/components/SeoHead.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import ListingForm from '@/pages/Admin/Listings/components/ListingForm.vue';
import type { ListingDetail, SelectOption, SeoData } from '@/types';

const props = defineProps<{
  seo: SeoData;
  listing: ListingDetail;
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
      <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
          <p class="text-sm font-semibold text-slate-500">Editar anuncio</p>
          <h1 class="text-3xl font-bold">{{ listing.title }}</h1>
        </div>
        <a
          v-if="listing.public_url"
          class="rounded-md border px-4 py-2 font-medium"
          :href="listing.public_url"
          target="_blank"
        >
          Ver publicado
        </a>
      </div>
      <ListingForm
        :listing="listing"
        :categories="categories"
        :cities="cities"
        :states="states"
        :statuses="statuses"
        :submit-url="`/admin/anuncios/${props.listing.id}`"
        method="put"
      />
    </section>
  </AppLayout>
</template>
