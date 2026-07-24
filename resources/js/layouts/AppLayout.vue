<script setup lang="ts">
import type { PageProps } from '@/types';
import { Link, router, usePage } from '@inertiajs/vue3';
import {
  AlertCircle,
  CheckCircle2,
  Heart,
  LayoutDashboard,
  LogOut,
  Plus,
  Search,
  Tag,
  UserPlus,
} from '@lucide/vue';
import { computed } from 'vue';

const page = usePage<PageProps>();
const user = computed(() => page.props.auth.user);
const flash = computed(() => page.props.flash);
const logout = (): void => router.post('/logout');
</script>

<template>
  <div class="min-h-screen bg-zinc-50 text-zinc-900">
    <header class="sticky top-0 z-40 border-b border-zinc-800 bg-zinc-950">
      <nav class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-4 px-6 py-3">
        <Link href="/" class="inline-flex items-center gap-2.5 font-bold tracking-tight">
          <span class="bg-brand-500 inline-flex h-7 w-7 items-center justify-center rounded-md">
            <Tag class="h-4 w-4 text-white" />
          </span>
          <span class="text-white">{{ page.props.appName }}</span>
        </Link>

        <div class="flex flex-wrap items-center gap-1 text-sm">
          <Link
            class="inline-flex items-center gap-1.5 rounded-md px-3 py-2 text-zinc-300 transition-colors hover:bg-zinc-800 hover:text-white"
            href="/anuncios"
          >
            <Search class="h-4 w-4" />
            Anuncios
          </Link>
          <template v-if="user">
            <Link
              class="inline-flex items-center gap-1.5 rounded-md px-3 py-2 text-zinc-300 transition-colors hover:bg-zinc-800 hover:text-white"
              href="/favoritos"
            >
              <Heart class="h-4 w-4" />
              Favoritos
            </Link>
            <Link
              class="inline-flex items-center gap-1.5 rounded-md px-3 py-2 text-zinc-300 transition-colors hover:bg-zinc-800 hover:text-white"
              href="/dashboard"
            >
              <LayoutDashboard class="h-4 w-4" />
              Painel
            </Link>
            <Link
              class="bg-brand-500 hover:bg-brand-600 ml-2 inline-flex items-center gap-1.5 rounded-md px-4 py-2 font-semibold text-white transition-colors"
              href="/admin/anuncios/create"
            >
              <Plus class="h-4 w-4" />
              Anunciar
            </Link>
            <button
              class="inline-flex items-center gap-1.5 rounded-md px-3 py-2 text-zinc-400 transition-colors hover:bg-zinc-800 hover:text-white"
              type="button"
              @click="logout"
            >
              <LogOut class="h-4 w-4" />
              Sair
            </button>
          </template>
          <template v-else>
            <Link
              class="rounded-md px-3 py-2 text-zinc-300 transition-colors hover:bg-zinc-800 hover:text-white"
              href="/login"
              >Entrar</Link
            >
            <Link
              class="bg-brand-500 hover:bg-brand-600 ml-2 inline-flex items-center gap-1.5 rounded-md px-4 py-2 font-semibold text-white transition-colors"
              href="/register"
            >
              <UserPlus class="h-4 w-4" />
              Criar conta
            </Link>
          </template>
        </div>
      </nav>
    </header>

    <main class="mx-auto max-w-7xl px-6 py-8">
      <div v-if="flash.success || flash.error" class="mb-6">
        <div
          v-if="flash.success"
          class="flex items-start gap-3 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900"
        >
          <CheckCircle2 class="mt-0.5 h-4 w-4 shrink-0" />
          <span>{{ flash.success }}</span>
        </div>
        <div
          v-if="flash.error"
          class="flex items-start gap-3 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900"
        >
          <AlertCircle class="mt-0.5 h-4 w-4 shrink-0" />
          <span>{{ flash.error }}</span>
        </div>
      </div>
      <slot />
    </main>
  </div>
</template>
