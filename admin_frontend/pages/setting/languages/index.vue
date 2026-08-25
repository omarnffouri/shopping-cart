<template>
  <PartialsSettingLayout
    active-route="languages"
    class="mb-5"
  >
    <template v-slot:rightArea>
      <PartialsLanguageSetting/>
    </template>
  </PartialsSettingLayout>
</template>

<script setup>
  definePageMeta({
    middleware: ['common-middleware', 'auth'],
    layout: 'default',
  });
</script>


