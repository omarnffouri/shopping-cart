<template>
  <PartialsSettingLayout
    active-route="media-storage"
    class="mb-5"
  >
    <template v-slot:rightArea>
      <PartialsMediaStorageSetting/>
    </template>
  </PartialsSettingLayout>
</template>

<script setup>
  definePageMeta({
    middleware: ['common-middleware', 'auth'],
    layout: 'default',
  });
</script>


