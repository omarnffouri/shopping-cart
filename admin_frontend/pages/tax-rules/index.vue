<template>
  <PartialsListPage
    v-if="$can('tax_rule', 'view')"
    ref="listPageRef"
    list-api="getTaxRules"
    delete-api="deleteTaxRule"
    route-name="tax-rules"
    empty-store-variable="allTaxRules"
    :name="$t('brand.tRule')"
    gate="tax_rule"
    :order-options="orderOptions"
    @delete-bulk="deleteBulk"
    @list="listPageRef"
  >
    <template v-slot:table="{list}">
        <tr class="lite-bold">
          <th class="w-50x mx-w-50x">
            <input type="checkbox" @change="checkAll">
          </th>
          <th>{{ $t('index.title') }}</th>
          <th>{{ $t('brand.price') }}</th>
          <th>{{ $t('category.created') }}</th>
          <th>&nbsp;</th>
        </tr>

        <tr v-for="(item, index) in list" :key="index">
          <td class="w-50x mx-w-50x">
            <input type="checkbox" :value="item.id" v-model="cbList">
          </td>
          <td>
            <nuxt-link
              class="link"
              :to="`/tax-rules/${item.id}`"
            >
              <h5 class="mx-w-300x">{{ item.title }}</h5>
            </nuxt-link>
          </td>
          <td>{{ priceFormat({type: item.type, price: item.price, icon: currencyIcon}) }}</td>
          <td>{{ item.created }}</td>
          <td>
            <button
              v-if="$can('tax_rule', 'edit')"
              @click.prevent="editNode(item)" class="lite-btn"
            >
              {{ $t('category.edit') }}</button>
            <button
              v-if="$can('tax_rule', 'delete')"
              @click.prevent="deleteNode(item)"
              class="delete-btn lite-btn"
            >
              {{ $t('category.delete') }}</button>
          </td>
        </tr>
    </template>
  </PartialsListPage>
</template>

<script setup>
  import {useSettingStore} from "~/store/setting";
  import {storeToRefs} from "pinia";
  import {useListHelper} from "~/composables/useListHelper";
  import {useUtils} from "~/composables/useUtils";

  definePageMeta({
    middleware: ['common-middleware', 'auth'],
    layout: 'default',
  });

  const settingStore = useSettingStore();
  const {setting} = storeToRefs(settingStore);

  const {t} = useI18n();

 const orderOptions = ref({
    title: { title: t('index.title') },
    created_at: { title: t('category.date') }
  });

  const currencyIcon = computed(() => {
    return setting.value?.currency_icon || '$'
  });

  const {priceFormat} = useUtils();
  const {cbList, deleteBulk, listPageRef, setItemList, checkAll, editNode, deleteNode} = useListHelper();
</script>
