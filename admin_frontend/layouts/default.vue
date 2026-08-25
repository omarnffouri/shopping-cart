<template>
  <div
    class="main"
    :class="`${$route.name} ${sidebarClass}`"
  >
    <CommonHeader />
    <CommonSidebar />
    <div
      class="content"
    >
      <p
        v-if="!activated"
        class="dashboard-notice info-msg mb-20 mb-sm-15"
      >
        {{ $t('dataPage.na') }}
        <nuxt-link class="link" to="/setting/miscellaneous">
          <b>{{ $t('dataPage.here') }}</b>
        </nuxt-link>
      </p>

      <p
        v-if="hasSetNotice && isSuperAdmin"
        class="dashboard-notice info-msg mb-20 mb-sm-15"
      >
        <b>{{ $t('setting.imp') }}</b>: {{ $t('setting.cClearMsg') }}
        <nuxt-link to="/setting/currency"><b>{{ $t('list.set') }}</b></nuxt-link> >
        <nuxt-link to="/setting/clear-cache"><b>{{ $t('setting.cClear') }}</b></nuxt-link>.

        <button
          class="close-btn"
          @click="closeNote"
        >
            <i class="icon close-icon"/>
          </button>
      </p>


      <slot
        v-if="profile"
        :key="fullUrl"
      />


<!--      <nuxt-->
<!--        v-if="$store.state.admin.permissions"-->
<!--        :key="fullUrl"-->
<!--        :nuxt-child-key="$route.path"-->
<!--        />-->
    </div>

    <transition name="fade" mode="out-in">
      <ToastMessage
        v-if="toastMessageStatus"
        :is-error="toastError"
        @hide="hideToast"
        :message="toastMessage"
      />
    </transition>
  </div>
</template>

<script setup>

  import { useCommonStore } from '~/store/common';
  import { useLanguageStore } from '~/store/language';
  import { useUiStore } from '~/store/ui';
  import { useAdminStore } from '~/store/admin';
  import { computed } from 'vue';
  import { useRoute } from 'vue-router';

  const uiStore = useUiStore();

  const { sidebarOpen, toastMessage,
    toastError, toastMessageStatus, dashboardNotice} = storeToRefs(uiStore)

  const {hideToast, setErrors, settingDashboardNotice} = uiStore

  const languageStore = useLanguageStore();
  const {currentLanguage} = storeToRefs(languageStore);

  const {getRequest} = useCommonStore();

  const adminStore = useAdminStore();
  const {setProfile } = adminStore
  const {profile, isSuperAdmin, activated} = storeToRefs(adminStore)

  const route = useRoute();

  const fullUrl = computed(() => {
    return route.fullPath.split('#')[0];
  });

  watch(() => route.fullPath, () => {
    setErrors()
  });

  const sidebarClass = computed(() => {
    return sidebarOpen.value ? 'sidebar-opened' : 'sidebar-closed';
  });

  const hasSetNotice = computed(() => {
    return dashboardNotice.value && dashboardNotice.value === 'false' || false;
  });

  const closeNote = () => {
    settingDashboardNotice(true)
  };

</script>
