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
  <div class="min-h-screen bg-slate-50 text-slate-900">
    <header class="border-b border-slate-200 bg-white/95 backdrop-blur">
      <nav class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-4 px-6 py-4">
        <Link href="/" class="inline-flex items-center gap-2 text-lg font-bold tracking-tight">
          <span class="bg-brand-600 inline-flex h-7 w-7 items-center justify-center rounded-lg">
            <Tag class="h-4 w-4 text-white" />
          </span>
          {{ page.props.appName }}
        </Link>
        <div class="flex flex-wrap items-center gap-3 text-sm">
          <Link
            class="hover:bg-brand-50 hover:text-brand-700 inline-flex items-center gap-2 rounded-md px-3 py-2 text-slate-700"
            href="/anuncios"
          >
            <Search class="h-4 w-4" />
            Anuncios
          </Link>
          <template v-if="user">
            <Link
              class="hover:bg-brand-50 hover:text-brand-700 inline-flex items-center gap-2 rounded-md px-3 py-2 text-slate-700"
              href="/favoritos"
            >
              <Heart class="h-4 w-4" />
              Favoritos
            </Link>
            <Link
              class="hover:bg-brand-50 hover:text-brand-700 inline-flex items-center gap-2 rounded-md px-3 py-2 text-slate-700"
              href="/dashboard"
            >
              <LayoutDashboard class="h-4 w-4" />
              Painel
            </Link>
            <Link
              class="bg-brand-600 hover:bg-brand-700 inline-flex items-center gap-2 rounded-md px-3 py-2 font-medium text-white"
              href="/admin/anuncios/create"
            >
              <Plus class="h-4 w-4" />
              Novo
            </Link>
            <button
              class="hover:bg-brand-50 hover:text-brand-700 inline-flex items-center gap-2 rounded-md px-3 py-2 text-slate-700"
              type="button"
              @click="logout"
            >
              <LogOut class="h-4 w-4" />
              Sair
            </button>
          </template>
          <template v-else>
            <Link
              class="hover:bg-brand-50 hover:text-brand-700 rounded-md px-3 py-2 text-slate-700"
              href="/login"
              >Entrar</Link
            >
            <Link
              class="bg-brand-600 hover:bg-brand-700 inline-flex items-center gap-2 rounded-md px-3 py-2 font-medium text-white"
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
          class="flex items-start gap-3 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900"
        >
          <CheckCircle2 class="mt-0.5 h-4 w-4 shrink-0" />
          <span>{{ flash.success }}</span>
        </div>
        <div
          v-if="flash.error"
          class="flex items-start gap-3 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900"
        >
          <AlertCircle class="mt-0.5 h-4 w-4 shrink-0" />
          <span>{{ flash.error }}</span>
        </div>
      </div>
      <slot />
    </main>
  </div>
</template>
