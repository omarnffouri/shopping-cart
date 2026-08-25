<template>
  <PartialsDataPage
    v-if="allRoles"
    ref="dataPageRef"
    set-api="setAdmin"
    get-api="getAdmin"
    route-name="admins-vendors"
    :name="$t('user.admVend')"
    :emit-before-submit="true"
    @before-submit="submitForm"
    :validation-keys="validationKeys"
    :result="result"
    gate="admin"
    @result="setResult"
  >
    <template v-slot:form="{hasError}">
      <div class="input-wrapper">
        <label>
          {{ $t('user.name') }}
        </label>
        <input
          type="text"
          :placeholder="$t('user.name')"
          v-model="result.name"
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
          v-model.trim="result.username"
          :class="{invalid: !result.username && hasError}"
        >
        <span
          class="error"
          v-if="!!!result.username && hasError"
        >
          {{ $t('category.req', { type: $t('user.uName')}) }}
        </span>
      </div>


      <div class="input-wrapper">

        <label>
          {{ $t('fSale.email') }}
        </label>

        <div class="icon-input">
          <i
            class="icon email-icon"
          />
          <input
            type="text"
            :placeholder="$t('fSale.email')"
            v-model.trim="result.email"
            :class="{invalid: !!!result.email || isInvalidEmail}"
          >
        </div>

        <span
          class="error"
          v-if="!!!result.email && hasError"
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


      <div
        v-if="!id"
        class="input-wrapper">
        <label>
          {{ $t('user.pass') }}
        </label>
        <password-field
          :value="result.password"
          :is-invalid="!isValidLength(result.password)"
          @change-password="result.password = $event"
        />
        <span
          class="error"
          v-if="!!!result.password && hasError"
        >
          {{ $t('category.req', { type: $t('user.pass')}) }}
        </span>
        <span
          class="error"
          v-else-if="!isValidLength(result.password) && hasError"
        >
          {{ $t('user.inPass') }}
        </span>
      </div>


      <div
        v-if="!id"
        class="input-wrapper">
        <label>
          {{ $t('user.cPass') }}
        </label>
        <password-field
          :value="result.confirm_password"
          :is-invalid="!isValidLength(result.confirm_password)"
          @change-password="result.confirm_password = $event"
        />
        <span
          class="error"
          v-if="!!!result.confirm_password && hasError"
        >
          {{ $t('category.req', { type: $t('user.cPass')}) }}
        </span>
        <span
          class="error"
          v-else-if="!isValidLength(result.confirm_password) && hasError"
        >
          {{ $t('user.inPass') }}
        </span>
        <span
          class="error"
          v-else-if="(result.confirm_password !== result.new_password) && hasError"
        >
          {{ $t('user.nMatch') }}
        </span>
      </div>

      <div class="input-wrapper">
        <div class="dply-felx j-left mb-20 mb-sm-15">
          <span class="mr-15">
            {{ $t('user.role') }}
          </span>
          <dropdown
            :selectedKey="getDataFromObject(result, 'roles.0.name', getDataFromObject(result, 'roles.0', null))"
            :options="allRoles"
            @clicked="dropdownSelected"
          />
        </div>
      </div>

      <label class="input-wrapper block">
        <span class="mr-15">{{ $t('title.ac') }}</span>
        <input type="checkbox" v-model="result.active" :true-value="1" :false-value="0">
      </label>

      <label class="input-wrapper block">
        <span class="mr-15">{{ $t('user.verified') }}</span>
        <input type="checkbox" v-model="result.verified" :true-value="1" :false-value="0">
      </label>

      <div
        v-if="getDataFromObject(result, 'roles.0.name', getDataFromObject(result, 'roles.0', null)) === 'vendor'"
        class="input-wrapper"
      >
        <label>
          {{ $t('user.com') }}
        </label>
        <input
          type="number"
          step="any"
          :placeholder="$t('user.eg')"
          name="commission"
          v-model.trim="result.commission"
        >
      </div>

    </template>
  </PartialsDataPage>
</template>

<script setup>

  import {useCommonStore} from "~/store/common";
  import {storeToRefs} from "pinia";
  import {onMounted,} from "vue";
  import {useValidationHelper} from "~/composables/useValidationHelper";
  import {useUtils} from "~/composables/useUtils";

  definePageMeta({
    middleware: ['common-middleware', 'auth'],
    layout: 'default',
  });

  const commonStore = useCommonStore();
  const {getAllList} = commonStore;
  const {allRoles} = storeToRefs(commonStore);


  const validationKeys = ref(['username', 'email']);
  const roles = ref(null);
  const result = ref({
    id: '',
    name: '',
    active: 0,
    verified: 0,
    username: '',
    commission: '',
    password: '',
    confirm_password: '',
    roles: [],
    email: ''
  });

  const id = computed(() => {
    return result.value?.id
  });

  const {getDataFromObject } = useUtils();
  const {isValidEmail, isValidLength } = useValidationHelper();

  const isInvalidEmail = computed(() => {
    return (result.value.email && !isValidEmail(result.value.email))
  });


  const dataPageRef = ref(null);

  const submitForm = () => {
    if (!id.value) {
      validationKeys.value.push('password')
      validationKeys.value.push('confirm_password')
    }

    dataPageRef.value.checkForm()
  };

  const setResult = (event) => {
    if (event.roles?.length) {
      event.roles = event?.roles?.map(i => {
        return i.name
      })
    } else {
      event.roles = [Object.values(allRoles.value)[0]?.title]
    }
    result.value = event
  };

  const activatedSelected = (data) => {
    result.value.active = data.key
  };

  const dropdownSelected = (data) => {
    result.value.roles = [data.key]
  };

  onMounted(async () => {
    if (!allRoles.value) {
      await getAllList({api: 'allRoles', action: 'setAllRoles'})
    }
  })

</script>

