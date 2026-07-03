<script setup lang="ts">
import type { ListingDetail, SelectOption } from '@/types';
import { useForm } from '@inertiajs/vue3';
import { ImagePlus, Trash2 } from '@lucide/vue';
import { computed, ref } from 'vue';

const props = defineProps<{
  listing?: ListingDetail;
  categories: SelectOption[];
  statuses: SelectOption[];
  submitUrl: string;
  method: 'post' | 'put';
}>();

const emit = defineEmits<{ saved: [] }>();
const imageInput = ref<HTMLInputElement | null>(null);
const existingImages = ref(props.listing?.images || []);
const files = ref<File[]>([]);

const form = useForm({
  category_id: props.listing?.category_id || props.categories[0]?.id || '',
  title: props.listing?.title || '',
  description: props.listing?.description || '',
  price: props.listing?.price_value || '',
  city: props.listing?.city || '',
  state: props.listing?.state || '',
  contact_name: props.listing?.contact_name || '',
  contact_email: props.listing?.contact_email || '',
  contact_phone: props.listing?.contact_phone || '',
  status: props.listing?.status || 'draft',
  expires_at: props.listing?.expires_at || '',
  images: [] as File[],
  remove_image_ids: [] as number[],
});

const selectedFileNames = computed(() => files.value.map((file) => file.name).join(', '));

const pickImages = (): void => imageInput.value?.click();

const onImagesSelected = (event: Event): void => {
  const input = event.target as HTMLInputElement;
  files.value = Array.from(input.files || []);
  form.images = files.value;
};

const removeExistingImage = (id: number): void => {
  existingImages.value = existingImages.value.filter((image) => image.id !== id);
  form.remove_image_ids.push(id);
};

const submit = (): void => {
  form
    .transform((data) => ({
      ...data,
      _method: props.method === 'put' ? 'put' : undefined,
    }))
    .post(props.submitUrl, {
      forceFormData: true,
      preserveScroll: true,
      onSuccess: () => emit('saved'),
    });
};
</script>

