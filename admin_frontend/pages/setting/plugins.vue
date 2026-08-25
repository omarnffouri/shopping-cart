<template>
  <PartialsSettingLayout
    active-route="plugins"
    class="mb-5"
  >
    <template v-slot:rightArea>
      <PartialsPluginsSetting />
    </template>
  </PartialsSettingLayout>
</template>

<script setup>
  definePageMeta({
    middleware: ['common-middleware', 'auth'],
    layout: 'default',
  });
</script>


