<script setup>
import { computed } from 'vue';
import { LogIn, User, UserPlus } from '@lucide/vue';
import { Button } from '@/Components/ui/button';

const props = defineProps({
    layout: { type: String, required: true },
    authenticated: { type: String, default: '0' },
    loginUrl: { type: String, required: true },
    registerUrl: { type: String, required: true },
    accountUrl: { type: String, required: true },
    loginLabel: { type: String, required: true },
    registerLabel: { type: String, required: true },
    accountLabel: { type: String, required: true },
});

const isAuthenticated = computed(() => props.authenticated === '1');

const quietButtonClass = 'border-brand/40 text-brand no-underline hover:bg-brand/10 hover:text-brand';
</script>

<template>
    <div v-if="layout === 'desktop'" class="flex flex-wrap items-center justify-end gap-2">
        <Button
            v-if="isAuthenticated"
            as-child
            variant="outline"
            size="sm"
            :class="quietButtonClass"
        >
            <a :href="accountUrl">
                <User data-icon="inline-start" aria-hidden="true" />
                {{ accountLabel }}
            </a>
        </Button>
        <template v-else>
            <Button as-child variant="outline" size="sm" :class="quietButtonClass">
                <a :href="loginUrl">
                    <LogIn data-icon="inline-start" aria-hidden="true" />
                    {{ loginLabel }}
                </a>
            </Button>
            <Button as-child size="sm" class="no-underline">
                <a :href="registerUrl">
                    <UserPlus data-icon="inline-start" aria-hidden="true" />
                    {{ registerLabel }}
                </a>
            </Button>
        </template>
    </div>
    <template v-else>
        <a
            v-if="isAuthenticated"
            :href="accountUrl"
            class="flex items-center gap-2 px-3 py-2 text-brand no-underline"
        >
            <User class="size-4 shrink-0" aria-hidden="true" />
            {{ accountLabel }}
        </a>
        <template v-else>
            <a :href="loginUrl" class="flex items-center gap-2 border-b border-hairline px-3 py-2 text-brand no-underline">
                <LogIn class="size-4 shrink-0" aria-hidden="true" />
                {{ loginLabel }}
            </a>
            <a :href="registerUrl" class="flex items-center gap-2 px-3 py-2 text-brand no-underline">
                <UserPlus class="size-4 shrink-0" aria-hidden="true" />
                {{ registerLabel }}
            </a>
        </template>
    </template>
</template>
