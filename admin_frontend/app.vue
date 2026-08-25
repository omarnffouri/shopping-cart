<script setup>

  import {ref, onMounted} from "vue";
  import {useIndexStore} from "./store";
  import {showError} from "nuxt/app";

  const {nuxtClientInit} = useIndexStore();

  const isLoading = ref(false)

  onMounted(async () => {
    isLoading.value = true

    try {
      await nuxtClientInit();
    } catch (e) {
      showError({
        statusCode: 400,
        message: e.message
      })
    }
    isLoading.value = false
  });

  const {setLocale} = useI18n()
  const cookies = document.cookie
  const cookieLang = cookies && cookies.includes('currentLanguage=')
    ? cookies.split('currentLanguage=')[1].split(';')[0] : ''

  if (cookieLang) {
    setLocale(cookieLang)
  }

</script>

<template>

  <div v-if="isLoading" class="spinner-wrapper">
    <spinner :radius="50" color="primary"/>
  </div>

  <NuxtLayout v-else>
    <NuxtPage/>
  </NuxtLayout>
</template>
