<script setup lang="ts">
import type { SelectOption } from '@/types';
import { Check, ChevronDown, X } from '@lucide/vue';
import { computed, ref } from 'vue';

const props = withDefaults(
  defineProps<{
    id?: string;
    modelValue: string | number | null;
    options: SelectOption[];
    placeholder?: string;
    searchPlaceholder?: string;
    searchable?: boolean;
    clearable?: boolean;
    disabled?: boolean;
  }>(),
  {
    id: undefined,
    placeholder: 'Selecione',
    searchPlaceholder: 'Buscar...',
    searchable: true,
    clearable: false,
    disabled: false,
  },
);

const emit = defineEmits<{ 'update:modelValue': [value: string | number | null] }>();

const isOpen = ref(false);
const search = ref('');

const normalizeSearch = (value: string): string =>
  value
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .toLowerCase();

const normalizedOptions = computed(() =>
  props.options.map((option) => ({
    ...option,
    value: option.value ?? option.id ?? option.slug ?? option.name ?? '',
    label: option.label ?? option.name ?? String(option.value ?? ''),
  })),
);

const selectedOption = computed(() =>
  normalizedOptions.value.find((option) => String(option.value) === String(props.modelValue ?? '')),
);

const filteredOptions = computed(() => {
  const term = normalizeSearch(search.value.trim());

  if (!term || !props.searchable) {
    return normalizedOptions.value;
  }

  return normalizedOptions.value.filter((option) => normalizeSearch(option.label).includes(term));
});

const open = (): void => {
  if (!props.disabled) {
    isOpen.value = true;
  }
};

const close = (event: FocusEvent): void => {
  const nextTarget = event.relatedTarget as Node | null;
  const currentTarget = event.currentTarget as HTMLElement;

  if (nextTarget && currentTarget.contains(nextTarget)) {
    return;
  }

  isOpen.value = false;
  search.value = '';
};

const selectOption = (value: string | number): void => {
  emit('update:modelValue', value);
  isOpen.value = false;
  search.value = '';
};

const clear = (): void => {
  emit('update:modelValue', null);
  search.value = '';
};
</script>

<template>
  <div class="relative" @focusout="close">
    <button
      :id="id"
      class="flex min-h-10 w-full items-center justify-between gap-2 rounded-md border bg-white px-3 py-2 text-left disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-500"
      type="button"
      :disabled="disabled"
      @click="isOpen ? (isOpen = false) : open()"
    >
      <span class="truncate" :class="selectedOption ? 'text-slate-900' : 'text-slate-500'">
        {{ selectedOption?.label || placeholder }}
      </span>
      <span class="flex items-center gap-1">
        <X
          v-if="clearable && selectedOption && !disabled"
          class="h-4 w-4 text-slate-400 hover:text-slate-700"
          @click.stop="clear"
        />
        <ChevronDown class="h-4 w-4 text-slate-500" />
      </span>
    </button>

    <div
      v-if="isOpen"
      class="absolute z-30 mt-1 max-h-72 w-full overflow-hidden rounded-md border bg-white shadow-lg"
    >
      <div v-if="searchable" class="border-b p-2">
        <input
          v-model="search"
          class="w-full rounded-md border px-3 py-2 text-sm"
          :placeholder="searchPlaceholder"
          type="search"
          @keydown.stop
        />
      </div>
      <div class="max-h-56 overflow-y-auto py-1">
        <button
          v-for="option in filteredOptions"
          :key="String(option.value)"
          class="flex w-full items-center justify-between gap-3 px-3 py-2 text-left text-sm hover:bg-slate-100"
          type="button"
          @click="selectOption(option.value)"
        >
          <span class="truncate">{{ option.label }}</span>
          <Check
            v-if="String(option.value) === String(modelValue ?? '')"
            class="h-4 w-4 shrink-0 text-slate-900"
          />
        </button>
        <p v-if="filteredOptions.length === 0" class="px-3 py-3 text-sm text-slate-500">
          Nenhuma opcao encontrada.
        </p>
      </div>
    </div>
  </div>
</template>
