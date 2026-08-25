<template>
  <pop-over
      :title="$t('title.OderCancel')"
      @close="$emit('close')"
      elem-id="cancel-pop-over"
      :layer="true"
      class="cancel-popup popup-top-auto"
  >
    <template v-slot:content>
      <div class="pos-rel" @click.stop>
        <div class="mb-15">
          <label class="block mb-5 bold">{{ $t('fSale.cReason') }}</label>
          <textarea
              v-model="cancellationData.message"
              class="w-100 p-10 border-radius-5"
              rows="4"
              :placeholder="$t('fSale.cReason')"
              :class="{error_textarea: !cancellationData.message && hasFormError}"
          ></textarea>
          <span
              class="error"
              v-if="!cancellationData.message && hasFormError"
          >
              {{ $t('category.req', {type: $t('setting.cr') }) }}
            </span>
        </div>


      </div>
    </template>

    <template v-slot:pop-footer>
      <div class="footer-buttons" @click.stop>
        <button
            class="outline-btn"
            @click.prevent="handleClose"
        >
          {{ $t('setting.dismiss') }}
        </button>
        <button
            class="primary-btn"
            @click.prevent="submitCancel"
        >
          {{ $t('title.OderCancel') }}
        </button>
      </div>
    </template>
  </pop-over>
</template>

<script setup>
import { ref } from 'vue';
import { useCommonStore } from '~/store/common';
import {useUiStore} from "~/store/ui.js";
import { useI18n } from 'vue-i18n'

const uiStore = useUiStore()
const {setToastMessage, setToastError} = uiStore
const { t } = useI18n()
const props = defineProps({
  orderId: { type: Number, required: true }
});
const hasFormError = ref(false);
const emit = defineEmits(['close', 'click']);

const cancellationData = ref({ message: '', refundable: false });



const handleClose = () => emit('close');

const submitCancel = () => {
  if (!cancellationData.value.message.trim()) {
    hasFormError.value = true
    commonStore.setToastError(
        t('fSale.cReason', {}, 'Cancellation reason') + ' is required'
    )
    // alert('Please provide a cancellation reason');
    return;
  }
  emit('click', cancellationData.value);
  emit('close');
};
</script>

<style scoped>
.cancel-popup {
  max-width: 500px;
}

/* Textarea styling */
textarea {
  border: 1px solid #ddd;
  resize: none;
}
.error_textarea{
  border: 1px solid red;
}

/* Footer buttons aligned right with small gap */
.footer-buttons {
  display: flex;
  justify-content: flex-end; /* Right side */
  gap: 12px; /* Space between buttons */
  padding-top: 10px;
}
</style>
