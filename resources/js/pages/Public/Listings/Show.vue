<script setup lang="ts">
import ListingImageLightbox from '@/components/ListingImageLightbox.vue';
import SeoHead from '@/components/SeoHead.vue';
import { formatPhone } from '@/composables/useInputMasks';
import AppLayout from '@/layouts/AppLayout.vue';
import type { ListingDetail, ListingPhoneReveal, PageProps, SeoData } from '@/types';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { Check, Copy, Heart, Mail, Maximize2, MessageCircle, Phone } from '@lucide/vue';
import axios from 'axios';
import { computed, nextTick, ref } from 'vue';

const props = defineProps<{ seo: SeoData; listing: ListingDetail }>();
const page = usePage<PageProps>();
const selectedImage = ref(props.listing.images[0]?.url || props.listing.cover_url);
const isLightboxOpen = ref(false);
const imageTrigger = ref<HTMLButtonElement | null>(null);
const form = useForm({ name: '', email: '', phone: '', message: '' });
const favoriteForm = useForm({});
const hasImages = computed(() => props.listing.images.length > 0);
const user = computed(() => page.props.auth.user);
const revealedPhone = ref<ListingPhoneReveal | null>(null);
const isRevealingPhone = ref(false);
const phoneRevealError = ref<string | null>(null);
const phoneCopied = ref(false);
const phoneCopyError = ref<string | null>(null);

const selectImage = (imageUrl: string): void => {
  selectedImage.value = imageUrl;
};

const openLightbox = (): void => {
  if (!selectedImage.value) {
    return;
  }

  isLightboxOpen.value = true;
};

const closeLightbox = async (isOpen: boolean): Promise<void> => {
  isLightboxOpen.value = isOpen;

  if (!isOpen) {
    await nextTick();
    imageTrigger.value?.focus();
  }
};

const onPhoneInput = (event: Event): void => {
  form.phone = formatPhone((event.target as HTMLInputElement).value);
};

const revealPhone = async (): Promise<void> => {
  isRevealingPhone.value = true;
  phoneRevealError.value = null;

  try {
    const response = await axios.get<ListingPhoneReveal>(
      `/anuncios/${props.listing.slug}/telefone`,
    );
    revealedPhone.value = response.data;
  } catch {
    phoneRevealError.value = 'Nao foi possivel revelar o telefone agora.';
  } finally {
    isRevealingPhone.value = false;
  }
};

const copyPhone = async (): Promise<void> => {
  if (!revealedPhone.value) {
    return;
  }

  phoneCopyError.value = null;

  try {
    await navigator.clipboard.writeText(revealedPhone.value.phone);
    phoneCopied.value = true;
  } catch {
    phoneCopied.value = false;
    phoneCopyError.value = 'Nao foi possivel copiar. Copie manualmente.';
  }
};

const toggleFavorite = (): void => {
  const url = `/favoritos/${props.listing.slug}`;
  const options = { preserveScroll: true };

  if (props.listing.is_favorited) {
    favoriteForm.delete(url, options);
    return;
  }

  favoriteForm.post(url, options);
};
</script>

