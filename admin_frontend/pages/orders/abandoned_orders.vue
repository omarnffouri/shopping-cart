<template>
  <div>
    <div v-if="posSetting"
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

    <PartialsOrderList
      v-if="isVendor"
    />

    <div v-else>
      <div style="height: 0; overflow: hidden;">
        <div v-for="(data, index) in pdfList"
             :key="index"
        >
          <PartialsInvoice
            :ref="el => setInvoiceRef(el, data.id)"
            :order="data"
          />
        </div>
      </div>

      <PartialsListPage
        ref="listPageRef"
        list-api="getAbendedOrders"
        delete-api="deleteOrder"
        route-name="orders"
        :name="$t('fSale.orderD')"
        :order-options="orderByOrders"
        gate="order"
        @deleted="deletedOrder"
        @list="setItemList"
      >
        <template v-slot:table-top="{orderOptions}">
          <div class="dply-felx gap-10 f-wrap mb-15">
            <div class="dply-felx gap-10 f-wrap">
              <PartialsTableSort
                :order-by-options="orderOptions"
              />

              <inline-pop-over
                :arrow="true"
                class="bulk-action mt-md-10"
                ref="bulkDeleteRef"
              >
                <template
                  v-slot:button
                >
                  {{ $t('title.act') }}
                </template>
                <template
                  v-slot:content
                >
                  <button @click.prevent="deleteAll" class="outline-btn">
                    {{ $t('category.delete') }}
                  </button>

                  <button @click.prevent="generatePdf" class="outline-btn">
                    {{ $t('setting.pi') }}
                  </button>
                </template>
              </inline-pop-over>
            </div>

            <date-filter
              @date-changed="filterChanged"
            />
          </div>

        </template>

        <template v-slot:table="{list}">
          <tr class="lite-bold">
            <th>
              <input type="checkbox" @change="checkAll">
            </th>
            <th>{{ $t('fSale.orderUp') }}</th>
            <th>{{ $t('category.status') }}</th>
            <th>{{ $t('fSale.pMethod') }}</th>
            <th>{{ $t('fSale.pStatus') }}</th>
            <th>{{ $t('fSale.voucher') }}</th>
            <th>{{ $t('fSale.user') }}</th>
            <th>{{ $t('fSale.amount') }}({{ currencyIcon }})</th>
            <th>{{ $t('category.created') }}</th>
            <th/>
          </tr>

          <tr
            v-for="(item, index) in list"
            :key="index"
            :class="{'new-data': !parseInt(item.viewed)}"
          >
            <td>
              <input type="checkbox" :value="item.id" v-model="cbList">
            </td>
            <td>
              <nuxt-link
                class="dply-felx j-left link"
                :to="`/orders/${item.id}`"
              >
                #{{ item.order }}
              </nuxt-link>

            </td>
            <td>{{ orderStatus[item.status].title }}</td>
            <td class="mn-w-80x">{{ paymentTypes[item.order_method] }}</td>
            <td
              :class="{'color-success': parseInt(item.payment_done) === status.PUBLIC}"
            >
              {{ parseInt(item.payment_done) === status.PUBLIC ? $t('fSale.paid') : $t('fSale.unpaid') }}
            </td>
            <td>{{ voucherStr(item) }}</td>
            <td>
              <span class="ellipsis mx-w-150x">{{ userStr(item) }}</span>
            </td>
            <td class="mn-w-90x">{{ item.total_amount }}</td>
            <td class="mn-w-90x">{{ item.created }}</td>
            <td class="ptb-10">
              <button
                v-if="$can('order', 'view')"
                @click.prevent="editNode(item)"
                class="lite-btn"
              >
                {{ $t('fSale.view') }}
              </button>
              <button
                v-if="$can('order', 'delete')"
                @click.prevent="deleteNode(item)"
                class="delete-btn lite-btn"
              >
                {{ $t('category.delete') }}
              </button>
            </td>
          </tr>
        </template>
      </PartialsListPage>
    </div>
  </div>
</template>

<script setup>
  import {useSettingStore} from "~/store/setting";
  import {useAdminStore} from "~/store/admin";
  import {storeToRefs} from "pinia";
  import {onMounted} from "vue";
  import {useConstants} from "~/composables/useConstants";

  definePageMeta({
    middleware: ['common-middleware', 'auth'],
    layout: 'default',
  });

  const settingStore = useSettingStore();
  const {setting} = storeToRefs(settingStore);

  const adminStore = useAdminStore();
  const {posSetting, isVendor} = storeToRefs(adminStore);
  const {t} = useI18n();

  const {orderStatus, paymentTypes, status, orderTypes} = useConstants();
  const {cbList, deleteBulk, listPageRef, setItemList, checkAll, editNode, deleteNode, itemList} = useListHelper();

  const pdfList = ref([]);
  const selectedOrderType = ref('website');
  const loaded = ref(false);
  const orderByOrders = ref({
    order_method: {title: t('fSale.pMethod')},
    status: {title: t('category.status')},
    total_amount: {title: t('brand.price')},
    user_id: {title: t('fSale.user')},
    created_at: {title: t('category.date')},
  });
  const checkedFilter = ref([]);

  const voucherStr = (order) => {
    if (order.voucher) {
      return `${order.voucher?.title || ''}`;
    }
    return t('prod.na');
  };

  const currencyIcon = computed(() => {
    return setting.value?.currency_icon || '$'
  });


  const router = useRouter();
  const route = useRoute();

  const selectedOption = ({key}) => {
    selectedOrderType.value = key
    router.push({
      query: {
        ...route.query,
        ...{
          order_type: key
        }
      }
    })
  };

  const bulkDeleteRef = ref(null);
  const invoiceRefs = ref({});

  const setInvoiceRef = (el, id) => {
    if (el) {
      invoiceRefs.value[id] = el;
    }
  };

  const generatePdf = async () => {
    pdfList.value = [];
    if (cbList.value?.length) {
      const orderList = [];

      itemList.value.forEach(i => {
        orderList[i.id] = i;
      });
      cbList.value.forEach(i => {
        if (orderList[i]) {
          pdfList.value.push(orderList[i]);
        }
      })
    }
    bulkDeleteRef.value.closePop();

    await nextTick();
    let i = 0;

    const downloadInterval = setInterval(() => {
      if (pdfList.value.length > i) {
        if (invoiceRefs.value[pdfList.value[i]?.id]) {
          invoiceRefs.value[pdfList.value[i].id]?.downloadPdf();
        }
      } else {
        clearInterval(downloadInterval);
      }
      i++;
    }, 200);
  };

  const deleteAll = () => {
    bulkDeleteRef.value.closePop();
    deleteBulk();
  };

  const deletedOrder = () => {
    //this.emptyDashboard()
  };

  const filterChanged = (filter = null) => {
    router.push({
      query: {
        ...route.query,
        ...filter,
        ...{
          page: 1,
          orderBy: 'created_at',
          orderByType: 'desc',
          filter: checkedFilter.value.join(','),
        }
      }
    });
  };

  const userStr = (order) => {
    return order?.address?.name ?? order?.address?.email;
  };

  onMounted(() => {
    if (route?.query?.order_type) {
      selectedOrderType.value = route?.query?.order_type;
    }
    checkedFilter.value = route?.query?.filter?.split(',') || [];
  });
</script>
