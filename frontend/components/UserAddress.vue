<template>
  <div>
    <transition name="fade" mode="out-in">
      <div
        class="spinner-wrapper flex"
        v-if="fetchingAddressData"
      >
        <spinner
          :radius="100"
        />
      </div>
    </transition>

    <div
      v-if="!hasAddress"
      class="info-msg mb-20"
    >
      {{ $t('userAddress.noAddress') }}
    </div>

    <div class="flex gap-20 align-initial wrap start" v-if="hasRadio">
      <label
        v-for="(value, key) in currentAddresses"
        :key="key"
        class="card p-15 address-card"
        :class="{active: selectedAddress === key}"
        @click.prevent="toggleAddress(key, $event)"
      >
        <input
          type="radio"
          name="user_address"
          :value="key"
          :checked="selectedAddress === key"
          @click.prevent
        />
        <span class="flex gap-10 mb-10 align-initial sided address-title">
          <span class="block bold">{{value.name}}</span>
          <span class="flex gap-10">
            <ajax-button
              class="outline-btn plr-10"
              :type="'button'"
              :text="$t('userAddress.edit')"
              color="primary"
              @clicked="$emit('editing', value)"
            />
            <ajax-button
              class="outline-btn plr-10"
              :type="'button'"
              :fetching-data="ajaxDeleting === value.id"
              :loading-text="$t('userAddress.deleting')"
              :text="$t('userAddress.delete')"
              color="primary"
              @clicked="deleteAddress(value)"
            />
          </span>
        </span>

        <span v-html="formatAddress(value)"/>
        <span class="block mt-5">tel: {{value.phone}}</span>
      </label>

      <button
        class="address-btn card" type="button"
        @click.prevent="$emit('add-address')"
      >
        <span class="icon-wrap mb-10">
          <i
            class="icon plus-icon"
          />
        </span>

        {{ $t('addresses.addAddress') }}
      </button>

    </div>

    <div v-else class="flex wrap start align-initial gap-10">
      <div
        class="card p-20  mx-w-400x address-card"
        v-for="(value, index) in currentAddresses"
        :key="index"
      >
        <span class="flex gap-10 sided mb-10 address-title">

          <span class="bold block">{{value.name}}</span>

          <span class="flex gap-10">
            <ajax-button
              class="outline-btn plr-10"
              :type="'button'"
              :text="$t('userAddress.edit')"
              color="primary"
              @clicked="$emit('editing', value)"
            />
            <ajax-button
              class="outline-btn plr-10"
              :type="'button'"
              :fetching-data="ajaxDeleting === value.id"
              :loading-text="$t('userAddress.deleting')"
              :text="$t('userAddress.delete')"
              color="primary"
              @clicked="deleteAddress(value)"
            />
          </span>
        </span>

        <span v-html="formatAddress(value)"/>
        <span class="block mt-5">tel: {{value.phone}}</span>

      </div>
    </div>
    <pagination
      ref="addressPaginationRef"
      :total-page="totalPage"
    />
  </div>
</template>

<script setup>

  import {useResourceStore} from "~/store/resource";
  import {useUserStore} from "~/store/user";
  import {useLanguageStore} from "~/store/language";
  import {useCommonStore} from "~/store/common";
  import {storeToRefs} from "pinia";
  import {onMounted, toRefs} from "vue";
  import {useAddressHelper} from "../composables/useAddressHelper";

  const props = defineProps({
    hasRadio: {
      type: Boolean,
      default: false
    }
  });

  const {hasRadio} = toRefs(props)

  const emit = defineEmits(['selected-address']);

  const languageStore = useLanguageStore();
  const {langCode} = storeToRefs(languageStore);

  const resourceStore = useResourceStore();
  const {countryList, phoneList} = storeToRefs(resourceStore);
  const {setCountryList, setPhoneList} = resourceStore;

  const userStore = useUserStore();
  const {getUserToken, setAllAddress} = userStore;
  const {allAddress} = storeToRefs(userStore);

  const commonStore = useCommonStore();
  const {setToastMessage, setToastError, unAuthGet, getRequest, postRequest, deleteRequest} = commonStore;


  const ajaxDeleting = ref(0);
  const selectedAddress = ref(-1);
  const selectedAddressObj = ref(null);

  const currentAddresses = computed(() => {
    return allAddress.value?.data || []
  });

  const route = useRoute();

  watch(selectedAddressObj, (value) => {
    if (!value) {
      emit('selected-address', null);
      return;
    }

    if (currentAddresses.value.length) {
      const countryName = countryList.value[value.country]?.name
      const stateName = value.state ? countryList.value[value.country].states[value.state]?.name : ''
      emit('selected-address', {...value, ...{countryTitle: countryName, stateTitle: stateName}})
      return;
    }

    emit('selected-address', null)
  });


  const fetchingAddressData = ref(false);

  watch(currentAddresses, (value, oldValue) => {
    if (value.length) {
      if (hasRadio.value) {
        const selectedId = selectedAddressObj.value?.id;

        if (selectedId) {
          const sameAddressIndex = value.findIndex((address) => parseInt(address.id) === parseInt(selectedId));
          if (sameAddressIndex >= 0) {
            selectedAddress.value = sameAddressIndex;
            selectedAddressObj.value = value[sameAddressIndex];
            return;
          }
        }

        if (!oldValue?.length) {
          selectedAddress.value = 0;
          selectedAddressObj.value = value[0];
          return;
        }

        if (selectedAddress.value >= 0 && value[selectedAddress.value]) {
          selectedAddressObj.value = value[selectedAddress.value];
          return;
        }

        selectedAddress.value = 0;
        selectedAddressObj.value = value[0];
      }
    } else {

      selectedAddress.value = -1
      selectedAddressObj.value = null
    }
  }, {deep: true, immediate: true});

  watch(selectedAddress, (value) => {
    if (value < 0) {
      selectedAddressObj.value = null;
      return;
    }

    selectedAddressObj.value = currentAddresses.value[value] || null
  });

  const toggleAddress = (index, event) => {
    // Keep action buttons inside the card clickable without toggling selection.
    if (event?.target && typeof event.target.closest === 'function' && event.target.closest('button')) {
      return;
    }

    if (selectedAddress.value === index) {
      selectedAddress.value = -1;
      return;
    }

    selectedAddress.value = index;
  };

  const hasAddress = computed(() => {
    if(allAddress.value?.data) {
      return !!allAddress.value?.data?.length;
    }
    return true;
  });

  const totalPage = computed(() => {
    return allAddress.value?.last_page
  });

 const addressPaginationRef = ref(null);

  const loadData = () => {
    addressPaginationRef.value?.routeParam();
  };

  const {fetchingData, formatAddress, deleteAddress} = useAddressHelper({ajaxDeleting});

  watch(() => route.query, async () => {
    await fetchingData();
  });

  onMounted(async () => {
    if (!countryList.value || !phoneList.value) {
      fetchingAddressData.value = true

      const {data} = await unAuthGet({
        params: '',
        lang: langCode.value,
        api: 'countriesPhones'
      })

      setCountryList(data?.countries)
      setPhoneList(data?.phones)
      fetchingAddressData.value = false
    }

    await fetchingData()
  })

</script>
