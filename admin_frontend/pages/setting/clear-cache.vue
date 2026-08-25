<template>
  <PartialsSettingLayout
    active-route="clear-cache"
    class="mb-5"
  >
    <template v-slot:rightArea>
      <div>
        <p class="dashboard-notice info-msg mb-20 mb-sm-15">
          <b>{{ $t('setting.imp') }}</b>: {{ $t('setting.cClearMsg') }}
        </p>

        <PartialsClearCacheBtn
          class="primary-btn"
        />
      </div>
    </template>
  </PartialsSettingLayout>
</template>

<script setup>
  definePageMeta({
    middleware: ['common-middleware', 'auth'],
    layout: 'default',
  });

</script>


