<template>
  <PartialsListPage
    ref="listPageRef"
    list-api="getSubscribers"
    delete-api="deleteSubscriber"
    route-name="subscribers"
    :name="$t('user.subs')"
    gate="subscriber"
    :add-button="false"
    :order-options="userObj"
    @list="setItemList"
  >
    <template
      v-slot:table-top="{orderOptions}"
    >
      <PartialsTableTop
        :order-by-options="orderOptions"
        class="mb-20 mb-sm-15"
        @delete-bulk="deleteBulk"
      >
        <template
          v-slot:add-button
        >
          <nuxt-link
            :to="sendEmailRoute"
            class="button primary-btn"
          >
            {{ $t('user.sEmail') }}
          </nuxt-link>
        </template>
      </PartialsTableTop>
    </template>

    <template v-slot:table="{list}">
      <tr class="lite-bold">
        <th>
          <input type="checkbox" @change="checkAll">
        </th>
        <th>{{ $t('fSale.email') }}</th>
        <th>{{ $t('category.created') }}</th>
        <th/>
      </tr>

      <tr v-for="(value, index) in list" :key="index">
        <td>
          <input type="checkbox" :value="value.id" v-model="cbList">
        </td>
        <td><span class="mx-w-300x">{{ value.email }}</span></td>
        <td>{{ value.created }}</td>
        <td>
          <button
            v-if="$can('subscriber', 'delete')"
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
  const userObj = ref({
    created_at: {title: t('category.date')},
    email: {title: t('fSale.email')},
  });

  const route = useRoute();
  const sendEmailRoute = computed(() => {
    // Removing the trailing slash
    return `${route.path.replace(/\/$/, "")}/send-email`;
  });

  const {cbList, deleteBulk, listPageRef, setItemList, checkAll, editNode, deleteNode} = useListHelper();
</script>
