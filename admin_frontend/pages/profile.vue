<template>
  <div
    v-if="$can('profile', 'view')"
    class="tab-sidebar"
  >
    <h5 class="title bold">
      {{ $t('profile.ap') }}
    </h5>

    <div class="dply-felx">
      <ul class="left-area">
        <li v-for="(value, index) in tabs" :key="index"
            :class="{active : value.tabId === activeTab}"
            @click="tabSelect(value)"
        >
          {{ value.title }}
        </li>
      </ul>

      <div class="right-area">
        <form
          v-if="tabId[0] === activeTab"
          class="pos-rel"
          :class="{'has-error': hasError}"
          @submit.prevent="checkForm"
        >
          <error-formatter/>

          <div class="input-wrapper">
            <label>
              {{ $t('user.name') }}
            </label>
            <input
              type="text"
              :placeholder="$t('user.name')"
              name="name"
              v-model="adminData.name"
            >
          </div>

          <div class="input-wrapper">
            <label>
              {{ $t('user.uName') }}
            </label>
            <input
              type="text"
              :placeholder="$t('user.uName')"
              name="username"
              v-model.trim="adminData.username"
              :class="{invalid: !adminData.username && hasError}"
            >
            <span
              class="error"
              v-if="!!!adminData.username && hasError"
            >
              {{ $t('category.req', { type: $t('user.uName')}) }}
            </span>
          </div>

          <div class="input-wrapper">
            <label>
              {{ $t('fSale.email') }}
            </label>
            <input
              type="text"
              :placeholder="$t('fSale.email')"
              v-model.trim="adminData.email"
              :class="{invalid: !adminData.email || isInvalidEmail}"
            >
            <span
              class="error"
              v-if="!!!adminData.email && hasError"
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
            <label>
              {{ $t('user.pass') }}
            </label>
            <password-field
              :value="adminData.password"
              :is-invalid="!isValidLength(adminData.password)"
              @change-password="adminData.password = $event"
            />
            <span
              class="error"
              v-if="!!!adminData.password && hasError"
            >
              {{ $t('category.req', { type: $t('user.pass')}) }}
            </span>
            <span
              class="error"
              v-else-if="!isValidLength(adminData.password) && hasError"
            >
              {{ $t('user.inPass') }}
            </span>
          </div>

          <div class="oflow-hidden">
            <ajax-button
              v-if="$can('profile', 'edit')"
              class="primary-btn"
              :fetching-data="formSubmitting"
            />
          </div>
        </form>

        <form
          v-if="tabId[1] === activeTab"
          :class="{'has-error': hasError}"
          @submit.prevent="updatePassword"
        >
          <error-formatter/>

          <div class="input-wrapper">
            <label>
              {{ $t('user.pass') }}
            </label>
            <password-field
              :value="password.password"
              :is-invalid="!isValidLength(password.password)"
              @change-password="password.password = $event"
            />
            <span
              class="error"
              v-if="!!!password.password && hasError"
            >
              {{ $t('category.req', { type: $t('user.pass')}) }}
            </span>
            <span
              class="error"
              v-else-if="!isValidLength(password.password) && hasError"
            >
              {{ $t('user.inPass') }}
            </span>
          </div>

          <div class="input-wrapper">
            <label>
              {{ $t('profile.np') }}
            </label>
            <password-field
              :value="password.new_password"
              :is-invalid="!isValidLength(password.new_password)"
              @change-password="password.new_password = $event"
            />
            <span
              class="error"
              v-if="!!!password.new_password && hasError"
            >
              {{ $t('category.req', { type: $t('profile.np')}) }}
            </span>
            <span
              class="error"
              v-else-if="!isValidLength(password.new_password) && hasError"
            >
              {{ $t('user.inPass') }}
            </span>
          </div>

          <div class="input-wrapper">
            <label>
              {{ $t('user.cPass') }}
            </label>
            <password-field
              :value="password.confirm_password"
              :is-invalid="!isValidLength(password.confirm_password)"
              @change-password="password.confirm_password = $event"
            />
            <span
              class="error"
              v-if="!!!password.confirm_password && hasError"
            >
              {{ $t('category.req', { type: $t('user.cPass')}) }}
            </span>
            <span
              class="error"
              v-else-if="!isValidLength(password.confirm_password) && hasError"
            >
              {{ $t('user.inPass') }}
            </span>
            <span
              class="error"
              v-else-if="(password.confirm_password !== password.new_password) && hasError"
            >
              {{ $t('user.nMatch') }}
            </span>
          </div>

          <div class="oflow-hidden">
            <ajax-button
              v-if="$can('profile', 'edit')"
              class="primary-btn"
              :fetching-data="formSubmitting"
            />
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
  import {useAdminStore} from '~/store/admin';
  import {useUiStore} from '~/store/ui';
  import {storeToRefs} from "pinia";
  import {useValidationHelper} from "~/composables/useValidationHelper";
  import {onMounted} from "vue";
  import {useAuthStore} from "~/store/auth";

  definePageMeta({
    middleware: ['common-middleware', 'auth'],
    layout: 'default',
  });

  const {setErrors} = useUiStore();

  const adminStore = useAdminStore();
  const {profile} = storeToRefs(adminStore);
  const {updateProfile, setPassword} = adminStore;

  const authStore = useAuthStore();
  const {logUserOut} = authStore;

  const {t} = useI18n();

  const formSubmitting = ref(false);
  const hasError = ref(false);
  const adminData = ref({
    id: null,
    name: null,
    username: null,
    email: null,
    password: ''
  });
  const password = ref({
    password: null,
    confirm_password: null,
    new_password: null,
  });
  const tabId = ref(['profile-update', 'password-update']);
  const activeTab = ref('profile-update');
  const tabs = ref([
    {
      title: t('profile.up'),
      tabId: 'profile-update',
    },
    {
      title: t('profile.cp'),
      tabId: 'password-update',
    },
  ]);

  const {isValidEmail, isValidLength} = useValidationHelper();
  const isInvalidEmail = computed(() => {
    return (adminData.value.email && !isValidEmail(adminData.value.email));
  });


  const route = useRoute();
  const router = useRouter();

  const tabSelect = (val) => {
    if (val.tabId !== route.hash.replace('#', '')) {
      router.push({
        hash: `#${val.tabId}`
      })
    }
    hasError.value = false;
    activeTab.value = val.tabId;
  };

  const checkForm = async () => {
    if (adminData.value.username && adminData.value.email && adminData.value.password) {
      formSubmitting.value = true
      const data = await updateProfile(adminData.value)
      if (data) {
        adminData.value = Object.assign({}, profile.value)
      }
      formSubmitting.value = false
    } else {
      hasError.value = true
    }
  };

  const updatePassword = async () => {
    if ((password.value.password
      && password.value.new_password
      && password.value.confirm_password)
      && (password.value.new_password === password.value.confirm_password)) {
      formSubmitting.value = true;
      const data = await setPassword(password.value);
      if (data?.status === 200) {
        await logUserOut();
        window.location.reload();
      }
      formSubmitting.value = false
    } else {
      hasError.value = true
    }
  };

  onMounted(() => {
    activeTab.value = route.hash ? route.hash.replace('#', '') : tabs.value[0].tabId;
    adminData.value = Object.assign({}, profile.value);
  });
</script>
