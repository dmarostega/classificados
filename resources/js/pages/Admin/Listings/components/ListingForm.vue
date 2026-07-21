<script setup lang="ts">
import SearchSelect from '@/components/SearchSelect.vue';
import { formatPhone } from '@/composables/useInputMasks';
import type { ListingDetail, SelectOption } from '@/types';
import { useForm } from '@inertiajs/vue3';
import { ImagePlus, Trash2 } from '@lucide/vue';
import { computed, ref, watch } from 'vue';

const props = defineProps<{
  listing?: ListingDetail;
  categories: SelectOption[];
  cities: SelectOption[];
  states: SelectOption[];
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
  accepts_offers: props.listing?.accepts_offers || false,
  quick_sale: props.listing?.quick_sale || false,
  negotiable_price: props.listing?.negotiable_price || false,
  easy_pickup: props.listing?.easy_pickup || false,
  city: props.listing?.city || '',
  state: props.listing?.state || '',
  contact_name: props.listing?.contact_name || '',
  contact_email: props.listing?.contact_email || '',
  contact_phone: formatPhone(props.listing?.contact_phone),
  status: props.listing?.status || 'draft',
  expires_at: props.listing?.expires_at || '',
  images: [] as File[],
  remove_image_ids: [] as number[],
});

const selectedFileNames = computed(() => files.value.map((file) => file.name).join(', '));
const imageErrors = computed(() =>
  Object.entries(form.errors)
    .filter(([key]) => key === 'images' || key.startsWith('images.'))
    .map(([, message]) => message),
);
const categoryOptions = computed(() =>
  props.categories.map((category) => ({
    value: category.id || '',
    label: category.name || '',
  })),
);
const cityOptions = computed(() =>
  form.state ? props.cities.filter((city) => city.state_code === form.state) : [],
);

watch(
  () => props.listing?.images,
  (images) => {
    existingImages.value = images || [];
  },
);

watch(
  () => form.state,
  () => {
    if (form.city && !cityOptions.value.some((city) => city.value === form.city)) {
      form.city = '';
    }
  },
);

const pickImages = (): void => imageInput.value?.click();

const onPhoneInput = (event: Event): void => {
  form.contact_phone = formatPhone((event.target as HTMLInputElement).value);
};

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
      onSuccess: () => {
        files.value = [];
        form.images = [];
        form.remove_image_ids = [];

        if (imageInput.value) {
          imageInput.value.value = '';
        }

        emit('saved');
      },
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
            <SearchSelect
              id="category"
              v-model="form.category_id"
              :options="categoryOptions"
              placeholder="Selecione"
              search-placeholder="Buscar categoria"
            />
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
            <SearchSelect
              id="status"
              v-model="form.status"
              :options="statuses"
              placeholder="Selecione"
              search-placeholder="Buscar status"
            />
          </div>
        </div>

        <fieldset class="space-y-2">
          <legend class="text-sm font-medium">Condições comerciais</legend>
          <div class="grid gap-2 sm:grid-cols-2">
            <label class="flex items-center gap-2 text-sm">
              <input v-model="form.accepts_offers" type="checkbox" />
              Aceita proposta
            </label>
            <label class="flex items-center gap-2 text-sm">
              <input v-model="form.quick_sale" type="checkbox" />
              Venda rápida
            </label>
            <label class="flex items-center gap-2 text-sm">
              <input v-model="form.negotiable_price" type="checkbox" />
              Preço negociável
            </label>
            <label class="flex items-center gap-2 text-sm">
              <input v-model="form.easy_pickup" type="checkbox" />
              Retirada facilitada
            </label>
          </div>
        </fieldset>

        <div class="grid gap-4 md:grid-cols-[90px_1fr]">
          <div>
            <label class="mb-1 block text-sm font-medium" for="state">UF</label>
            <SearchSelect
              id="state"
              v-model="form.state"
              :options="states"
              placeholder="UF"
              search-placeholder="Buscar UF"
            />
          </div>
          <div>
            <label class="mb-1 block text-sm font-medium" for="city">Cidade</label>
            <SearchSelect
              id="city"
              v-model="form.city"
              :disabled="!form.state"
              :options="cityOptions"
              :placeholder="form.state ? 'Selecione' : 'Selecione UF'"
              search-placeholder="Buscar cidade"
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
            maxlength="15"
            placeholder="(47) 99999-9999"
            type="tel"
            @input="onPhoneInput"
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
      <div v-if="imageErrors.length" class="mt-2 space-y-1">
        <p v-for="error in imageErrors" :key="error" class="text-sm text-red-700">
          {{ error }}
        </p>
      </div>

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