<template>
  <form class="space-y-6" @submit.prevent="submit">
    <div class="grid gap-5 lg:grid-cols-[1fr_320px]">
      <section class="space-y-5 rounded-lg border bg-white p-5">
        <div>
          <label class="mb-1 block text-sm font-medium" for="title">Titulo</label>
          <input
            id="title"
            v-model="form.title"
            class="w-full rounded-md border px-3 py-2"
            maxlength="120"
            required
          />
          <p v-if="form.errors.title" class="text-sm text-red-700">{{ form.errors.title }}</p>
        </div>

        <div>
          <label class="mb-1 block text-sm font-medium" for="description">Descricao</label>
          <textarea
            id="description"
            v-model="form.description"
            class="min-h-48 w-full rounded-md border px-3 py-2"
            required
          />
          <p v-if="form.errors.description" class="text-sm text-red-700">
            {{ form.errors.description }}
          </p>
        </div>

        <div class="grid gap-4 md:grid-cols-3">
          <div>
            <label class="mb-1 block text-sm font-medium" for="category">Categoria</label>
            <select
              id="category"
              v-model="form.category_id"
              class="w-full rounded-md border px-3 py-2"
              required
            >
              <option v-for="category in categories" :key="category.id" :value="category.id">
                {{ category.name }}
              </option>
            </select>
          </div>
          <div>
            <label class="mb-1 block text-sm font-medium" for="price">Preco</label>
            <input
              id="price"
              v-model="form.price"
              class="w-full rounded-md border px-3 py-2"
              inputmode="decimal"
              required
            />
            <p v-if="form.errors.price" class="text-sm text-red-700">{{ form.errors.price }}</p>
          </div>
          <div>
            <label class="mb-1 block text-sm font-medium" for="status">Status</label>
            <select
              id="status"
              v-model="form.status"
              class="w-full rounded-md border px-3 py-2"
              required
            >
              <option v-for="status in statuses" :key="status.value" :value="status.value">
                {{ status.label }}
              </option>
            </select>
          </div>
        </div>

        <div class="grid gap-4 md:grid-cols-[1fr_90px]">
          <div>
            <label class="mb-1 block text-sm font-medium" for="city">Cidade</label>
            <input
              id="city"
              v-model="form.city"
              class="w-full rounded-md border px-3 py-2"
              required
            />
          </div>
          <div>
            <label class="mb-1 block text-sm font-medium" for="state">UF</label>
            <input
              id="state"
              v-model="form.state"
              class="w-full rounded-md border px-3 py-2 uppercase"
              maxlength="2"
              required
            />
          </div>
        </div>
      </section>

      <aside class="space-y-5 rounded-lg border bg-white p-5">
        <div>
          <label class="mb-1 block text-sm font-medium" for="contact_name">Responsavel</label>
          <input
            id="contact_name"
            v-model="form.contact_name"
            class="w-full rounded-md border px-3 py-2"
            required
          />
        </div>
        <div>
          <label class="mb-1 block text-sm font-medium" for="contact_email"
            >E-mail de contato</label
          >
          <input
            id="contact_email"
            v-model="form.contact_email"
            class="w-full rounded-md border px-3 py-2"
            type="email"
          />
          <p v-if="form.errors.contact_email" class="text-sm text-red-700">
            {{ form.errors.contact_email }}
          </p>
        </div>
        <div>
          <label class="mb-1 block text-sm font-medium" for="contact_phone">Telefone</label>
          <input
            id="contact_phone"
            v-model="form.contact_phone"
            class="w-full rounded-md border px-3 py-2"
            type="tel"
          />
        </div>
        <div>
          <label class="mb-1 block text-sm font-medium" for="expires_at">Expira em</label>
          <input
            id="expires_at"
            v-model="form.expires_at"
            class="w-full rounded-md border px-3 py-2"
            type="date"
          />
        </div>
      </aside>
    </div>

    <section class="rounded-lg border bg-white p-5">
      <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
          <h2 class="font-semibold">Imagens</h2>
          <p class="text-sm text-slate-500">Ate 8 imagens. Elas serao otimizadas para WebP.</p>
        </div>
        <button
          class="inline-flex items-center gap-2 rounded-md border px-4 py-2"
          type="button"
          @click="pickImages"
        >
          <ImagePlus class="h-4 w-4" />
          Selecionar imagens
        </button>
        <input
          ref="imageInput"
          class="hidden"
          multiple
          accept="image/*"
          type="file"
          @change="onImagesSelected"
        />
      </div>

      <p v-if="selectedFileNames" class="mt-3 text-sm text-slate-500">
        Selecionadas: {{ selectedFileNames }}
      </p>
      <p v-if="form.errors.images" class="mt-2 text-sm text-red-700">{{ form.errors.images }}</p>

      <div v-if="existingImages.length" class="mt-5 grid gap-4 sm:grid-cols-2 md:grid-cols-4">
        <div
          v-for="image in existingImages"
          :key="image.id"
          class="overflow-hidden rounded-md border"
        >
          <img
            :src="image.url"
            :alt="image.alt_text || form.title"
            class="aspect-[4/3] w-full object-cover"
          />
          <button
            class="inline-flex w-full items-center justify-center gap-2 border-t px-3 py-2 text-sm text-red-700"
            type="button"
            @click="removeExistingImage(image.id)"
          >
            <Trash2 class="h-4 w-4" />
            Remover
          </button>
        </div>
      </div>
    </section>

    <div class="flex justify-end gap-3">
      <button
        class="rounded-md bg-slate-900 px-5 py-2 font-medium text-white disabled:opacity-60"
        :disabled="form.processing"
      >
        Salvar anuncio
      </button>
    </div>
  </form>
</template>
