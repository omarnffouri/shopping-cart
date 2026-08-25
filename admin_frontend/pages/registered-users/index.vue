<template>
  <PartialsListPage
    ref="listPageRef"
    list-api="getUsers"
    delete-api="deleteUser"
    route-name="users"
    :name="$t('user.users')"
    gate="user"
    :add-button="false"
    :order-options="userObj"
    @delete-bulk="deleteBulk"
    @list="setItemList"
  >
    <template v-slot:table="{list}">
      <tr class="lite-bold">
        <th>
          <input type="checkbox" @change="checkAll">
        </th>
        <th>{{ $t('user.name') }}</th>
        <th>{{ $t('fSale.email') }}</th>
        <th>{{ $t('user.verified') }}</th>
        <th>{{ $t('category.created') }}</th>
        <th/>
      </tr>

      <tr
        v-for="(value, index) in list"
        :key="index"
        :class="{'new-data': !parseInt(value.viewed)}"
      >
        <td>
          <input type="checkbox" :value="value.id" v-model="cbList">
        </td>
        <td><span class="mx-w-300x">{{ getDataFromObject(value, 'name', 'n/a') }}</span></td>
        <td><span class="mx-w-300x">{{ getDataFromObject(value, 'email', 'n/a') }}</span></td>
        <td
          class="status"
          :class="{active: value.verified == 1 }"
        >
          <span>{{ getVerificationStatus(value.verified) }}</span>
        </td>
        <td>{{ value.created }}</td>
        <td>
          <button
            v-if="$can('user', 'delete')"
            @click.prevent="deleteNode(value)" class="delete-btn lite-btn">{{ $t('category.delete') }}
          </button>
        </td>
      </tr>
    </template>
  </PartialsListPage>
</template>

<script setup>
  import {useUtils} from "~/composables/useUtils";
  import {useListHelper} from "~/composables/useListHelper";

  definePageMeta({
    middleware: ['common-middleware', 'auth'],
    layout: 'default',
  });

  const {t} = useI18n();

  const userObj = ref({
    created_at: {title: t('category.date')},
    name: {title: t('user.name')},
    email: {title: t('fSale.email')},
    verified: {title: t('user.verified')}
  });

  const {getDataFromObject, getVerificationStatus} = useUtils();
  const {cbList, deleteBulk, listPageRef, setItemList, checkAll, editNode, deleteNode} = useListHelper();
</script>
