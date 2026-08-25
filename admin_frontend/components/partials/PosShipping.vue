<template>
  <div class="b-t mt-15 pt-15">

    <address-popup
      v-if="showAddressPopup"
      :selectedUser="selectedUser"
      :address="editableAddress"
      @close="closeAddressPopup"
    />

    <div class="dply-felx start">
      <label for="pos-shipping" class="mb-0">
        <input
          type="checkbox"
          id="pos-shipping"
          value="1"
          :true-value="1" :false-value="0"
          v-model="posShippingCb"
          @change="cbChanged"
        />
        {{ $t('fSale.dFee') }}
      </label>
      <button
        type="button"
        class="toggle-arrow"
        @click="showForm = !showForm"
      ><i class="icon black arrow-down"></i></button>
    </div>

    <div
      class="p-15"
      v-if="showForm"
    >
      <h4 v-if="error" class="error mt-15">{{error}}</h4>

      <label
        v-for="(value, key) in currentAddresses"
        :key="key"
        class="card mtb-15 address-card "
        :class="{active: selectedAddress === key}"
      >
        <span class="flex gap-10 mb-10 sided address-title">
            <span class="dply-felx gap-10">
               <input
                 type="radio"
                 name="user_address"
                 :value="key"
                 v-model="selectedAddress"
                 @change="addressChanged"
               />
              <span class="block bold">{{value.name}}</span>
            </span>

            <span class="dply-felx gap-10">
              <ajax-button
                class="edit-btn plr-10 xs"
                :type="'button'"
                :text="$t('category.edit')"
                color="primary"
                @clicked="editAddress(value)"

              />
              <ajax-button
                class="delete-btn plr-10 xs"
                :type="'button'"
                :fetching-data="ajaxDeleting === value.id"
                :loading-text="$t('ship.dtn')"
                :text="$t('category.delete')"
                color="primary"
                @clicked="deleteAddress(value)"
              />
            </span>
          </span>

        <span v-html="formatAddress(value)"/>
        <span class="block mt-5">tel: {{value.phone}}</span>
      </label>

      <button
        type="button"
        class="w-100 mt-15 outline-btn"
        data-ignore="user-address-pop-over"
        @click="addAddress"
      >
        {{ $t('ship.ana') }}
      </button>

    </div>
  </div>
</template>

<script>
  import AddressPopup from "~/components/partials/AddressPopup";
  import AjaxButton from "~/components/AjaxButton";
  import addressHelper from "~/mixin/addressHelper";
  import {storeToRefs} from "pinia";
  import {useCommonStore} from "../../store/common";
  import {useResourceStore} from "../../store/resource";
  import {useLanguageStore} from "../../store/language";

  export default {
    setup(){
      const commonStore = useCommonStore()
      const {getRequest, deleteData} = commonStore

      const resourceStore = useResourceStore()
      const {setCountryList, setPhoneList} = resourceStore
      const {countryList, phoneList} = storeToRefs(resourceStore)

      const languageStore = useLanguageStore()
      const {langCode} = storeToRefs(languageStore)

      const showAddressPopup = ref(false)
      const error = ref(false)

      const addAddress = () => {
        emptyError()
        showAddressPopup.value = true
      }

      const emptyError = () => {
        error.value = null
      }

      return {getRequest, deleteData, setCountryList, setPhoneList, countryList, phoneList,
        langCode, addAddress, emptyError, error, showAddressPopup}
    },
    name: "PosShipping",

    data() {
      return {
        posShippingCb: [],
        selectedAddress: 0,
        editableAddress: null,
        ajaxDeleting: false,
        showForm: false
      }
    },
    components: {
      AjaxButton,
      AddressPopup
    },
    props: {
      selectedUser: {
        type: Object,
        default: () => {
          return null
        }
      },
      addressList: {
        type: Array
      },
    },
    mixins: [addressHelper],
    computed: {
      currentAddresses(){
        return this.addressList
      },
    },
    methods: {
      addressChanged(){
        this.emitSelected()
      },
      async deleteAddress(value){
        if (confirm(this.$t('admin.dltMsg'))) {
          try {
            this.ajaxDeleting = true
            await this.deleteData({params: value.id, api: 'deleteUserAddress', id: value.id })
            this.ajaxDeleting = false
            this.$emit('address-removed', value)

          } catch (e) {
             showError({
            statusCode: 400,
            message: e.message
          })
          }
        }
      },
      closeAddressPopup(evt){
        this.editableAddress = null
        this.showAddressPopup = false

        if(evt){
          this.$emit('address-added', evt)
          this.emitSelected()
        }
      },
      emitSelected(){
        const selected = this.addressList[this.selectedAddress] ?? null
        if(selected) {
          const countryName = this.countryList[selected.country]?.name
          const stateName = selected.state ? this.countryList[selected.country].states[selected.state]?.name : ''

          this.$emit('selected-address', {...selected,
            ...{countryTitle: countryName, stateTitle: stateName}})
        }
      },
      userChanged(){
        this.emptyError()
        this.posShippingCb = []
        this.showForm = false
      },
      emptyError(){
        this.error = null
      },
      editAddress(value){
        this.editableAddress = value
        this.addAddress()
      },

      cbChanged(evt){
        if(!evt.target.checked){
          this.$emit('has-address', false)
          this.$emit('selected-address', null)
        } else if(this.selectedUser?.id === -1){
          this.$emit('has-address', false)
          this.$emit('selected-address', null)
        } else if(this.addressList.length){
          this.$emit('has-address', true)
          this.emitSelected()
        } else {
          this.$emit('has-address', false)
          this.$emit('selected-address', null)
        }
        this.showForm = evt.target.checked
      },

    },
    async mounted() {
      if (!this.countryList || !this.phoneList) {
        this.loading = true

        const data = await this.getRequest({
          params: null,
          lang: this.langCode,
          api: 'countriesPhones'
        })

        this.setCountryList(data?.countries)
        this.setPhoneList(data?.phones)
        this.loading = false
      }

      this.addressChanged()
    }
  }
</script>

<style scoped>

</style>
