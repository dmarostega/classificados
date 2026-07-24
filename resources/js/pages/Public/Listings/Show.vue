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
  if (!selectedImage.value) return;
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
  if (!revealedPhone.value) return;
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
    <article class="grid gap-8 lg:grid-cols-[1fr_360px]">
      <!-- coluna esquerda: imagens + descrição -->
      <section class="space-y-5">
        <!-- galeria -->
        <div class="overflow-hidden rounded-2xl bg-zinc-900">
          <div class="aspect-[16/10] bg-zinc-800">
            <button
              v-if="selectedImage"
              ref="imageTrigger"
              class="group relative h-full w-full cursor-zoom-in focus:outline-none"
              type="button"
              aria-label="Ampliar imagem do anuncio"
              @click="openLightbox"
            >
              <img :src="selectedImage" :alt="listing.title" class="h-full w-full object-contain" />
              <span
                class="absolute right-3 bottom-3 rounded-full bg-zinc-950/70 p-2 text-white opacity-0 transition group-hover:opacity-100 group-focus:opacity-100"
              >
                <Maximize2 class="h-4 w-4" aria-hidden="true" />
              </span>
            </button>
            <div v-else class="flex h-full items-center justify-center text-zinc-500">
              Sem imagem
            </div>
          </div>
          <div v-if="hasImages" class="flex gap-2 overflow-x-auto p-3">
            <button
              v-for="image in listing.images"
              :key="image.id"
              class="h-16 w-20 shrink-0 overflow-hidden rounded-lg border-2 transition-colors"
              :class="
                selectedImage === image.url
                  ? 'border-brand-500'
                  : 'border-transparent hover:border-zinc-600'
              "
              type="button"
              @click="selectImage(image.url)"
            >
              <img
                :src="image.url"
                :alt="image.alt_text || listing.title"
                class="h-full w-full object-cover"
              />
            </button>
          </div>
        </div>

        <!-- título + descrição -->
        <div class="rounded-2xl border border-zinc-200 bg-white p-6">
          <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
              <p
                v-if="listing.category"
                class="text-brand-600 text-xs font-bold tracking-widest uppercase"
              >
                {{ listing.category }}
              </p>
              <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-zinc-900">
                {{ listing.title }}
              </h1>
            </div>
            <p class="text-2xl font-extrabold text-zinc-900">{{ listing.price }}</p>
          </div>

          <div v-if="listing.commercial_badges?.length" class="mt-3 flex flex-wrap gap-2">
            <span
              v-for="badge in listing.commercial_badges"
              :key="badge"
              class="rounded-full px-3 py-1 text-xs font-semibold"
              :class="
                badge === 'Reservado'
                  ? 'bg-amber-100 text-amber-800 ring-1 ring-amber-300'
                  : 'bg-brand-50 text-brand-700 ring-brand-200 ring-1'
              "
            >
              {{ badge }}
            </span>
          </div>

          <p class="mt-2 text-sm text-zinc-400">{{ listing.city }} / {{ listing.state }}</p>

          <div
            class="mt-6 border-t border-zinc-100 pt-6 text-sm leading-relaxed whitespace-pre-line text-zinc-700"
          >
            {{ listing.description }}
          </div>
        </div>
      </section>

      <!-- coluna direita: sidebar de contato (fundo escuro) -->
      <aside class="space-y-4">
        <!-- card de contato escuro -->
        <div class="rounded-2xl bg-zinc-950 p-6 text-white">
          <h2 class="text-lg font-bold">Falar com anunciante</h2>
          <p class="mt-0.5 text-sm text-zinc-400">{{ listing.contact_name }}</p>

          <Link
            v-if="listing.advertiser"
            class="text-brand-400 hover:text-brand-300 mt-2 inline-flex text-xs font-medium"
            :href="listing.advertiser.url"
          >
            Ver mais anuncios deste anunciante →
          </Link>

          <!-- telefone -->
          <div v-if="listing.contact_phone_masked" class="mt-5 space-y-3">
            <p class="inline-flex items-center gap-2 text-sm font-medium text-zinc-200">
              <Phone class="h-4 w-4 text-zinc-400" />
              {{ revealedPhone?.phone || listing.contact_phone_masked }}
            </p>

            <button
              v-if="!revealedPhone"
              class="hover:border-brand-500 hover:text-brand-400 block w-full rounded-lg border border-zinc-700 px-3 py-2 text-sm font-semibold text-zinc-200 transition-colors disabled:opacity-50"
              type="button"
              :disabled="isRevealingPhone"
              @click="revealPhone"
            >
              {{ isRevealingPhone ? 'Revelando...' : 'Ver telefone completo' }}
            </button>

            <div v-else class="flex flex-wrap gap-2">
              <button
                class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-700 px-3 py-1.5 text-xs font-semibold text-zinc-200 hover:border-zinc-500"
                type="button"
                @click="copyPhone"
              >
                <Check v-if="phoneCopied" class="h-3.5 w-3.5" />
                <Copy v-else class="h-3.5 w-3.5" />
                {{ phoneCopied ? 'Copiado' : 'Copiar' }}
              </button>
              <a
                class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-700 px-3 py-1.5 text-xs font-semibold text-zinc-200 hover:border-zinc-500"
                :href="revealedPhone.phone_href"
              >
                <Phone class="h-3.5 w-3.5" />
                Ligar
              </a>
              <a
                class="inline-flex items-center gap-1.5 rounded-lg bg-green-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-green-700"
                :href="revealedPhone.whatsapp_url"
                rel="noopener noreferrer"
                target="_blank"
              >
                <MessageCircle class="h-3.5 w-3.5" />
                WhatsApp
              </a>
            </div>

            <p v-if="phoneRevealError" class="text-xs text-red-400">{{ phoneRevealError }}</p>
            <p v-if="phoneCopyError" class="text-xs text-red-400">{{ phoneCopyError }}</p>
          </div>

          <!-- formulário de contato -->
          <form
            class="mt-6 space-y-3"
            @submit.prevent="form.post(`/anuncios/${listing.slug}/contato`)"
          >
            <div>
              <label class="mb-1 block text-xs font-semibold text-zinc-400" for="name">Nome</label>
              <input
                id="name"
                v-model="form.name"
                required
                class="focus:border-brand-500 focus:ring-brand-500 w-full rounded-lg border border-zinc-700 bg-zinc-900 px-3 py-2 text-sm text-white placeholder-zinc-600 focus:ring-1 focus:outline-none"
                type="text"
              />
              <p v-if="form.errors.name" class="mt-1 text-xs text-red-400">
                {{ form.errors.name }}
              </p>
            </div>
            <div>
              <label class="mb-1 block text-xs font-semibold text-zinc-400" for="email"
                >E-mail</label
              >
              <input
                id="email"
                v-model="form.email"
                required
                class="focus:border-brand-500 focus:ring-brand-500 w-full rounded-lg border border-zinc-700 bg-zinc-900 px-3 py-2 text-sm text-white placeholder-zinc-600 focus:ring-1 focus:outline-none"
                type="email"
              />
              <p v-if="form.errors.email" class="mt-1 text-xs text-red-400">
                {{ form.errors.email }}
              </p>
            </div>
            <div>
              <label class="mb-1 block text-xs font-semibold text-zinc-400" for="phone"
                >Telefone</label
              >
              <input
                id="phone"
                v-model="form.phone"
                class="focus:border-brand-500 focus:ring-brand-500 w-full rounded-lg border border-zinc-700 bg-zinc-900 px-3 py-2 text-sm text-white placeholder-zinc-600 focus:ring-1 focus:outline-none"
                maxlength="15"
                placeholder="(47) 99999-9999"
                type="tel"
                @input="onPhoneInput"
              />
            </div>
            <div>
              <label class="mb-1 block text-xs font-semibold text-zinc-400" for="message"
                >Mensagem</label
              >
              <textarea
                id="message"
                v-model="form.message"
                required
                class="focus:border-brand-500 focus:ring-brand-500 min-h-28 w-full rounded-lg border border-zinc-700 bg-zinc-900 px-3 py-2 text-sm text-white placeholder-zinc-600 focus:ring-1 focus:outline-none"
              />
              <p v-if="form.errors.message" class="mt-1 text-xs text-red-400">
                {{ form.errors.message }}
              </p>
            </div>
            <button
              class="bg-brand-500 hover:bg-brand-600 inline-flex w-full items-center justify-center gap-2 rounded-lg py-2.5 text-sm font-bold text-white transition-colors disabled:opacity-60"
              :disabled="form.processing"
            >
              <Mail class="h-4 w-4" />
              Enviar mensagem
            </button>
          </form>
        </div>

        <!-- botão favoritar separado (fundo branco) -->
        <button
          v-if="user"
          class="inline-flex w-full items-center justify-center gap-2 rounded-2xl border-2 py-3 text-sm font-bold transition-colors disabled:opacity-60"
          :class="
            listing.is_favorited
              ? 'border-rose-200 bg-rose-50 text-rose-700 hover:bg-rose-100'
              : 'border-zinc-200 bg-white text-zinc-700 hover:border-zinc-300 hover:bg-zinc-50'
          "
          type="button"
          :disabled="favoriteForm.processing"
          @click="toggleFavorite"
        >
          <Heart class="h-4 w-4" :class="listing.is_favorited ? 'fill-current' : ''" />
          {{ listing.is_favorited ? 'Remover dos favoritos' : 'Salvar nos favoritos' }}
        </button>
        <Link
          v-else
          class="inline-flex w-full items-center justify-center gap-2 rounded-2xl border-2 border-zinc-200 bg-white py-3 text-sm font-bold text-zinc-700 hover:bg-zinc-50"
          href="/login"
        >
          <Heart class="h-4 w-4" />
          Entrar para favoritar
        </Link>
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
