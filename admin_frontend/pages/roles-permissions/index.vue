<template>
  <PartialsListPage
    ref="listPageRef"
    list-api="getRoles"
    delete-api="deleteRole"
    route-name="roles-permissions"
    :name="$t('profile.rp')"
    empty-store-variable="allRoles"
    :order-options="orderOptions"
    gate="role"
  >
    <template v-slot:table="{list}">
      <tr class="lite-bold">
        <th>{{ $t('user.name') }}</th>
        <th>{{ $t('category.created') }}</th>
        <th>&nbsp;</th>
      </tr>

      <tr v-for="(value, index) in list" :key="index">
        <td class="">
          <nuxt-link
            class="link"
            :to="`/roles-permissions/${value.id}`"
          >
            <h5 class="mx-w-300x">{{ value.name }}</h5>
          </nuxt-link>
        </td>
        <td>{{ value.created }}</td>
        <td>
          <button
            v-if="$can('role', 'edit')"
            @click.prevent="editNode(value)" class="lite-btn">{{ $t('category.edit') }}</button>
          <button
            v-if="$can('role', 'delete')"
            @click.prevent="deleteNode(value)" class="delete-btn lite-btn">{{ $t('category.delete') }}</button>
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

  const  orderOptions = ref({
    name: { title: t('user.name') },
    created_at: { title: t('category.date') },
  });

  const {listPageRef, editNode, deleteNode} = useListHelper();
</script>
