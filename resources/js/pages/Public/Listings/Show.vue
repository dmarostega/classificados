<script setup lang="ts">
import SeoHead from '@/components/SeoHead.vue';
import { formatPhone } from '@/composables/useInputMasks';
import AppLayout from '@/layouts/AppLayout.vue';
import type { ListingDetail, SeoData } from '@/types';
import { Link, useForm } from '@inertiajs/vue3';
import { Mail, Phone } from '@lucide/vue';
import { computed, ref } from 'vue';

const props = defineProps<{ seo: SeoData; listing: ListingDetail }>();
const selectedImage = ref(props.listing.images[0]?.url || props.listing.cover_url);
const form = useForm({ name: '', email: '', phone: '', message: '' });
const hasImages = computed(() => props.listing.images.length > 0);

const onPhoneInput = (event: Event): void => {
  form.phone = formatPhone((event.target as HTMLInputElement).value);
};
</script>

<template>
  <SeoHead :seo="seo" />
  <AppLayout>
    <article class="grid gap-8 lg:grid-cols-[1fr_380px]">
      <section class="space-y-5">
        <div class="overflow-hidden rounded-lg border bg-white">
          <div class="aspect-[16/10] bg-slate-100">
            <img
              v-if="selectedImage"
              :src="selectedImage"
              :alt="listing.title"
              class="h-full w-full object-cover"
            />
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
              @click="selectedImage = image.url"
            >
              <img
                :src="image.url"
                :alt="image.alt_text || listing.title"
                class="h-full w-full object-cover"
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
          <p class="mt-2 text-sm text-slate-500">{{ listing.city }} / {{ listing.state }}</p>
          <div class="prose prose-slate mt-6 max-w-none whitespace-pre-line">
            {{ listing.description }}
          </div>
        </div>
      </section>

      <aside class="h-fit rounded-lg border bg-white p-6">
        <h2 class="text-lg font-semibold">Falar com anunciante</h2>
        <p class="mt-1 text-sm text-slate-500">{{ listing.contact_name }}</p>
        <Link
          v-if="listing.advertiser"
          class="mt-3 inline-flex text-sm font-medium underline"
          :href="listing.advertiser.url"
        >
          Ver mais anuncios deste anunciante
        </Link>
        <p
          v-if="listing.contact_phone_masked"
          class="mt-4 inline-flex items-center gap-2 text-sm font-medium"
        >
          <Phone class="h-4 w-4" />
          {{ listing.contact_phone_masked }}
        </p>

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
  </AppLayout>
</template>
