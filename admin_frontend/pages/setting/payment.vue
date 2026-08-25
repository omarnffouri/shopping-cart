<template>
  <PartialsSettingLayout
    active-route="payment"
    class="mb-5"
  >
    <template v-slot:rightArea>
      <PartialsPaymentSetting/>
    </template>
  </PartialsSettingLayout>
</template>

<script setup>
  definePageMeta({
    middleware: ['common-middleware', 'auth'],
    layout: 'default',
  });
</script>


