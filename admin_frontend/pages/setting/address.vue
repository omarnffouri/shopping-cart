<template>
  <PartialsSettingLayout
    active-route="address"
    class="mb-5"
  >
    <template v-slot:rightArea>

      <PartialsAddressSetting />

    </template>
  </PartialsSettingLayout>
</template>

<script setup>
  definePageMeta({
    middleware: ['common-middleware', 'auth'],
    layout: 'default',
  });
</script>


