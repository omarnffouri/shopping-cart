<template>
  <form
    @submit.prevent="checkForm()"
    :class="{'has-error': hasError}"
    class="login-form"
  >
    <h4 class="mb-30 mb-sm-15">
      {{ $t('profile.sendCode') }}
    </h4>

    <error-formatter/>

    <div class="input-wrapper">
      <div class="icon-input">
        <i class="icon email-icon">&nbsp;</i>
        <input
          type="text"
          :placeholder="$t('fSale.email')"
          v-model.trim="email"
          :class="{invalid: !!!email || isInvalidEmail}"
        >
      </div>

      <span
        class="error"
        v-if="!!!email && hasError"
      >
        {{ $t('category.req', { type: $t('fSale.email')}) }}
      </span>
      <span
        class="error"
        v-else-if="isInvalidEmail && hasError"
      >
        {{ $t('user.isValid', { type: $t('fSale.email') }) }}
      </span>
    </div>

    <div class="dply-felx j-right mt-15">
      <nuxt-link
        to="/login"
        class="link"
      >
        {{ $t('profile.ltya') }}
      </nuxt-link>
    </div>

    <ajax-button
      class="mt-20 primary-btn"
      :fetching-data="formSubmitting"
      :loading-text="$t('profile.se')"
      :text="$t('profile.svc')"
      :activate-btn="true"
    />

  </form>
</template>

<script setup>
  import {useCommonStore} from '~/store/common';
  import {useUiStore} from '~/store/ui';

  import {useValidationHelper} from "~/composables/useValidationHelper";
  import {onMounted} from "vue";

  definePageMeta({
    layout: 'login-layout',
    middleware: ['common-middleware', 'non-auth']
  });

  const uiStore = useUiStore();
  const {setErrors} = uiStore;

  const commonStore = useCommonStore();
  const {setRequest} = commonStore;

  const email = ref('');
  const password = ref('');
  const hasError = ref(false);
  const formSubmitting = ref(false);

  const {isValidEmail, isValidLength} = useValidationHelper();

  const isInvalidEmail = computed(() => {
    return (email.value && !isValidEmail(email.value));
  });

  const isLengthInvalid = computed(() => {
    return (password.value && !isValidLength(password.value));
  });

  const checkForm = async () => {
    hasError.value = false
    if (!email.value || isInvalidEmail.value) {
      hasError.value = true
      return false
    }

    formSubmitting.value = true
    const data = await setRequest({
      params: {
        email: email.value
      },
      api: 'forgotPassword'
    });

    if (data) {
      navigateTo(`/verify-code?email=${email.value}`);
    }
    formSubmitting.value = false
  };

  onMounted(() => {
    setErrors();
  });
</script>
