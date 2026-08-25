<template>
    <div>
        <!-- Enable Telr -->
        <div class="input-wrapper dply-felx start">
            <label for="telr" class="mb-0">
                <input
                    type="checkbox"
                    id="telr"
                    :true-value="1" :false-value="0"
                    v-model="paymentData.telr"
                    @change="cbChanged"
                />
                {{ $t('ship.telr') }}
            </label>
            <button
                type="button"
                class="toggle-arrow"
                @click="showForm = !showForm"
            >
                <i class="icon black arrow-down"/>
            </button>
        </div>

        <!-- Telr Config -->
        <div v-if="showForm" class="payment-form-wrap">
            <div class="input-wrapper">
                <label>{{ $t('ship.tsi') }}</label>
                <input
                    placeholder="e.g. 123456789"
                    type="text"
                    v-model="paymentData.telr_store_id"
                    @input="emitData"
                >
            </div>

            <div class="input-wrapper">
                <label>{{ $t('ship.tak') }}</label>
                <input
                    placeholder="e.g. 123456789"
                    type="text"
                    v-model="paymentData.telr_auth_key"
                    @input="emitData"
                >
            </div>

            <!-- Telr Mode Dropdown -->
            <div class="input-wrapper">
                <div class="dply-felx j-left mb-20 mb-sm-15">
                      <span class="mr-15">
                        {{ $t('ship.tmode') }}
                      </span>

                    <dropdown
                        :selectedKey="paymentData.telr_mode"
                        :options="paymentChannelModes"
                        @clicked="telrModeSelect"
                    />
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import util from "~/mixin/util"
export default {
    name: 'Telr',
    mixins: [util],
    data() {
        return {
            showForm: false,
        }
    },
    props: {
        paymentData: {
            type: Object,
            required: true
        }
    },
    methods: {
        cbChanged(evt){
            this.showForm = evt.target.checked
            this.emitData()
        },
        telrModeSelect(item){
            this.paymentData.telr_mode = item.key
            this.emitData()
        },
        emitData(){
            this.$emit('change', this.paymentData)
        }
    }
}
</script>