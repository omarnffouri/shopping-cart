<template>
  <PartialsListPage
    v-if="$can('header_link', 'view')"
    ref="listPageRef"
    list-api="getCustomScripts"
    delete-api="deleteCustomScript"
    route-name="custom-scripts"
    :name="$t('title.cScript')"
    gate="header_link"
    :order-options="orderOptions"
    @delete-bulk="deleteBulk"
    @list="setItemList"
  >
    <template v-slot:table="{list}">
      <tr class="lite-bold">
        <th class="w-50x mx-w-50x">
          <input type="checkbox" @change="checkAll">
        </th>
        <th>{{ $t('title.rParam') }}</th>
        <th>{{ $t('category.status') }}</th>
        <th/>
      </tr>

      <tr v-for="(value, index) in list" :key="index">
        <td class="w-50x mx-w-50x">
          <input type="checkbox" :value="value.id" v-model="cbList">
        </td>
        <td>
          <span>{{ value.route_pattern }}</span>
        </td>
        <td
          class="status"
          :class="{active: value.status == 1 }"
        >
          <span>{{ getStatus(value.status) }}</span>
        </td>
        <td>
          <button
            v-if="$can('header_link', 'edit')"
            @click.prevent="editNode(value)" class="lite-btn">{{ $t('category.edit') }}
          </button>
          <button
            v-if="$can('header_link', 'delete')"
            @click.prevent="deleteNode(value)" class="delete-btn lite-btn">{{ $t('category.delete') }}
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

  const orderOptions = ref({
    created_at: {title: t('category.date')},
    status: {title: t('category.status')},
    route_pattern: {title: t('title.rParam')}
  });

  const loading = ref(false);
  const result = ref([]);

  const {getStatus} = useUtils();
  const {cbList, deleteBulk, listPageRef, setItemList, checkAll, editNode, deleteNode} = useListHelper();
</script>

