<script setup>
import { Link } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';

defineOptions({
    inheritAttrs: false,
});
</script>

<template>
    <Link href="/" class="inline-flex items-center">
        <ApplicationLogo v-bind="$attrs" class="block size-20" />
    </Link>
</template>
