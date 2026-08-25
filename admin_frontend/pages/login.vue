<template>
  <login-layout>
    <form
        ref="loginForm"
        @submit.prevent="checkForm()"
        :class="{'has-error': hasError}"
        class="login-form"
    >
      <h4 class="mb-30 mb-sm-15">
        {{ $t('profile.wb') }}
      </h4>

      <error-formatter/>

      <div class="input-wrapper">
        <div class="icon-input">
          <i
              class="icon email-icon"
          />
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

      <div class="input-wrapper">
        <password-field
            :value="password"
            :is-invalid="isLengthInvalid"
            @change-password="passwordChanged"
        />
        <span
            class="error"
            v-if="!!!password && hasError"
        >
          {{ $t('category.req', { type: $t('user.pass')}) }}
        </span>
        <span
            class="error"
            v-else-if="isLengthInvalid && hasError"
        >
          {{ $t('user.inPass') }}
        </span>
      </div>
      <div class="sided mt-15">
        <label class="checkbox">
          <input
              type="checkbox"
              v-model="rememberToken"

          >
          {{ $t('profile.rm') }}
        </label>

        <nuxt-link
            to="/forgot-password"
            class="link"
        >
          {{ $t('profile.fp') }}
        </nuxt-link>
      </div>

      <ajax-button
          :activate-btn="true"
          class="mt-20 primary-btn"
          :fetching-data="formSubmitting"
          :loading-text="$t('dataPage.logging')"
          :text="$t('dataPage.sign')"
      />
      <client-only>
        <div
            v-if="isDemo"
        >
          <button
              @click.prevent="setCredentials(-1)"
              class="outline-btn block mtb-15 w-100"
          >
            {{ $t('profile.laa') }}
          </button>

          <button
              @click.prevent="setCredentials(1)"
              class="outline-btn block w-100"
          >
            {{ $t('profile.lav') }}
          </button>

        </div>
      </client-only>


    </form>
  </login-layout>
</template>

<script setup>
  import {useCommonStore} from '~/store/common';
  import {useAdminStore} from '~/store/admin';
  import {useUiStore} from '~/store/ui';
  import {useIndexStore} from '~/store/index';
  import {useAuthStore} from '~/store/auth';
  import {useValidationHelper} from "~/composables/useValidationHelper";
  import {onMounted} from "vue";
  import {storeToRefs} from "pinia";
  import LoginLayout from "~/layouts/login-layout";

  definePageMeta({
    layout: 'login-layout',
    middleware: ['common-middleware', 'non-auth']
  });

  const uiStore = useUiStore();
  const {setErrors, settingRemember} = uiStore;
  const {rememberMe} = storeToRefs(uiStore);

  const commonStore = useCommonStore();
  const {getRequest, setRequest, unAuthPost} = commonStore;

  const indexStore = useIndexStore();
  const {settingSiteData} = indexStore;

  const adminStore = useAdminStore();
  const {setProfile} = adminStore;

  const authStore = useAuthStore();
  const {setToken} = authStore;


  const isDemo = computed(() => {
    const config = useRuntimeConfig();
    return config.public.isDemo;
  });


  const email = ref('');
  const password = ref('');
  const rememberToken = ref('');
  const hasError = ref(false);
  const formSubmitting = ref(false);
  const redirectionUrl = ref('');

  const {isValidEmail, isValidLength} = useValidationHelper();

  const isInvalidEmail = computed(() => {
    return (email.value && !isValidEmail(email.value));
  });

  const isLengthInvalid = computed(() => {
    return (password.value && !isValidLength(password.value));
  });


  const passwordChanged = (evt) => {
    password.value = evt
  };

  const setCredentials = (data) => {
    if (data < 0) {
      email.value = 'admin@mail.com'
      password.value = '123456'
    } else {
      email.value = 'vendor@mail.com'
      password.value = '123456'
    }
    checkForm()
  };

  const checkForm = async () => {
    hasError.value = false

    if (!email.value || !password.value || isInvalidEmail.value || isLengthInvalid.value) {
      hasError.value = true
      return false
    }
    settingRemember(rememberToken.value)
    formSubmitting.value = true


    const res = await unAuthPost({
      params: {
        remember_token: rememberToken.value,
        password: password.value,
        email: email.value
      },
      api: 'login'
    });

    formSubmitting.value = false

    if (res?.status === 201) {
      return
    }
    setToken(res.token)

    const rememberExpires = useCookie('remember_expires', {
      maxAge: 60 * 60 * 24 * 7,
    })

    rememberExpires.value = rememberToken.value ? 7 : null

    formSubmitting.value = true
    const data = await getRequest({
      params: {},
      api: 'profile'
    });

    formSubmitting.value = false

    if (data) {
      await settingSiteData(data)
      setProfile(data)
      hasError.value = false
      setErrors()
      return navigateTo(redirectionUrl.value || '/');
    }

    formSubmitting.value = false
  };


  onMounted(() => {
    redirectionUrl.value = localStorage.getItem('redirection_url')
    if (redirectionUrl.value) {
      //localStorage.removeItem('redirection_url');
    }

    rememberToken.value = (rememberMe.value === 'true') ? true : '';
    setErrors();
  });

</script>
