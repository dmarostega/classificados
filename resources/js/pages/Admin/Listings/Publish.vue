<script setup lang="ts">
import SeoHead from '@/components/SeoHead.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { SeoData } from '@/types';
import { Check, Copy, ExternalLink } from '@lucide/vue';
import { computed, ref } from 'vue';

interface MarketplaceDraft {
  marketplace: string;
  label: string;
  title: string;
  description: string;
  short_text: string;
  full_text: string;
  suggested_category: string | null;
  price: string;
  checklist: string[];
  warnings: string[];
}

const props = defineProps<{
  seo: SeoData;
  listing: {
    title: string;
    price: string;
    category: string | null;
    location: string;
    description: string;
    edit_url: string;
    public_url: string | null;
  };
  drafts: MarketplaceDraft[];
}>();

const selectedMarketplace = ref(props.drafts[0]?.marketplace ?? '');
const copiedField = ref<string | null>(null);
const copyError = ref<string | null>(null);
const selectedDraft = computed(() =>
  props.drafts.find((draft) => draft.marketplace === selectedMarketplace.value),
);

const copy = async (field: string, value: string): Promise<void> => {
  copyError.value = null;

  try {
    await navigator.clipboard.writeText(value);
    copiedField.value = field;
  } catch {
    copiedField.value = null;
    copyError.value = 'Nao foi possivel copiar agora. Selecione o texto e copie manualmente.';
  }
};
</script>

