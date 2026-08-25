<template>
  <PartialsListPage
    ref="listPageRef"
    list-api="getPages"
    delete-api="deletePage"
    empty-store-variable="allPages"
    route-name="pages"
    :name="$t('admin.page')"
    :order-options="orderOptions"
    gate="page"
  >
    <template v-slot:table="{list}">
      <tr class="lite-bold">
        <th>{{ $t('index.title') }}</th>
        <th>{{ $t('category.slug') }}</th>
        <th>{{ $t('category.created') }}</th>
        <th>&nbsp;</th>
      </tr>

      <tr
        v-for="(value, index) in list"
        :key="index"
      >
        <td>
          <nuxt-link
            :to="`/pages/${value.id}`"
            class="link"
          >
            <h5 class="mx-w-300x">{{ value.title }}</h5>
          </nuxt-link>
        </td>
        <td>{{ value.slug }}</td>
        <td>{{ value.created }}</td>
        <td>
          <button
            v-if="$can('page', 'edit')"
            @click.prevent="editNode(value)" class="lite-btn">{{ $t('category.edit') }}
          </button>
          <button
            v-if="$can('page', 'delete')"
            @click.prevent="deleteNode(value)" class="delete-btn lite-btn">{{ $t('category.delete') }}
          </button>
        </td>
      </tr>
    </template>
  </PartialsListPage>
</template>

<script setup>
  import {useListHelper} from "~/composables/useListHelper";

  definePageMeta({
    middleware: ['common-middleware', 'auth'],
    layout: 'default',
  });

  const {t} = useI18n();

  const orderOptions = ref({
    created_at: {title: t('category.date')},
    title: {title: t('index.title')},
    slug: {title: t('category.slug')}
  });

  const {cbList, deleteBulk, listPageRef, setItemList, checkAll, editNode, deleteNode} = useListHelper();
</script>

