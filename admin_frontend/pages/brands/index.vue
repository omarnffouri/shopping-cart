<template>
  <PartialsListPage
    v-if="$can('brand', 'view')"
    ref="listPageRef"
    list-api="getBrands"
    delete-api="deleteBrand"
    route-name="brands"
    empty-store-variable="allBrands"
    :name="$t('brand.brand')"
    gate="brand"
    :order-options="orderOptions"
    @delete-bulk="deleteBulk"
    @list="setItemList"
  >
    <template v-slot:table="{list}">
      <tr class="lite-bold">
        <th class="w-50x mx-w-50x">
          <input type="checkbox" @change="checkAll">
        </th>
        <th>{{ $t('index.title') }}</th>
        <th>{{ $t('category.slug') }}</th>
        <th>{{ $t('category.featured') }}</th>
        <th>{{ $t('category.status') }}</th>
        <th>{{ $t('category.created') }}</th>
        <th>&nbsp;</th>
      </tr>

      <tr v-for="(value, index) in list" :key="index">
        <td class="w-50x mx-w-50x">
          <input type="checkbox" :value="value.id" v-model="cbList">
        </td>
        <td class="">
          <nuxt-link
            :to="`/brands/${value.id}`"
            class="dply-felx j-left link"
          >
            <ImageLazy
              class="mr-20"
              :lazy-src="getThumbImageURL(value.image)"
              :alt="value.title"
            />
            <h5 class="mx-w-300x">{{ value.title }}</h5>
          </nuxt-link>

        </td>

        <td>
          {{ value.slug }}
        </td>

        <td
          class="status"
          :class="{active: value.featured == 1 }"
        >
          <span>{{ getFeatured(value.featured) }}</span>
        </td>
        <td
          class="status"
          :class="{active: value.status == 1 }"
        >
          <span>{{ getStatus(value.status) }}</span>
        </td>
        <td>{{ value.created }}</td>
        <td>
          <button
            v-if="$can('brand', 'edit')"
            @click.prevent="editNode(value)" class="lite-btn">{{ $t('category.edit') }}</button>
          <button
            v-if="$can('brand', 'delete')"
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
    title: { title: t('index.title') },
    featured: { title: t('category.featured') },
    created_at: { title: t('category.date') },
    status: { title: t('category.status') }
  });

  const {getFeatured, getStatus, getThumbImageURL} = useUtils();
  const {cbList, deleteBulk, listPageRef, setItemList, checkAll, editNode, deleteNode} = useListHelper();

</script>

