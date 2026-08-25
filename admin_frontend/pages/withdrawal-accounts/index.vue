<template>
  <PartialsListPage
    ref="listPageRef"
    list-api="getWithdrawalAccounts"
    delete-api="deleteWithdrawalAccount"
    route-name="withdrawal-accounts"
    :name="$t('admin.wAcc')"
    gate="withdrawal_account"
    :order-options="orderOptions"
    @delete-bulk="deleteBulk"
    @list="setItemList"
  >
    <template v-slot:table="{list}">
      <tr class="lite-bold">
        <th>
          <input type="checkbox" @change="checkAll">
        </th>
        <th>{{ $t('index.title') }}</th>
        <th>{{ $t('admin.default') }}</th>
        <th>{{ $t('user.name') }}</th>
        <th>{{ $t('admin.accNum') }}</th>
        <th>{{ $t('user.bank') }}</th>
        <th>{{ $t('user.branch') }}</th>
        <th />
      </tr>

      <tr v-for="(value, index) in list" :key="index">
        <td>
          <input type="checkbox" :value="value.id" v-model="cbList">
        </td>
        <td class="">
          <nuxt-link
            class="link"
            :to="`/withdrawal-accounts/${value.id}`"
          >
            <h5 class="mx-w-300x">{{ value.title }}</h5>
          </nuxt-link>
        </td>
        <td
          class="status"
          :class="{active: value.default === 1 }"
        >
          <span>{{ getFeatured(value.default) }}</span>
        </td>
        <td>{{ value.account_name }}</td>
        <td>{{ value.account_number }}</td>
        <td>{{ value.bank_name }}</td>
        <td>{{ value.branch_name }}</td>
        <td>
          <button
            v-if="$can('withdrawal_account', 'edit')"
            @click.prevent="editNode(value)"
            class="lite-btn"
          >
            {{ $t('category.edit') }}
          </button>
          <button
            v-if="$can('withdrawal_account', 'delete')"
            @click.prevent="deleteNode(value)"
            class="delete-btn lite-btn"
          >
            {{ $t('category.delete') }}
          </button>
        </td>
      </tr>
    </template>
  </PartialsListPage>
</template>

<script setup>

  import {useListHelper} from "~/composables/useListHelper";
  import {useUtils} from "~/composables/useUtils";

  definePageMeta({
    middleware: ['common-middleware', 'auth'],
    layout: 'default',
  });

  const {t} = useI18n();
  const orderOptions=ref( {
    title: { title: t('index.title') },
    default: { title: t('admin.default') },
    account_name: { title: t('user.name') },
    account_number: { title: t('admin.accNum') },
    bank_name: { title: t('user.bank')  },
    branch_name: { title: t('user.branch') },
    created_at: { title: t('category.date') }
  });

  const {getFeatured} = useUtils();
  const {cbList, deleteBulk, listPageRef, setItemList, checkAll, editNode, deleteNode} = useListHelper();

</script>
