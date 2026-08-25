<template>
  <PartialsListPage
    ref="listPageRef"
    list-api="getFlashSales"
    delete-api="deleteFlashSale"
    route-name="flash-sales"
    :name="$t('fSale.fSale')"
    :order-options="orderOptions"
    gate="flash_sale"
    @delete-bulk="deleteBulk"
    @list="setItemList"
  >
    <template v-slot:table="{list}">
        <tr class="lite-bold">
          <th>
            <input type="checkbox" @change="checkAll">
          </th>
          <th>{{ $t('index.title') }}</th>
          <th>{{ $t('category.status') }}</th>
          <th>{{ $t('prod.sTime') }}</th>
          <th>{{ $t('prod.eTime') }}</th>
          <th>{{ $t('category.created') }}</th>
          <th>&nbsp;</th>
        </tr>

        <tr v-for="(value, index) in list" :key="index">
          <td>
            <input type="checkbox" :value="value.id" v-model="cbList">
          </td>
          <td>
            <nuxt-link
              class="link"
              :to="`/flash-sales/${value.id}`"
            >
              <h5 class="mx-w-300x">{{ value.title }}</h5>
            </nuxt-link>
          </td>
          <td
            class="status"
            :class="{active: value.status == 1 }"
          >
            <span>{{ getStatus(value.status) }}</span>
          </td>
          <td>{{ value.start_time }}</td>
          <td>{{ value.end_time }}</td>
          <td>{{ value.created }}</td>
          <td>
            <button
              v-if="$can('flash_sale', 'edit')"
              @click.prevent="editNode(value)" class="lite-btn">{{ $t('category.edit') }}</button>
            <button
              v-if="$can('flash_sale', 'delete')"
              @click.prevent="deleteNode(value)" class="delete-btn lite-btn">{{ $t('category.delete') }}</button>
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

  const orderOptions = ref({
    created_at: { title: t('category.date') },
    title: {title: t('index.title') },
    status: { title: t('category.status') }
  });

  const { getStatus } = useUtils();
  const { cbList, deleteBulk, listPageRef, setItemList, checkAll, editNode, deleteNode } = useListHelper();
</script>
