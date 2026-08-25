<template>
  <PartialsDataPage
    ref="dataPageRef"
    set-api="setFooterImageLink"
    get-api="getFooterImageLink"
    set-image-api="setFooterImageLinkImage"
    route-name="footer-links"
    hash="payment-social"
    :name="$t('dataPage.fImgs')"
    :validation-keys="['title', 'link']"
    :file-keys="['id', 'type']"
    :result="result"
    gate="footer_link"
    @result="result = $event"
  >
    <template v-slot:form="{hasError}">

      <div class="input-wrapper">
        <label>{{ $t('index.title') }}</label>
        <input
          type="text"
          :placeholder="$t('index.title')"
          name="title"
          v-model="result.title"
          :class="{invalid: !!!result.title && hasError}"
        >
        <span
          class="error"
          v-if="!!!result.title && hasError"
        >
          {{ $t('category.req', { type: $t('index.title')}) }}
        </span>
      </div>

      <div class="input-wrapper">
        <label>{{ $t('admin.link') }}</label>
        <input
          type="text"
          :placeholder="$t('admin.link')"
          name="link"
          v-model="result.link"
          ref="link"
          :class="{invalid: !!!result.link && hasError}"
        >
        <span
          class="error"
          v-if="!!!result.link && hasError"
        >
          {{ $t('category.req', { type: $t('admin.link')}) }}
        </span>
      </div>

      <div class="input-wrapper">
        <label class="block">{{ $t('category.status') }}</label>
        <dropdown
          :selectedKey="`${result.status}`"
          :options="statusObj"
          @clicked="dropdownSelected"
        />
      </div>

    </template>
  </PartialsDataPage>
</template>

<script setup>
  import {useConstants} from "~/composables/useConstants";

  definePageMeta({
    middleware: ['common-middleware', 'auth'],
    layout: 'default',
  });

  const { statusObj} = useConstants();

  const route = useRoute();
  const result = ref({
    id: '',
      title: '',
      link: '',
      type: route?.query?.type,
      status: 2,
      image: ''
  });

  const dropdownSelected = (data) => {
    result.value.status = data.key
  };
</script>
