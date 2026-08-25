<template>
  <PartialsSettingLayout
    active-route="order"
    class="mb-5"
  >
    <template v-slot:rightArea>
      <form
        :class="{'has-error': hasError}"
        @submit.prevent="saveOrderSetting"
      >
        <div
          v-if="loading"
          class="spinner-wrapper"
        >
          <spinner
            :radius="60"
            color="primary"
            class="mr-15"
          />
        </div>

        <div
          v-else
          class="input-wrapper single-line"
        >
          <label>
            {{ $t('setting.autoCancelMinutes') || 'Abandoned Order countdown (minutes)' }}
          </label>

          <input
            v-model.number="autoCancelMinutes"
            type="number"
            min="1"
            max="1440"
            step="1"
            class="form-control"
            :disabled="saving"
            @input="hasError = false"
          />
        </div>

        <div
          v-if="!loading"
          class="input-wrapper"
          style="margin-top: -8px;"
        >
          <small style="opacity: 0.8;">
            {{ $t('setting.autoCancelMinutesHelp') || 'Applies to unpaid pending online-payment orders.' }}
          </small>
        </div>

        <ajax-button
          v-if="$can('setting', 'edit')"
          class="primary-btn"
          :text="$t('setting.sv') || 'Save'"
          :fetching-data="saving"
        />
      </form>
    </template>
  </PartialsSettingLayout>
</template>

<script setup>
import {useCommonStore} from '~/store/common'
import {useUiStore} from "~/store/ui.js";

definePageMeta({
  middleware: ['common-middleware', 'auth'],
  layout: 'default',
})

const {setToastMessage, setToastError} = useUiStore();
const {getRequest, setRequest} = useCommonStore()

const loading = ref(false)
const saving = ref(false)
const hasError = ref(false)

const autoCancelMinutes = ref(10)

const clampMinutes = (value) => {
  const n = Number(value)
  if (!Number.isFinite(n)) return null
  const rounded = Math.trunc(n)
  if (rounded < 1 || rounded > 1440) return null
  return rounded
}

const fetchOrderSetting = async () => {
  loading.value = true
  hasError.value = false

  const res = await getRequest({
    api: 'adminOrderSettingFind',
    params: '',
  })

  // getRequest returns data with {status, data}
  if (res?.status === 200) {
    const minutes = clampMinutes(res?.data?.auto_cancel_minutes)
    autoCancelMinutes.value = minutes ?? 10
  } else {
    autoCancelMinutes.value = 10
  }

  loading.value = false
}

const saveOrderSetting = async () => {
  const minutes = clampMinutes(autoCancelMinutes.value)

  if (minutes === null) {
    hasError.value = true
    setToastError('Auto-cancel minutes must be between 1 and 1440.')
    return
  }

  saving.value = true
  hasError.value = false

  const res = await setRequest({
    api: 'adminOrderSettingAction',
    params: { auto_cancel_minutes: minutes },
  })

  if (res) {
    autoCancelMinutes.value = res.auto_cancel_minutes
    setToastMessage('Order settings updated successfully.')
  } else {
    hasError.value = true
    setToastError(res?.message || 'Failed to save order settings.')
  }

  saving.value = false
}

onMounted(async () => {
  await fetchOrderSetting()
})
</script>