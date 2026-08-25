<template>
  <div>
    <error-formatter/>

    <PartialsWithdrawal
      v-if="$can('withdrawal_request', 'create')"
      ref="withdrawalRef"
      @withdraw-done="reloadList"
    />
    <partialsListPage
      ref="listPageRef"
      list-api="getWithdrawalRequests"
      delete-api="deleteWithdrawalRequest"
      route-name="withdrawal-requests"
      :name="$t('user.wReq')"
      gate="withdrawal_request"
      :order-options="orderOptions"
      @list="setItemList"
    >
      <template
        v-slot:table-top="{orderOptions}"
      >
        <div class="dply-felx gap-10 j-left f-wrap">
          <PartialsTableSort
            :order-by-options="orderOptions"
          />
          <InlinePopOver
            :arrow="true"
            class="bulk-action"
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
            </template>
          </InlinePopOver>
        </div>
      </template>

      <template v-slot:table="{list}">
        <tr class="lite-bold">
          <th>
            <input type="checkbox" @change="checkAll">
          </th>
          <th>{{ $t('index.amount') }}({{ currencyIcon }})</th>
          <th>{{ $t('user.apprBy') }}</th>
          <th>{{ $t('category.status') }}</th>
          <th>{{ $t('user.bank') }}</th>
          <th>{{ $t('user.branch') }}</th>
          <th>{{ $t('user.aName') }}</th>
          <th>{{ $t('user.acc') }}</th>
          <th>{{ $t('user.msg') }}</th>
          <th>{{ $t('category.created') }}</th>
          <th/>
        </tr>

        <tr v-for="(value, index) in list" :key="index">
          <td>
            <input type="checkbox" :value="value.id" v-model="cbList">
          </td>
          <td>{{ value.amount }}</td>
          <td>{{ getDataFromObject(value, 'approved_admin.username', $t('prod.na')) }}</td>
          <td
            class="status"
            :class="value.status === 1 ? 'active' : value.status === 2 ? 'info': ''"
          >
            <span>{{ withdrawalStatus[value.status] }}</span>
          </td>
          <td>{{ value.withdrawal_account.bank_name }}</td>
          <td>{{ value.withdrawal_account.branch_name }}</td>
          <td>{{ value.withdrawal_account.account_name }}</td>
          <td>{{ value.withdrawal_account.account_number }}</td>
          <td><p class="mx-w-150x">{{ value.message }}</p></td>
          <td>{{ value.created }}</td>
          <td>
            <button
              v-if="$can('withdrawal_request', 'delete')"
              @click.prevent="deleteNode(value)"
              class="delete-btn lite-btn"
            >
              {{ $t('category.delete') }}
            </button>
            <button
              v-if="withdrawalStatusIn.PENDING === parseInt(value.status) && $can('withdrawal_request', 'cancel')"
              class="delete-btn lite-btn"
              @click.prevent="openCancellation(value.id)"
            >
              {{ $t('title.cancel') }}
            </button>
            <button
              v-if="withdrawalStatusIn.PENDING === parseInt(value.status) && $can('withdrawal_request', 'approve')"
              class="edit-btn lite-btn"
              @click.prevent="approveWithdrawal(value.id)"
            >
              {{ $t('title.approve') }}
            </button>

          </td>
        </tr>
        <PartialsSancelWithdrawal
          v-if="cancelWithdrawalPopup && cancellationId"
          :id="cancellationId.toString()"
          v-outside-click="closeCancellation"
          @close="closeCancellation"
        />
      </template>
    </partialsListPage>
  </div>
</template>

<script setup>
  import {useSettingStore} from '~/store/setting';
  import {useUiStore} from '~/store/ui';
  import {useCommonStore} from '~/store/common';
  import {useListHelper} from "~/composables/useListHelper";
  import {useUtils} from "~/composables/useUtils";
  import {useConstants} from "~/composables/useConstants";

  definePageMeta({
    middleware: ['common-middleware', 'auth'],
    layout: 'default',
  });

  const {setRequest} = useCommonStore();
  const settingStore = useSettingStore();
  const {setting} = storeToRefs(settingStore);
  const {setErrors} = useUiStore()
  const cancellationId = ref('');
  const {t} = useI18n();
  const cancelWithdrawalPopup = ref(false);

  const orderOptions = ref({
    amount: {title: t('index.amount')},
    status: {title: t('category.status')},
    approved_by: {title: t('user.apprBy')},
    withdrawal_account_id: {title: t('user.wAcc')},
    created_at: {title: t('category.date')},
  });

  const currencyIcon = computed(() => {
    return setting.value?.currency_icon || '$';
  });

  const bulkDeleteRef = ref(null);

  const deleteAll = () => {
    bulkDeleteRef.value.closePop();
    deleteBulk();
  };

  const withdrawalRef = ref(null);

  const reloadWithdrawal = () => {
    withdrawalRef.value?.fetchingData();
  };

  const {withdrawalStatus, withdrawalStatusIn} = useConstants();
  const {getDataFromObject} = useUtils();
  const {cbList, deleteBulk, listPageRef, setItemList, checkAll, editNode, deleteNode} = useListHelper();

  const reloadList = () => {
    listPageRef.value?.fetchingData();
  };

  const openCancellation = (id) => {
    setErrors(null);
    cancellationId.value = id;
    cancelWithdrawalPopup.value = true;
  };

  const approveWithdrawal = async (id) => {
    const data = await setRequest({
      params: {
        id: id,
      },
      api: 'approveWithdrawalRequest'
    });
    if (data) {
      reloadList();
    }
  };

  const closeCancellation = (evt) => {
    setErrors(null);
    cancellationId.value = '';
    cancelWithdrawalPopup.value = false;
    if (evt?.reload) {
      reloadList();
      reloadWithdrawal();
    }
  };
</script>
