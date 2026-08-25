<template>
  <PartialsDataPage
    v-if="allSubscriptionEmailFormats && $can('subscriber', 'view')"
    ref="dataPageRef"
    route-name="subscribers"
    :name="$t('dataPage.eFor')"
    gate="subscriber"
    :validation-keys="['subject']"
    :result="result"
    @result="result = $event"
  >
    <template v-slot:form="{hasError}">

      <div
        v-for="(item, index) in allSubscriptionEmailFormats"
        :key="index"
      >
        <label class="cp mtb-5">
          <input
            type="radio"
            :value="item.id"
            v-model="selectedFormat"
          >
          {{ item.title }}
        </label>
      </div>

      <div class="dply-felx j-right">
        <ajax-button
          class="primary-btn"
          type="button"
          text="Send Email"
          :fetching-data="sendingEmail"
          @clicked="sendSubscriptionEmail"
        />
      </div>

    </template>
  </PartialsDataPage>
</template>

<script setup>
  import {useCommonStore} from "~/store/common";
  import {onMounted} from "vue";

  definePageMeta({
    middleware: ['common-middleware', 'auth'],
    layout: 'default',
  });

  const commonStore = useCommonStore();
  const {getRequest, setRequest, setAllSubscriptionEmailFormats} = commonStore;
  const {allSubscriptionEmailFormats} = storeToRefs(commonStore);

  const sendingEmail = ref(false);
  const selectedFormat = ref('');
  const result = ref({
    format: ''
  });

  const sendSubscriptionEmail = async (id) => {
    sendingEmail.value = true;
    await setRequest({
      params: {id: selectedFormat.value},
      api: 'sendSubscriptionEmail'
    });
    sendingEmail.value = false;
  };

  onMounted(async () => {
    if (!allSubscriptionEmailFormats.value) {
      const data = await getRequest({params: {}, api: 'getAllSubscriptionEmailFormats'});
      setAllSubscriptionEmailFormats(data);
      if (allSubscriptionEmailFormats.value.length) {
        selectedFormat.value = allSubscriptionEmailFormats.value[0].id;
      }
    } else {
      if (allSubscriptionEmailFormats.value.length) {
        selectedFormat.value = allSubscriptionEmailFormats.value[0].id;
      }
    }
  });
</script>
