<template>
  <PartialsDataPage
    ref="dataPageRef"
    set-api="setSubscriptionEmailFormat"
    get-api="getSubscriptionEmailFormat"
    route-name="subscription-email-formats"
    :name="$t('dataPage.sefUp')"
    :validation-keys="['title', 'subject', 'body']"
    :result="result"
    :emit-before-submit="true"
    gate="subscription_email_format"
    empty-store-variable="allSubscriptionEmailFormats"
    @result="result = $event"
    @before-submit="submitForm"
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

        <label>{{ $t('util.sub') }}</label>
        <input
          type="text"
          :placeholder="$t('util.sub')"
          name="subject"
          v-model="result.subject"
          :class="{invalid: !!!result.subject && hasError}"
        >
        <span
          class="error"
          v-if="!!!result.subject && hasError"
        >
          {{ $t('category.req', { type: $t('util.sub')}) }}
        </span>
      </div>

      <!--<p
        v-if="viewAsHtml"
        class="info-msg mb-20 mb-sm-15"
      >
        Supported tags: h1, h2...h6, p, strong, em, u, s, blockquote, pre, ul, li, ol, br, a.
      </p>-->

      <div class="input-wrapper ">
        <label>
          <span class="mr-10">{{ $t('profile.body') }}</span>
          <button
            type="button"
            class="btn-clear"
            @click.prevent="viewHtml"
          >
            <b v-if="viewAsHtml">{{ $t('profile.vrh') }}</b>
            <b v-else>{{ $t('profile.vah') }}</b>
          </button>
        </label>

        <div
          v-if="!viewAsHtml"
          v-dompurify-html="result.body"
          class="textarea"
        />
        <textarea
          v-else
          :placeholder="$t('profile.ebah')"
          v-model="result.body"
          :class="{invalid: !!!result.body && hasError}"
        />
        <span
          class="error"
          v-if="!!!result.body && hasError"
        >
          {{ $t('category.req', { type: $t('profile.body')}) }}
        </span>
      </div>

    </template>
  </PartialsDataPage>
</template>

<script setup>
  import DOMPurify from 'dompurify'

  definePageMeta({
    middleware: ['common-middleware', 'auth'],
    layout: 'default',
  });

  const viewAsHtml = ref(true);
  const loading = ref(false);
  const result = ref({
    id: '',
    title: '',
    subject: '',
    body: ''
  });

  const dataPageRef = ref(null);

  const submitForm = () => {
    result.value.body = DOMPurify.sanitize(result.value.body)
    dataPageRef.value.checkForm()
  };

  const viewHtml = () => {
    viewAsHtml.value = !viewAsHtml.value
  };
</script>
