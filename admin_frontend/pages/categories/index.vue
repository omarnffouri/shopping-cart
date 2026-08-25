<template>
    <PartialsListPage
            ref="listPageRef"
            list-api="getCategories"
            delete-api="deleteCategory"
            route-name="categories"
            empty-store-variable="allCategories"
            :name="$t('category.cat')"
            gate="category"
            :order-options="orderOptions"
            class="tree-wrapper"
            @delete-bulk="deleteBulk"
            @list="setItemList"
    >
        <template v-slot:table="{list}">
            <ul class="table-tree">
                <tree-node
                        v-for="value in list"
                        :node="value"
                        :key="value.id"
                        @edit="editNode"
                        @delete="deleteNode"
                >
                    <template v-slot:checkbox="{id}">
                        <input type="checkbox" :value="id" v-model="cbList">
                    </template>

                    <template v-slot:inner-checkbox="{id}">
                        <input type="checkbox" :value="id" v-model="cbList">
                    </template>

                </tree-node>
            </ul>
        </template>
    </PartialsListPage>
</template>

<script setup>

    import {useListHelper} from "../../composables/useListHelper";

    definePageMeta({
        middleware: ['common-middleware', 'auth'],
        layout: 'default',
    });

    const {t} = useI18n();

    const orderOptions = ref({
        created_at: {title: t('category.date')},
        title: {title: t('index.title')},
        status: {title: t('category.status')}
    });

    const {cbList, deleteBulk, listPageRef, setItemList, deleteNode, editNode} = useListHelper();


</script>
