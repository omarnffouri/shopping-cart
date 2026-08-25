<template>
  <div
    v-if="$can('message', 'view')"
    class="detail-width"
  >
    <div
      v-if="loading"
      class="spinner-wrapper"
    >
      <spinner
        :radius="60"
        color="primary"
      />
    </div>

    <div
      v-if="result"
      class="order-wrapper tab-sidebar"
    >
      <div class="title">
        <div class="dply-felx d-block-sm">
          <h4 class="mb-sm-10">
            {{ $t('profile.um') }}
          </h4>
          <div class="dply-felx j-left f-wrap mlr--5 mtb-sm--5">
            <dropdown
              class="mlr-5 mtb-sm-5"
              :selected-key="result.replied"
              :options="messageReply"
              @clicked="dropdownSelected"
            />
            <ajax-button
              name="save-edit"
              class="primary-btn mlr-5 mtb-sm-5"
              :text="$t('profile.us')"
              :loading-text="$t('profile.updatn')"
              :fetching-data="formSubmitting"
              type="button"
              @clicked="updateSReplied"
            />
          </div>
        </div>
      </div>

      <form class="form-wrapper">
        <div class="dply-felx d-block-sm">
          <div class="input-wrapper">
            <label>{{ $t('user.name') }}</label>
            <p>{{ result.name }}</p>
          </div>

          <div class="input-wrapper">
            <label>{{ $t('fSale.email') }}</label>
            <p>{{ result.email }}</p>
          </div>
        </div>

        <div class="input-wrapper">
          <label>{{ $t('util.sub') }}</label>
          <p>{{ result.subject }}</p>
        </div>

        <div class="input-wrapper">
          <label> {{ $t('user.msg') }}</label>
          <p>{{ result.message }}</p>
        </div>

      </form>
    </div>
  </div>
</template>

<script setup>
  import {useCommonStore} from '~/store/common';
  import {onMounted} from "vue";
  import {ability} from '~/composables/ability';
  import {useConstants} from "~/composables/useConstants";

  definePageMeta({
    middleware: ['common-middleware', 'auth'],
    layout: 'default',
  });
  const {getById, setById} = useCommonStore();

  const formSubmitting = ref(false);
  const statusChanged = ref(false);
  const loading = ref(false);
  const result = ref({
    id: '',
    name: '',
    email: '',
    subject: '',
    replied: ''
  });

  const {messageReply} = useConstants();
  const route = useRoute();

  const id = computed(() => {
    return route?.params?.id;
  });

  const updateSReplied = async () => {
    if (!statusChanged.value) {
      return false
    }
    statusChanged.value = false
    formSubmitting.value = true
    await setById({id: id.value, params: result.value, api: 'setUserMessage'})
    formSubmitting.value = false
  };

  const dropdownSelected = (data) => {
    statusChanged.value = true
    result.value.replied = data.key
  };

  const fetchingData = async () => {
    loading.value = true
    result.value = Object.assign({}, await getById({id: id.value, params: {}, api: 'getUserMessage'}))
    loading.value = false
  };

  onMounted(async () => {
    if (ability.can('message', 'view')) {
      await fetchingData();
    }
  });
</script>
