<script setup lang="ts">
import SeoHead from '@/components/SeoHead.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { SeoData } from '@/types';
import { Link, useForm } from '@inertiajs/vue3';
import { BarChart2, FileEdit, Globe, LayoutGrid } from '@lucide/vue';
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
      <!-- cabeçalho -->
      <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
          <p class="text-brand-600 text-xs font-bold tracking-widest uppercase">
            Painel do anunciante
          </p>
          <h1 class="mt-1 text-3xl font-extrabold tracking-tight text-zinc-900">
            Gerencie seus anuncios
          </h1>
        </div>
        <Link
          class="bg-brand-500 hover:bg-brand-600 inline-flex items-center gap-2 rounded-xl px-5 py-2.5 font-bold text-white transition-colors"
          href="/admin/anuncios/create"
        >
          + Novo anuncio
        </Link>
      </div>

      <!-- métricas -->
      <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="flex items-center gap-4 rounded-2xl bg-zinc-950 p-5 text-white">
          <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-zinc-800">
            <LayoutGrid class="h-5 w-5 text-zinc-300" />
          </span>
          <div>
            <p class="text-xs font-semibold text-zinc-400">Total</p>
            <p class="text-2xl font-extrabold">{{ metrics.total }}</p>
          </div>
        </div>
        <div class="bg-brand-500 flex items-center gap-4 rounded-2xl p-5 text-white">
          <span class="bg-brand-600 flex h-10 w-10 shrink-0 items-center justify-center rounded-xl">
            <Globe class="text-brand-200 h-5 w-5" />
          </span>
          <div>
            <p class="text-brand-100 text-xs font-semibold">Publicados</p>
            <p class="text-2xl font-extrabold">{{ metrics.published }}</p>
          </div>
        </div>
        <div class="flex items-center gap-4 rounded-2xl border border-zinc-200 bg-white p-5">
          <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-zinc-100">
            <FileEdit class="h-5 w-5 text-zinc-500" />
          </span>
          <div>
            <p class="text-xs font-semibold text-zinc-400">Rascunhos</p>
            <p class="text-2xl font-extrabold text-zinc-900">{{ metrics.drafts }}</p>
          </div>
        </div>
        <div class="flex items-center gap-4 rounded-2xl border border-zinc-200 bg-white p-5">
          <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-zinc-100">
            <BarChart2 class="h-5 w-5 text-zinc-500" />
          </span>
          <div>
            <p class="text-xs font-semibold text-zinc-400">Visualizacoes</p>
            <p class="text-2xl font-extrabold text-zinc-900">{{ metrics.views }}</p>
          </div>
        </div>
      </div>

      <!-- imagem de compartilhamento -->
      <form class="rounded-2xl border border-zinc-200 bg-white p-6" @submit.prevent="submitProfile">
        <h2 class="font-bold text-zinc-900">Imagem para compartilhamento</h2>
        <p class="mt-1 text-sm text-zinc-500">
          Opcional. Use JPG, PNG ou WebP horizontal (ideal: 1200 × 630 px), de ate 10 MB.
        </p>
        <div class="mt-4 flex flex-wrap items-center gap-3">
          <img
            v-if="advertiserProfile.og_image_url"
            :src="advertiserProfile.og_image_url"
            alt="Imagem atual de compartilhamento"
            class="h-20 w-32 rounded-lg border object-cover"
          />
          <input
            ref="ogImageInput"
            accept="image/jpeg,image/png,image/webp"
            type="file"
            @change="onOgImageChange"
          />
          <button
            class="rounded-xl bg-zinc-950 px-4 py-2 text-sm font-bold text-white transition-colors hover:bg-zinc-800 disabled:opacity-60"
            :disabled="!profileForm.og_image || profileForm.processing"
          >
            Salvar imagem
          </button>
          <button
            v-if="advertiserProfile.og_image_url"
            class="rounded-xl border border-red-200 px-4 py-2 text-sm font-bold text-red-700 hover:bg-red-50"
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

      <!-- tabela de anúncios recentes -->
      <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white">
        <div class="flex items-center justify-between border-b border-zinc-100 px-6 py-4">
          <h2 class="font-bold text-zinc-900">Ultimos anuncios</h2>
          <Link class="text-brand-600 hover:text-brand-700 text-xs font-bold" href="/admin/anuncios"
            >Ver todos →</Link
          >
        </div>
        <div class="divide-y divide-zinc-50">
          <Link
            v-for="listing in latestListings"
            :key="listing.id"
            class="grid items-center gap-2 px-6 py-4 transition-colors hover:bg-zinc-50 md:grid-cols-[1fr_140px_120px]"
            :href="listing.edit_url"
          >
            <span class="font-semibold text-zinc-900">{{ listing.title }}</span>
            <span class="text-sm text-zinc-400">{{ listing.status }}</span>
            <span class="text-sm font-bold text-zinc-900">{{ listing.price }}</span>
          </Link>
          <p v-if="latestListings.length === 0" class="px-6 py-10 text-sm text-zinc-400">
            Nenhum anuncio cadastrado ainda.
          </p>
        </div>
      </div>
    </section>
  </AppLayout>
</template>
