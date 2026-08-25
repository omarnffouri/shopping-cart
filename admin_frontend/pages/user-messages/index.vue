<template>
  <PartialsListPage
    ref="listPageRef"
    list-api="getUserMessages"
    delete-api="deleteUserMessage"
    route-name="user-messages"
    :name="$t('profile.umLower')"
    gate="message"
    :add-button="false"
    :order-options="orderOptions"
  >

    <template v-slot:table="{list}">

      <tr class="lite-bold">
        <th>{{ $t('user.name') }}</th>
        <th>{{ $t('fSale.email') }}</th>
        <th>{{ $t('setting.replied') }}</th>
        <th>{{ $t('category.created') }}</th>
        <th>&nbsp;</th>
      </tr>

      <tr v-for="(value, index) in list" :key="index">
        <td><span class="mx-w-300x">{{ value.name }}</span></td>
        <td><span class="mx-w-300x">{{ value.email }}</span></td>
        <td
          class="status"
          :class="{active: parseInt(value.replied) === 1 }"
        >
          <span>{{ getFeatured(value.replied) }}</span>
        </td>
        <td>{{ value.created }}</td>
        <td>
          <button
            v-if="$can('message', 'view')"
            @click.prevent="editNode(value)" class="lite-btn"
          >
            {{ $t('fSale.view') }}
          </button>
          <button
            v-if="$can('message', 'delete')"
            @click.prevent="deleteNode(value)" class="delete-btn lite-btn">
            {{ $t('category.delete') }}
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
 const orderOptions= ref({
    created_at: {title: t('category.date') },
    name: { title: t('user.name') },
    email: { title: t('fSale.email') },
    replied: { title: t('setting.replied') },
    viewed: { title: t('setting.viewed') }
  });

  const {getFeatured} = useUtils();
  const {cbList, deleteBulk, listPageRef, setItemList, checkAll, editNode, deleteNode} = useListHelper();

</script>
