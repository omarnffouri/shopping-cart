<template>
  <div class="login-container">
    <img
        src="~/assets/images/login-bg.png" alt=""
    />
    <slot/>
    <transition name="fade" mode="out-in">
      <ToastMessage
          v-if="toastMessageStatus"
          :is-error="toastError"
          :message="toastMessage"
          @hide="hideToast"
      />
    </transition>
  </div>
</template>

<script setup>
  import {useUiStore} from '~/store/ui';

  const uiStore = useUiStore();
  const {toastMessage, toastError, toastMessageStatus} = storeToRefs(uiStore);
  const {hideToast} = uiStore;
</script>
