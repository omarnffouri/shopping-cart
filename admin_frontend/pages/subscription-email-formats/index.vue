<template>
  <PartialsListPage
    ref="listPageRef"
    list-api="getSubscriptionEmailFormats"
    delete-api="deleteSubscriptionEmailFormat"
    route-name="subscription-email-formats"
    :name="$t('dataPage.sef')"
    gate="subscription_email_format"
    empty-store-variable="allSubscriptionEmailFormats"
    :order-options="orderOptions"
  >
    <template v-slot:table="{list}">
      <tr class="lite-bold">
        <th>{{ $t('index.title') }}</th>
        <th>{{ $t('util.sub') }}</th>
        <th>{{ $t('category.created') }}</th>
        <th/>
      </tr>

      <tr v-for="(value, index) in list" :key="index">
        <td>{{ value.title }}</td>
        <td>{{ value.subject }}</td>
        <td>{{ value.created }}</td>
        <td>

          <button
            v-if="$can('subscription_email_format', 'edit')"
            @click.prevent="editNode(value)" class="lite-btn">{{ $t('category.edit') }}
          </button>
          <button
            v-if="$can('subscription_email_format', 'delete')"
            @click.prevent="deleteNode(value)" class="delete-btn lite-btn"
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

  definePageMeta({
    middleware: ['common-middleware', 'auth'],
    layout: 'default',
  });

  const {t} = useI18n();
  const orderOptions = ref({
    title: {title: t('index.title')},
    subject: {title: t('util.sub')},
    created_at: {title: t('category.date')},
  });

  const {cbList, deleteBulk, listPageRef, setItemList, checkAll, editNode, deleteNode} = useListHelper();
</script>

