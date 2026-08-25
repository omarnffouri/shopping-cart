<template>
  <PartialsSettingLayout
    active-route="social-login"
    class="mb-5"
  >
    <template v-slot:rightArea>
      <PartialsSocialLoginSetting />
    </template>
  </PartialsSettingLayout>
</template>

<script setup>
  definePageMeta({
    middleware: ['common-middleware', 'auth'],
    layout: 'default',
  });
</script>


