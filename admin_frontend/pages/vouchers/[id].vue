<template>
  <PartialsDataPage
    ref="dataPage"
    set-api="setVoucher"
    get-api="getVoucher"
    route-name="vouchers"
    :name="$t('fSale.voucher')"
    :validation-keys="['title']"
    :result="result"
    gate="voucher"
    @result="setResult"
  >

    <template v-slot:form="{hasError}">

      <div class="input-wrapper">

        <label>{{ $t('index.title') }}</label>
        <input
          type="text"
          :placeholder="$t('index.title')"
          name="title"
          v-model="result.title"
          ref="title"
          :class="{invalid: !!!result.title && hasError}"
        >
        <span
          class="error"
          v-if="!!!result.title && hasError"
        >
          {{ $t('category.req', { type: $t('index.title')}) }}
        </span>
      </div>

      <div class="dply-felx align-start j-left inputs d-block-sm">
        <div class="input-wrapper">
          <div class="flex-v-centered">
            <span class="mr-15">{{ $t('brand.price') }}</span>
            <div>
              <input
                type="number"
                step="any"
                :placeholder="$t('brand.price')"
                v-model="result.price"
                :class="{invalid: !!!result.price && hasError}"
              >
              <span
                class="error"
                v-if="!!!result.price && hasError"
              >
                {{ $t('category.req', { type: $t('brand.price')}) }}
              </span>
            </div>

          </div>
        </div>

        <div class="input-wrapper ">
          <div class="sided f-none-sm f-right">
            <span class="mr-15 text-nowrap">{{ $t('prod.priType') }}</span>

            <dropdown
              :selectedKey="`${result.type}`"
              :options="priceTypeObj"
              @clicked="dropdownPriceType"
            />
          </div>
        </div>
      </div>

      <div class="dply-felx align-start j-left inputs  d-block-sm">
        <div class="input-wrapper ">
          <div class="flex-v-centered">
            <span class="mr-15">{{ $t('prod.capped') }}({{ currencyIcon }})</span>
            <div>
              <input
                type="number"
                step="any"
                :placeholder="$t('brand.price')"
                v-model="result.capped_price"
              >
            </div>

          </div>
        </div>

        <div class="input-wrapper ">
          <div class="flex-v-centered">
            <span class="mr-15">{{ $t('prod.spent') }}({{ currencyIcon }})</span>
            <div>
              <input
                type="number"
                step="any"
                :placeholder="$t('brand.price')"
                v-model="result.min_spend"
                :class="{invalid: !!!result.min_spend && hasError}"
              >
              <span
                class="error"
                v-if="!!!result.min_spend && hasError"
              >{{ $t('category.req', { type: $t('prod.spent')}) }}</span>
            </div>

          </div>
        </div>
      </div>

      <div class="dply-felx align-start j-left inputs  d-block-sm">
        <div class="input-wrapper ">
          <div class="flex-v-centered">
            <span class="mr-15">{{ $t('prod.usage') }}</span>
            <div>
              <input
                type="number"
                step="any"
                :placeholder="$t('brand.price')"
                v-model="result.usage_limit"
                :class="{invalid: !!!result.usage_limit && hasError}"
              >
              <span
                class="error"
                v-if="!!!result.usage_limit && hasError"
              >{{ $t('category.req', { type: $t('prod.usage')}) }}</span>
            </div>

          </div>
        </div>

        <div class="input-wrapper ">
          <div class="flex-v-centered">
            <span class="mr-15">{{ $t('prod.limit') }}</span>
            <div>
              <input
                type="number"
                step="any"
                :placeholder="$t('brand.price')"
                v-model="result.limit_per_customer"
                :class="{invalid: !!!result.limit_per_customer && hasError}"
              >
              <span
                class="error"
                v-if="!!!result.limit_per_customer && hasError"
              >{{ $t('category.req', { type: $t('prod.limit')}) }}</span>
            </div>

          </div>
        </div>
      </div>

      <div class="input-wrapper">
        <label>{{ $t('prod.code') }}</label>
        <input
          type="text"
          :placeholder="$t('prod.code')"
          v-model="result.code"
          :class="{invalid: !!!result.code && hasError}"
        >
        <span class="error" v-if="!!!result.code && hasError">{{ $t('category.req', { type: $t('prod.code')}) }}</span>
      </div>

      <div class="dply-felx align-start j-left inputs d-block-sm">
        <div class="input-wrapper mlr-7-5">
          <div :class="{'red-border': !!!result.start_time && hasError}"
               class="flex-v-centered no-border">

            <span class="mr-15">{{ $t('prod.sTime') }}</span>

            <FlatPickr class="form-bottom" width="300px" :config="dpConfig" v-model="result.start_time"/>

          </div>

          <span
            class="error"
            v-if="!!!result.start_time && hasError"
          >
              {{ $t('category.req', { type: $t('prod.sTime')}) }}
            </span>
        </div>

        <div class="input-wrapper mlr-7-5">
          <div
            :class="{'red-border': (!!!result.end_time && hasError) || (!dateValidation && hasError)}"
            class="flex-v-centered no-border">
            <span class="mr-15">{{ $t('prod.eTime') }}</span>


            <FlatPickr class="form-bottom" width="300px" :config="dpConfig" v-model="result.end_time"/>


          </div>

          <span
            class="error"
            v-if="!!!result.end_time && hasError"
          >
                        {{ $t('category.req', { type: $t('prod.eTime')}) }}
                    </span>

          <span class="error"
                v-else-if="!dateValidation && hasError"
          >
                      {{ $t('prod.greater') }}
                    </span>

        </div>
        <div class="info-row mlr-7-5">
          <div class="row-left">
            <span>{{ $t('prod.showValidThru') }}</span>
          </div>
          <label class="toggle" :aria-label="$t('prod.showValidThru')">
            <input type="checkbox" v-model="result.show_validity_date">
            <div class="toggle-track">
              <div class="toggle-thumb">
                <svg class="icon-off" viewBox="0 0 10 10" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                  <line x1="2" y1="2" x2="8" y2="8"/><line x1="8" y1="2" x2="2" y2="8"/>
                </svg>
                <svg class="icon-on" viewBox="0 0 10 10" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                  <polyline points="1.5,5 4,7.5 8.5,2.5"/>
                </svg>
              </div>
            </div>
          </label>
        </div>
      </div>



      <div class="input-wrapper">
        <div class="dply-felx j-left mb-20 mb-sm-15">
          <span class="mr-15">{{ $t('category.status') }}</span>

          <dropdown
            :selectedKey="`${result.status}`"
            :options="statusObj"
            @clicked="dropdownSelected"
          />
        </div>
      </div>

    </template>
  </PartialsDataPage>
