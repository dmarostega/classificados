<script setup lang="ts">
import type { ListingImage } from '@/types';
import { ChevronLeft, ChevronRight, X } from '@lucide/vue';
import { computed, nextTick, onBeforeUnmount, onMounted, watch } from 'vue';

const props = defineProps<{
  images: ListingImage[];
  modelValue: boolean;
  selectedImageUrl: string | null;
  listingTitle: string;
}>();

const emit = defineEmits<{
  'update:modelValue': [value: boolean];
  select: [image: ListingImage];
}>();

const closeButtonId = 'listing-image-lightbox-close';
const selectedImageIndex = computed(() =>
  props.images.findIndex((image) => image.url === props.selectedImageUrl),
);
const selectedImage = computed(() => props.images[selectedImageIndex.value] ?? null);
const canNavigate = computed(() => props.images.length > 1);

const close = (): void => {
  emit('update:modelValue', false);
};

const selectImageAt = (index: number): void => {
  const image = props.images[index];

  if (image) {
    emit('select', image);
  }
};

const showPreviousImage = (): void => {
  if (!canNavigate.value) {
    return;
  }

  selectImageAt((selectedImageIndex.value - 1 + props.images.length) % props.images.length);
};

const showNextImage = (): void => {
  if (!canNavigate.value) {
    return;
  }

  selectImageAt((selectedImageIndex.value + 1) % props.images.length);
};

const handleKeydown = (event: KeyboardEvent): void => {
  if (!props.modelValue) {
    return;
  }

  if (event.key === 'Escape') {
    close();
  }

  if (event.key === 'ArrowLeft') {
    showPreviousImage();
  }

  if (event.key === 'ArrowRight') {
    showNextImage();
  }
};

watch(
  () => props.modelValue,
  async (isOpen) => {
    if (isOpen) {
      await nextTick();
      document.getElementById(closeButtonId)?.focus();
    }
  },
);

onMounted(() => {
  window.addEventListener('keydown', handleKeydown);
});

onBeforeUnmount(() => {
  window.removeEventListener('keydown', handleKeydown);
});
</script>

<template>
  <Teleport to="body">
    <div
      v-if="modelValue && selectedImage"
      class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/90 p-4"
      role="dialog"
      aria-modal="true"
      aria-labelledby="listing-image-lightbox-title"
      @click.self="close"
    >
      <h2 id="listing-image-lightbox-title" class="sr-only">
        Imagem ampliada de {{ listingTitle }}
      </h2>
      <div class="relative flex h-full w-full max-w-6xl items-center justify-center">
        <button
          :id="closeButtonId"
          class="absolute top-0 right-0 z-10 rounded-full bg-white p-2 text-slate-900 shadow hover:bg-slate-100 focus:ring-2 focus:ring-white focus:outline-none"
          type="button"
          aria-label="Fechar visualizacao ampliada"
          @click="close"
        >
          <X class="h-5 w-5" />
        </button>

        <button
          v-if="canNavigate"
          class="absolute left-0 z-10 rounded-full bg-white p-2 text-slate-900 shadow hover:bg-slate-100 focus:ring-2 focus:ring-white focus:outline-none"
          type="button"
          aria-label="Imagem anterior"
          @click="showPreviousImage"
        >
          <ChevronLeft class="h-6 w-6" />
        </button>

        <img
          :src="selectedImage.url"
          :alt="selectedImage.alt_text || listingTitle"
          class="max-h-full max-w-full object-contain"
        />

        <button
          v-if="canNavigate"
          class="absolute right-0 z-10 rounded-full bg-white p-2 text-slate-900 shadow hover:bg-slate-100 focus:ring-2 focus:ring-white focus:outline-none"
          type="button"
          aria-label="Proxima imagem"
          @click="showNextImage"
        >
          <ChevronRight class="h-6 w-6" />
        </button>
      </div>
    </div>
  </Teleport>
</template>
