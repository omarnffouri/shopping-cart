<template>
  <div class="dashboard">
    <div
      v-if="posSetting"
      class="mb-15 dply-felx sided"
    >
      <h4>
        {{ $t('error.orders') }}
      </h4>
      <dropdown
        class="right-dropdown"
        :selected-key="selectedOrderType"
        :options="orderTypes"
        @clicked="selectedOption"
      />
    </div>

    <div class="card-wrapper">
      <div class="dashboard-card">
        <div class="card-inner card">
          <i
            class="icon products"
          />
          <p class="f-1-2">
            {{ $t('index.tProduct') }}
          </p>
          <h3><b>{{ productCount }}</b></h3>
        </div>
      </div>
      <div v-if="isSuperAdmin" class="dashboard-card">
        <div class="card-inner card">
          <i
            class="icon users"
          />
          <p class="f-1-2">
            {{ $t('index.tUsers') }}
          </p>
          <h3><b>{{ usersCount }}</b></h3>
        </div>
      </div>
      <div class="dashboard-card">
        <div class="card-inner card">
          <i
            class="icon orders"
          />
          <p class="f-1-2">
            {{ $t('index.tOrders') }}
          </p>
          <h3><b>{{ orderCount }}</b></h3>
        </div>
      </div>
      <div class="dashboard-card">
        <div class="card-inner card">
          <i
            class="icon withdrawal"
          />
          <p class="f-1-2">
            {{ $t('index.tSells') }}
          </p>
          <h3><b>{{priceFormatting(orderAmount)}}</b></h3>
        </div>
      </div>
    </div>
    <PartialsOrderChart
      v-if="chartMonth"
      :chart-month="chartMonth"
      :monthly-order="monthlyOrder"
      @month-changed="monthChanged"
    />
    <PartialsOrderStatistic
      :order-type="selectedOrderType"
    />
  </div>
</template>

<script setup>

  import {useCommonStore} from '~/store/common';
  import {useAdminStore} from '~/store/admin';
  import {useSettingStore} from '~/store/setting';
  import {storeToRefs} from "pinia";
  import {useUtils} from "~/composables/useUtils";
  import {onMounted} from "vue";
  import {useConstants} from "~/composables/useConstants";

  definePageMeta({
    middleware: ['common-middleware', 'auth'],
    layout: 'default',
  });

  const {getRequest} = useCommonStore();

  const settingStore = useSettingStore()
  const {setting} = storeToRefs(settingStore)

  const adminStore = useAdminStore();
  const {posSetting, isSuperAdmin} = storeToRefs(adminStore)

  const selectedOrderType = ref('all');
  const chartData = ref(null);
  const dashboard = ref(null);
  const chartMonth = ref(null);
  const fetching = ref(false);

  const {getTimeZone, priceFormatting} = useUtils();
  const {orderTypes} = useConstants();

  const monthlyOrder = computed(() => {
    return chartData.value?.monthly_order || null;
  });

  const usersCount = computed(() => {
    return dashboard.value?.users || 0;
  });

  const productCount = computed(() => {
    return dashboard.value?.products || 0;
  });

  const orderCount = computed(() => {
    return dashboard.value?.orders || 0;
  });

  const orderAmount = computed(() => {
    return (parseFloat(dashboard.value?.orders_amount || 0)).toFixed(2);
  });

  const selectedOption = ({key}) => {
    selectedOrderType.value = key
    dashboard.value = false
    fetchingData()
  };

  const monthChanged = (evt) => {
    if (parseInt(evt.month) === parseInt(chartMonth.value.month)) {
      return false;
    }
    chartMonth.value = evt;
    fetchingData();
  };

  const fetchingData = async () => {
    chartData.value = null;
    const data = await getRequest({
      params: {
        ...chartMonth.value,
        ...{order_type: selectedOrderType.value},
        ...{time_zone: getTimeZone()},
        ...{dashboard: !!dashboard.value}
      },
      api: 'dashboard'
    });

    if (data?.dashboard) {
      dashboard.value = data.dashboard;
    }

    chartData.value = data.chart_data;
  };

  onMounted(() => {
    let date = new Date();
    chartMonth.value = {
      year: date.getFullYear(),
      month: date.getMonth() + 1
    };
    fetchingData();
  })
</script>