<template>
  <SeoHead :seo="seo" />
  <AppLayout>
    <article class="grid gap-8 lg:grid-cols-[1fr_380px]">
      <section class="space-y-5">
        <div class="overflow-hidden rounded-lg border bg-white">
          <div class="aspect-[16/10] bg-slate-100 p-3">
            <button
              v-if="selectedImage"
              ref="imageTrigger"
              class="group relative h-full w-full cursor-zoom-in rounded-md focus:ring-2 focus:ring-slate-900 focus:ring-offset-2 focus:outline-none"
              type="button"
              aria-label="Ampliar imagem do anuncio"
              @click="openLightbox"
            >
              <img :src="selectedImage" :alt="listing.title" class="h-full w-full object-contain" />
              <span
                class="absolute right-3 bottom-3 rounded-full bg-slate-950/70 p-2 text-white opacity-0 transition group-hover:opacity-100 group-focus:opacity-100"
              >
                <Maximize2 class="h-4 w-4" aria-hidden="true" />
              </span>
            </button>
            <div v-else class="flex h-full items-center justify-center text-slate-400">
              Sem imagem
            </div>
          </div>
          <div v-if="hasImages" class="flex gap-3 overflow-x-auto border-t p-3">
            <button
              v-for="image in listing.images"
              :key="image.id"
              class="h-20 w-24 shrink-0 overflow-hidden rounded-md border"
              type="button"
              @click="selectImage(image.url)"
            >
              <img
                :src="image.url"
                :alt="image.alt_text || listing.title"
                class="h-full w-full object-contain"
              />
            </button>
          </div>
        </div>

        <div class="rounded-lg border bg-white p-6">
          <p class="text-sm font-semibold text-slate-500">
            {{ listing.category }}
          </p>
          <h1 class="mt-2 text-3xl font-bold">{{ listing.title }}</h1>
          <p class="mt-2 text-2xl font-bold">{{ listing.price }}</p>
          <div v-if="listing.commercial_badges?.length" class="mt-3 flex flex-wrap gap-2">
            <span
              v-for="badge in listing.commercial_badges"
              :key="badge"
              class="rounded-full bg-slate-100 px-3 py-1 text-sm font-medium text-slate-700"
            >
              {{ badge }}
            </span>
          </div>
          <p class="mt-2 text-sm text-slate-500">{{ listing.city }} / {{ listing.state }}</p>
          <div class="prose prose-slate mt-6 max-w-none whitespace-pre-line">
            {{ listing.description }}
          </div>
        </div>
      </section>

      <aside class="h-fit rounded-lg border bg-white p-6">
        <button
          v-if="user"
          class="mb-6 inline-flex w-full items-center justify-center gap-2 rounded-md border px-4 py-2 font-medium hover:bg-slate-50 disabled:opacity-60"
          :class="listing.is_favorited ? 'border-rose-200 text-rose-700' : ''"
          type="button"
          :disabled="favoriteForm.processing"
          @click="toggleFavorite"
        >
          <Heart class="h-4 w-4" :class="listing.is_favorited ? 'fill-current' : ''" />
          {{ listing.is_favorited ? 'Remover dos favoritos' : 'Salvar nos favoritos' }}
        </button>
        <Link
          v-else
          class="mb-6 inline-flex w-full items-center justify-center gap-2 rounded-md border px-4 py-2 font-medium hover:bg-slate-50"
          href="/login"
        >
          <Heart class="h-4 w-4" />
          Entrar para favoritar
        </Link>

        <h2 class="text-lg font-semibold">Falar com anunciante</h2>
        <p class="mt-1 text-sm text-slate-500">{{ listing.contact_name }}</p>
        <Link
          v-if="listing.advertiser"
          class="mt-3 inline-flex text-sm font-medium underline"
          :href="listing.advertiser.url"
        >
          Ver mais anuncios deste anunciante
        </Link>
        <div v-if="listing.contact_phone_masked" class="mt-4 space-y-3">
          <p class="inline-flex items-center gap-2 text-sm font-medium">
            <Phone class="h-4 w-4" />
            {{ revealedPhone?.phone || listing.contact_phone_masked }}
          </p>

          <button
            v-if="!revealedPhone"
            class="block rounded-md border px-3 py-2 text-sm font-medium hover:bg-slate-50 disabled:opacity-60"
            type="button"
            :disabled="isRevealingPhone"
            @click="revealPhone"
          >
            {{ isRevealingPhone ? 'Revelando...' : 'Ver telefone' }}
          </button>

          <div v-else class="flex flex-wrap gap-2">
            <button
              class="inline-flex items-center gap-2 rounded-md border px-3 py-2 text-sm font-medium hover:bg-slate-50"
              type="button"
              @click="copyPhone"
            >
              <Check v-if="phoneCopied" class="h-4 w-4" />
              <Copy v-else class="h-4 w-4" />
              {{ phoneCopied ? 'Copiado' : 'Copiar' }}
            </button>
            <a
              class="inline-flex items-center gap-2 rounded-md border px-3 py-2 text-sm font-medium hover:bg-slate-50"
              :href="revealedPhone.phone_href"
            >
              <Phone class="h-4 w-4" />
              Ligar
            </a>
            <a
              class="inline-flex items-center gap-2 rounded-md border px-3 py-2 text-sm font-medium hover:bg-slate-50"
              :href="revealedPhone.whatsapp_url"
              rel="noopener noreferrer"
              target="_blank"
            >
              <MessageCircle class="h-4 w-4" />
              WhatsApp
            </a>
          </div>

          <p v-if="phoneRevealError" class="text-sm text-red-700">
            {{ phoneRevealError }}
          </p>
          <p v-if="phoneCopyError" class="text-sm text-red-700">
            {{ phoneCopyError }}
          </p>
        </div>

        <form
          class="mt-6 space-y-4"
          @submit.prevent="form.post(`/anuncios/${listing.slug}/contato`)"
        >
          <div>
            <label class="mb-1 block text-sm font-medium" for="name">Nome</label>
            <input
              id="name"
              v-model="form.name"
              required
              class="w-full rounded-md border px-3 py-2"
              type="text"
            />
            <p v-if="form.errors.name" class="text-sm text-red-700">
              {{ form.errors.name }}
            </p>
          </div>
          <div>
            <label class="mb-1 block text-sm font-medium" for="email">E-mail</label>
            <input
              id="email"
              v-model="form.email"
              required
              class="w-full rounded-md border px-3 py-2"
              type="email"
            />
            <p v-if="form.errors.email" class="text-sm text-red-700">
              {{ form.errors.email }}
            </p>
          </div>
          <div>
            <label class="mb-1 block text-sm font-medium" for="phone">Telefone</label>
            <input
              id="phone"
              v-model="form.phone"
              class="w-full rounded-md border px-3 py-2"
              maxlength="15"
              placeholder="(47) 99999-9999"
              type="tel"
              @input="onPhoneInput"
            />
          </div>
          <div>
            <label class="mb-1 block text-sm font-medium" for="message">Mensagem</label>
            <textarea
              id="message"
              v-model="form.message"
              required
              class="min-h-32 w-full rounded-md border px-3 py-2"
            />
            <p v-if="form.errors.message" class="text-sm text-red-700">
              {{ form.errors.message }}
            </p>
          </div>
          <button
            class="inline-flex w-full items-center justify-center gap-2 rounded-md bg-slate-900 px-4 py-2 font-medium text-white"
            :disabled="form.processing"
          >
            <Mail class="h-4 w-4" />
            Enviar contato
          </button>
        </form>
      </aside>
    </article>
    <ListingImageLightbox
      :images="listing.images"
      :model-value="isLightboxOpen"
      :selected-image-url="selectedImage"
      :listing-title="listing.title"
      @select="selectImage($event.url)"
      @update:model-value="closeLightbox"
    />
  </AppLayout>
</template>