</template>

<script setup>
import {useSettingStore} from '~/store/setting';
import {storeToRefs} from "pinia";
import {useConstants} from "../../composables/useConstants";

definePageMeta({
  middleware: ['common-middleware', 'auth'],
  layout: 'default',
})

const settingStore = useSettingStore();
const {setting} = storeToRefs(settingStore);

const result = ref({
  id: '',
  title: '',
  capped_price: '',
  limit_per_customer: '',
  usage_limit: '',
  min_spend: '',
  code: '',
  start_time: '',
  end_time: '',
  type: 1,
  show_validity_date: 1,
  status: 2
});

const {statusObj, dpConfig, priceTypeObj} = useConstants();

const dateValidation = computed(() => {
  return new Date(result.value.end_time) > new Date(result.value.start_time)
});

const currencyIcon = computed(() => {
  return setting.value?.currency_icon || '$'
});

const dropdownPriceType = (data) => {
  result.value.type = data.key
};

const dropdownSelected = (data) => {
  result.value.status = data.key
};


const setResult = (event) => {
  result.value = event;
};
</script>
<style scoped>
.toggle {
  position: relative;
  display: inline-block;
  width: 56px;
  height: 30px;
  flex-shrink: 0;
}
.toggle input { opacity: 0; width: 0; height: 0; position: absolute; }

.toggle-track {
  position: absolute;
  inset: 0;
  border-radius: 30px;
  background: #e2e5ef;
  cursor: pointer;
  overflow: hidden;
  transition: background 0.35s cubic-bezier(0.4,0,0.2,1), box-shadow 0.35s;
}

.toggle-thumb {
  position: absolute;
  top: 4px; left: 4px;
  width: 22px; height: 22px;
  border-radius: 50%;
  background: white;
  box-shadow: 0 2px 8px rgba(0,0,0,0.18);
  transition: transform 0.38s cubic-bezier(0.34,1.56,0.64,1), width 0.2s ease;
  z-index: 2;
  display: flex;
  align-items: center;
  justify-content: center;
}
.toggle-thumb svg { width: 10px; height: 10px; opacity: 0; transition: opacity 0.2s ease 0.1s; }
.toggle-thumb .icon-off { color: #b0b5cc; }
.toggle-thumb .icon-on  { color: #6366f1; position: absolute; }

.toggle input:checked + .toggle-track {
  background: linear-gradient(135deg, #6366f1, #8b5cf6);
  box-shadow: 0 0 0 3px rgba(99,102,241,0.15), inset 0 1px 3px rgba(0,0,0,0.1);
}
.toggle input:checked + .toggle-track::before {
  background-position: -200% center;
  transition: background-position 0.6s ease;
}
.toggle input:checked + .toggle-track .toggle-thumb {
  transform: translateX(26px);
  box-shadow: 0 2px 12px rgba(99,102,241,0.4);
}
.toggle input:checked + .toggle-track .toggle-thumb .icon-on { opacity: 1; }
.toggle input:active + .toggle-track .toggle-thumb { width: 26px; }
.toggle input:focus-visible + .toggle-track { outline: 2px solid #6366f1; outline-offset: 2px; }

.info-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 6px 16px;
  border-radius: 12px;
  background: #fafafa;
  border: 1.5px solid #f0f0f0;
  margin-bottom: 8px;
  transition: border-color 0.2s, background 0.2s;
}
.info-row:last-child { margin-bottom: 0; }
.info-row:hover { border-color: #e0e0f0; background: #f8f8ff; }
.row-left { display: flex; flex-direction: column; gap: 5px; }

.pill-option input[type="radio"] { position: absolute; opacity: 0; width: 0; height: 0; }
.pill-option label {
  display: flex;
  align-items: center;
  gap: 5px;
  padding: 6px 14px;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 600;
  color: #6b7280;
  cursor: pointer;
  transition: color 0.2s, background 0.2s, box-shadow 0.2s;
  user-select: none;
  white-space: nowrap;
}
.pill-option label svg { width: 12px; height: 12px; }
.pill-option.yes input:checked + label { background: white; color: #059669; box-shadow: 0 1px 6px rgba(0,0,0,0.1); }
.pill-option.yes input:checked + label svg { stroke: #059669; }
.pill-option.no  input:checked + label { background: white; color: #ef4444; box-shadow: 0 1px 6px rgba(0,0,0,0.1); }
.pill-option.no  input:checked + label svg { stroke: #ef4444; }
.pill-option.yes input:not(:checked) + label:hover { color: #059669; }
.pill-option.no  input:not(:checked) + label:hover { color: #ef4444; }
</style>