<template>
  <SeoHead :seo="seo" />
  <AppLayout>
    <section class="space-y-6">
      <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
          <p class="text-sm font-semibold text-slate-500">Assistente de marketplaces</p>
          <h1 class="text-3xl font-bold">Preparar anuncio para outros canais</h1>
          <p class="mt-2 max-w-3xl text-slate-600">
            Este assistente apenas prepara o texto do anuncio para outros canais. A publicacao final
            deve ser feita manualmente por voce no marketplace escolhido. Nao coletamos login, senha
            ou credenciais de plataformas externas.
          </p>
        </div>
        <a class="rounded-md border px-4 py-2 font-medium" :href="listing.edit_url">
          Voltar ao anuncio
        </a>
      </div>

      <div class="grid gap-4 rounded-lg border bg-white p-5 sm:grid-cols-2 lg:grid-cols-4">
        <div>
          <p class="text-xs font-semibold text-slate-500 uppercase">Anuncio</p>
          <p class="mt-1 font-semibold">{{ listing.title }}</p>
        </div>
        <div>
          <p class="text-xs font-semibold text-slate-500 uppercase">Preco</p>
          <p class="mt-1 font-semibold">{{ listing.price }}</p>
        </div>
        <div>
          <p class="text-xs font-semibold text-slate-500 uppercase">Categoria</p>
          <p class="mt-1 font-semibold">{{ listing.category || 'Nao informada' }}</p>
        </div>
        <div>
          <p class="text-xs font-semibold text-slate-500 uppercase">Localizacao</p>
          <p class="mt-1 font-semibold">{{ listing.location }}</p>
        </div>
      </div>

      <div
        class="flex gap-2 overflow-x-auto border-b pb-px"
        role="tablist"
        aria-label="Marketplaces"
      >
        <button
          v-for="draft in drafts"
          :key="draft.marketplace"
          class="shrink-0 border-b-2 px-4 py-3 text-sm font-medium"
          :class="
            selectedMarketplace === draft.marketplace
              ? 'border-slate-900 text-slate-900'
              : 'border-transparent text-slate-500'
          "
          type="button"
          role="tab"
          :aria-selected="selectedMarketplace === draft.marketplace"
          @click="selectedMarketplace = draft.marketplace"
        >
          {{ draft.label }}
        </button>
      </div>

      <div v-if="selectedDraft" class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_360px]">
        <div class="space-y-4">
          <div class="rounded-lg border bg-white p-5">
            <div class="flex flex-wrap items-center justify-between gap-3">
              <h2 class="font-semibold">Titulo adaptado</h2>
              <button
                class="inline-flex items-center gap-2 rounded-md border px-3 py-2 text-sm font-medium"
                type="button"
                @click="copy('title', selectedDraft.title)"
              >
                <Check v-if="copiedField === 'title'" class="h-4 w-4" />
                <Copy v-else class="h-4 w-4" />
                {{ copiedField === 'title' ? 'Copiado' : 'Copiar titulo' }}
              </button>
            </div>
            <p class="mt-3 whitespace-pre-line">{{ selectedDraft.title }}</p>
          </div>

          <div class="rounded-lg border bg-white p-5">
            <div class="flex flex-wrap items-center justify-between gap-3">
              <h2 class="font-semibold">Descricao adaptada</h2>
              <button
                class="inline-flex items-center gap-2 rounded-md border px-3 py-2 text-sm font-medium"
                type="button"
                @click="copy('description', selectedDraft.description)"
              >
                <Check v-if="copiedField === 'description'" class="h-4 w-4" />
                <Copy v-else class="h-4 w-4" />
                {{ copiedField === 'description' ? 'Copiado' : 'Copiar descricao' }}
              </button>
            </div>
            <p class="mt-3 text-sm leading-6 whitespace-pre-line">
              {{ selectedDraft.description }}
            </p>
          </div>

          <div class="rounded-lg border bg-white p-5">
            <div class="flex flex-wrap items-center justify-between gap-3">
              <h2 class="font-semibold">Texto completo</h2>
              <button
                class="inline-flex items-center gap-2 rounded-md border px-3 py-2 text-sm font-medium"
                type="button"
                @click="copy('full', selectedDraft.full_text)"
              >
                <Check v-if="copiedField === 'full'" class="h-4 w-4" />
                <Copy v-else class="h-4 w-4" />
                {{ copiedField === 'full' ? 'Copiado' : 'Copiar texto completo' }}
              </button>
            </div>
            <p class="mt-3 text-sm leading-6 whitespace-pre-line">{{ selectedDraft.full_text }}</p>
          </div>
          <p v-if="copyError" class="text-sm text-red-700">{{ copyError }}</p>
        </div>

        <aside class="space-y-4">
          <div class="rounded-lg border bg-white p-5">
            <h2 class="font-semibold">Dados sugeridos</h2>
            <dl class="mt-4 space-y-3 text-sm">
              <div>
                <dt class="text-slate-500">Preco</dt>
                <dd class="font-medium">{{ selectedDraft.price }}</dd>
              </div>
              <div v-if="selectedDraft.suggested_category">
                <dt class="text-slate-500">Categoria</dt>
                <dd class="font-medium">{{ selectedDraft.suggested_category }}</dd>
              </div>
              <div>
                <dt class="text-slate-500">Texto curto</dt>
                <dd class="mt-1">{{ selectedDraft.short_text }}</dd>
              </div>
            </dl>
          </div>
          <div class="rounded-lg border bg-white p-5">
            <h2 class="font-semibold">Checklist para publicar manualmente</h2>
            <ol class="mt-4 list-decimal space-y-3 pl-5 text-sm text-slate-700">
              <li v-for="item in selectedDraft.checklist" :key="item">{{ item }}</li>
            </ol>
          </div>
          <div class="rounded-lg border border-amber-200 bg-amber-50 p-5 text-sm text-amber-950">
            <h2 class="font-semibold">Avisos</h2>
            <ul class="mt-3 list-disc space-y-2 pl-5">
              <li v-for="warning in selectedDraft.warnings" :key="warning">{{ warning }}</li>
            </ul>
          </div>
          <a
            v-if="listing.public_url"
            class="inline-flex items-center gap-2 text-sm font-medium underline"
            :href="listing.public_url"
            rel="noopener noreferrer"
            target="_blank"
            ><ExternalLink class="h-4 w-4" />Ver anuncio publico</a
          >
        </aside>
      </div>
    </section>
  </AppLayout>
</template>
