<script setup lang="ts">
import SeoHead from '@/components/SeoHead.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { SeoData } from '@/types';
import { Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

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
  advertiserProfile: { og_image_url: string | null };
}>();

const profileForm = useForm({
  _method: 'patch',
  og_image: null as File | null,
  remove_og_image: false,
});
const ogImageInput = ref<HTMLInputElement | null>(null);
const submitProfile = (): void => profileForm.post('/perfil-anunciante', { forceFormData: true });
const onOgImageChange = (event: Event): void => {
  profileForm.og_image = (event.target as HTMLInputElement).files?.[0] || null;
  profileForm.remove_og_image = false;
};
const removeOgImage = (): void => {
  profileForm.og_image = null;
  profileForm.remove_og_image = true;
  submitProfile();
};
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

      <form class="rounded-lg border bg-white p-5" @submit.prevent="submitProfile">
        <h2 class="font-semibold">Imagem para compartilhamento</h2>
        <p class="mt-1 text-sm text-slate-500">
          Opcional. Use JPG, PNG ou WebP horizontal (ideal: 1200 × 630 px), de ate 10 MB.
        </p>
        <div class="mt-4 flex flex-wrap items-center gap-3">
          <img
            v-if="advertiserProfile.og_image_url"
            :src="advertiserProfile.og_image_url"
            alt="Imagem atual de compartilhamento"
            class="h-20 w-32 rounded border object-cover"
          />
          <input
            ref="ogImageInput"
            accept="image/jpeg,image/png,image/webp"
            type="file"
            @change="onOgImageChange"
          />
          <button
            class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white disabled:opacity-60"
            :disabled="!profileForm.og_image || profileForm.processing"
          >
            Salvar imagem
          </button>
          <button
            v-if="advertiserProfile.og_image_url"
            class="rounded-md border px-4 py-2 text-sm font-medium text-red-700"
            type="button"
            :disabled="profileForm.processing"
            @click="removeOgImage"
          >
            Remover imagem
          </button>
        </div>
        <p v-if="profileForm.errors.og_image" class="mt-2 text-sm text-red-700">
          {{ profileForm.errors.og_image }}
        </p>
      </form>

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
