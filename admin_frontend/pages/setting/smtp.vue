<template>
  <PartialsSettingLayout
    active-route="smtp"
    class="mb-5"
  >
    <template v-slot:rightArea>
      <PartialsSmtpSetting/>
    </template>
  </PartialsSettingLayout>
</template>

<script setup>
  definePageMeta({
    middleware: ['common-middleware', 'auth'],
    layout: 'default',
  });
</script>


