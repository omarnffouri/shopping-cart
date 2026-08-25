<template>
    <PartialsListPage
        ref="listPageRef"
        list-api="getProducts"
        delete-api="deleteProduct"
        route-name="products"
        :name="$t('title.prod')"
        :order-options="orderByProduct"
        gate="product"
        @delete-bulk="deleteBulk"
        @list="setItemList"
    >
        <template v-slot:bulk-action>
            <button @click.prevent="bulkUpdate(1)" class="outline-btn" style="white-space: nowrap">
                {{ $t('prod.mark_avail') }}
            </button>
            <button @click.prevent="bulkUpdate(0)" class="outline-btn" style="white-space: nowrap">
                {{ $t('prod.mark_unavail') }}
            </button>
        </template>
        <template v-slot:filter>
            <div class="delivery-filter"
                :class="{'delivery-filter--active': filterAvailable === '1', 'delivery-filter--open': filterOpen}"
                v-click-outside="() => filterOpen = false">
                <div class="delivery-filter__trigger" @click="filterOpen = !filterOpen">
                    <span class="delivery-filter__dot" />
                    <span class="delivery-filter__label">{{ filterLabel }}</span>
                    <svg class="delivery-filter__chevron" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="6 9 12 15 18 9"/>
                    </svg>
                </div>

                <Transition name="dropdown">
                    <div v-if="filterOpen" class="delivery-filter__dropdown">
                        <div class="delivery-filter__option"
                            :class="{ 'delivery-filter__option--selected': filterAvailable === null }"
                            @click="selectFilter(null)">
                            <span class="delivery-filter__option-dot delivery-filter__option-dot--all" />
                            <span>All Products</span>
                            <svg v-if="filterAvailable === null" class="delivery-filter__check" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="20 6 9 17 4 12"/>
                            </svg>
                        </div>
                        <div class="delivery-filter__option"
                            :class="{ 'delivery-filter__option--selected': filterAvailable === '1' }"
                            @click="selectFilter('1')">
                            <span class="delivery-filter__option-dot delivery-filter__option-dot--yes" />
                            <span>Available Today</span>
                            <svg v-if="filterAvailable === '1'" class="delivery-filter__check" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="20 6 9 17 4 12"/>
                            </svg>
                        </div>
                    </div>
                </Transition>
            </div>

            <search-dropdown
                ref="categorySearch"
                currentId="categorySearch"
                :placeholder="$t('category.catUp')"
                list-api="getAllCategories"
                :selected-text="selectedCategoryText"
                @clicked="onCategoryChange"
            />
        </template>
        <template v-slot:table="{list}">
            <tr class="lite-bold">
                <th class="w-50x mx-w-50x">
                    <input type="checkbox" @change="checkAll">
                </th>
                <th>{{ $t('index.title') }}</th>
                <th>{{ $t('category.status') }}</th>
                <th>{{ $t('prod.brand') }}</th>
                <th>{{ $t('error.cat') }}</th>
                <th>{{ $t('prod.tRule') }}</th>
                <th>{{ $t('prod.purchased') }}({{ currencyIcon }})</th>
                <th>{{ $t('prod.selling') }}({{ currencyIcon }})</th>
                <th>{{ $t('prod.offered') }}({{ currencyIcon }})</th>
                <th>{{ $t('prod.availDelivToday') }}</th>
                <th>{{ $t('category.created') }}</th>
                <th/>
                <th/>
            </tr>

            <tr v-for="(value, index) in list" :key="index">
                <td class="w-50x mx-w-50x">
                    <input type="checkbox" :value="value.id" v-model="cbList">
                </td>
                <td>
                    <nuxt-link
                        class="dply-felx j-left link"
                        :to="`/products/${value.id}`"
                    >
                        <ImageLazy
                            class="mr-20"
                            :lazy-src="getThumbImageURL(value.image)"
                            :alt="value.title"
                        />
                        <h5 class="mx-w-400x">{{ value.title }}</h5>
                    </nuxt-link>
                </td>
                <td
                    class="status"
                    :class="{active: value.status == 1 }"
                >
                    <span>{{ getStatus(value.status) }}</span>
                </td>
                <td>
                    <nuxt-link
                        v-if="value.brand"
                        class="link"
                        :to="`brands/${value.brand.id}`"
                    >
                        {{ value.brand.title }}
                    </nuxt-link>
                    <span v-else>{{ $t('prod.na') }}</span>
                </td>

                <td>
            <span class="dply-felx f-wrap gap-10 mx-w-300x j-left">
               <nuxt-link
                   v-for="(data, index) in value.product_categories"
                   :key="index"
                   class="link"
                   :to="`/categories/${data.category.id}`"
               >
                {{ data.category.title }}
              </nuxt-link>
            </span>

                </td>

                <td>
                    <nuxt-link
                        v-if="value.tax_rules"
                        class="link"
                        :to="`tax-rules/${value.tax_rules.id}`"
                    >
                        {{ value.tax_rules.title }}
                    </nuxt-link>
                    <span v-else>{{ $t('prod.na') }}</span>
                </td>
                <td>{{ value.purchased }}</td>
                <td>{{ value.selling }}</td>
                <td>
            <span v-if="value.offered">
               {{ value.offered }}
            </span>
                </td>
                <td>
                    <span class="delivery-badge"
                        :class="value.available_for_delivery_today == 1 ? 'delivery-badge--yes' : 'delivery-badge--no'">
                        <span class="delivery-badge__dot" />
                        {{ value.available_for_delivery_today == 1 ? $t('prod.yes') : $t('prod.no') }}
                    </span>
                </td>
                <td>{{ value.created }}</td>
                <td>
                    <nuxt-link
                        class="lite-btn button"
                        :to="`/rating-reviews?product=${value.id}`"
                    >
                        {{ $t('prod.reviews') }}
                    </nuxt-link>
                </td>
                <td>
                    <button
                        v-if="$can('product', 'edit')"
                        @click.prevent="editNode(value)"
                        class="lite-btn"
                    >
                        {{ $t('category.edit') }}
                    </button>
                    <button
                        v-if="$can('product', 'delete')"
                        @click.prevent="deleteNode(value)"
                        class="delete-btn lite-btn"
                    >
                        {{ $t('category.delete') }}
                    </button>
                </td>
            </tr>
        </template>
    </PartialsListPage>
</template>

<script setup>
import {storeToRefs} from "pinia";
import {useSettingStore} from "../../store/setting";
import {useListHelper} from "../../composables/useListHelper";
import {useUtils} from "../../composables/useUtils";

const route = useRoute()
const router = useRouter()

definePageMeta({
    middleware: ['common-middleware', 'auth'],
    layout: 'default',
});

const settingStore = useSettingStore();
const {setting} = storeToRefs(settingStore);

const currencyIcon = computed(() => {
    return setting.value?.currency_icon || '$'
});

const {t} = useI18n();

const orderByProduct = ref({
    title: {title: t('index.title')},
    category_id: {title: t('category.catUp')},
    purchased: {title: t('prod.purchased')},
    selling: {title: t('prod.selling')},
    offered: {title: t('prod.offered')},
    created_at: {title: t('category.date')},
    status: {title: t('category.status')}
});

const {getStatus, getThumbImageURL } = useUtils();
const {cbList, deleteBulk, listPageRef, setItemList, checkAll, editNode, deleteNode} = useListHelper();
import { useCommonStore } from '~/store/common';
import { useUiStore } from '~/store/ui';

const commonStore = useCommonStore();
const uiStore = useUiStore();
const categorySearch = ref(null);
const selectedCategoryId = ref(route.query.category_id || null)
const filterAvailable = ref(route.query.available_for_delivery_today || null)

watch(() => route.query.available_for_delivery_today, async (newVal) => {
    filterAvailable.value = newVal || null;
    await nextTick();
    listPageRef.value.fetchingData();
});

const bulkUpdate = async (val) => {
    if (cbList.value.length) {
        if (confirm(t('prod.confirm_bulk'))) {
            try {
                await commonStore.setRequest({
                    api: 'productBulkUpdate',
                    params: {
                        ids: cbList.value,
                        action: 'available_for_delivery_today',
                        value: val
                    }
                });
                uiStore.setToastMessage(t('prod.bulk_success'));
                listPageRef.value.fetchingData();
                cbList.value = [];
            } catch (e) {
                console.error(e);
            }
        }
    }
};

const filterOpen = ref(false);

const filterLabel = computed(() => {
    if (filterAvailable.value === '1') return 'Available Today';
    return 'All Products';
});

const updateQuery = async (patch) => {
    const nextQuery = { ...route.query, ...patch }

    Object.keys(nextQuery).forEach((k) => {
        if (nextQuery[k] === null || nextQuery[k] === '' || nextQuery[k] === undefined) {
            delete nextQuery[k]
        }
    })

    await router.push({ path: route.path, query: nextQuery })
}

const onCategoryChange = async (payload) => {
    if (!payload) {
        selectedCategoryId.value = null
        await updateQuery({ category_id: null, page: 1 })
        return
    }

    const id = payload?.id ?? payload?.value ?? payload?.category_id
    const text = payload?.title ?? payload?.label ?? payload?.name ?? ''

    const val = (id === null || id === undefined || id === '') ? null : String(id)

    selectedCategoryId.value = val

    await updateQuery({ category_id: val, page: 1 })
}

// Sync state + refetch when URL changes (back/forward, shared link, etc.)
watch(() => route.query.category_id,
    async (newVal) => {
        selectedCategoryId.value = newVal || null
        await nextTick()
        listPageRef.value.fetchingData()
    }
)
const selectFilter = async (val) => {
    filterAvailable.value = val
    filterOpen.value = false

    // IMPORTANT: don't clear category_id here
    await updateQuery({ available_for_delivery_today: val, page: 1 })
}

watch(() => route.query.available_for_delivery_today,
    async (newVal) => {
        filterAvailable.value = newVal || null
        await nextTick()
        listPageRef.value.fetchingData()
    }
)

const selectedCategoryText = computed(() => {
    if (!commonStore.allCategories) return null;
    return commonStore.allCategories[selectedCategoryId.value]?.title || null
})

onBeforeMount(async () => {
    await commonStore.getDropdownList()
})
</script>

<style scoped>
.delivery-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 3px 10px 3px 8px;
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 600;
    letter-spacing: 0.02em;
    white-space: nowrap;
    line-height: 1.4;
}

.delivery-badge__dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    flex-shrink: 0;
}

.delivery-badge--yes {
    background-color: #dcfce7;
    color: #15803d;
    border: 1px solid #bbf7d0;
}
.delivery-badge--yes .delivery-badge__dot {
    background-color: #22c55e;
    box-shadow: 0 0 0 2px #bbf7d0;
}

.delivery-badge--no {
    background-color: #f3f4f6;
    color: #6b7280;
    border: 1px solid #e5e7eb;
}
.delivery-badge--no .delivery-badge__dot {
    background-color: #9ca3af;
}

/* Trigger */
.delivery-filter {
    position: relative;
    display: inline-flex;
    flex-direction: column;
    margin-left: 10px;
    user-select: none;
}

.delivery-filter__trigger {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #f9fafb;
    border: 2px solid #e5e7eb;
    border-radius: 50px;
    padding: 0 10px;
    height: 42px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.delivery-filter__trigger:hover {
    border-color: #d1d5db;
    background: #f3f4f6;
}

.delivery-filter--active .delivery-filter__trigger {
    background: #f0fdf4;
    border-color: #86efac;
    box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.08);
}

.delivery-filter--open .delivery-filter__trigger {
    border-color: #22c55e;
    background: #fff;
    box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.1);
}

.delivery-filter__label {
    font-size: 0.8rem;
    font-weight: 600;
    color: #374151;
    min-width: 105px;
    transition: color 0.2s;
}

.delivery-filter--active .delivery-filter__label {
    color: #15803d;
}

.delivery-filter__dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #d1d5db;
    flex-shrink: 0;
    transition: background 0.2s, box-shadow 0.2s;
}

.delivery-filter--active .delivery-filter__dot {
    background: #22c55e;
    box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.2);
    animation: pulse-dot 2s infinite;
}

.delivery-filter__chevron {
    color: #9ca3af;
    flex-shrink: 0;
    transition: color 0.2s, transform 0.2s;
}

.delivery-filter--open .delivery-filter__chevron {
    color: #22c55e;
    transform: rotate(180deg);
}

/* Dropdown panel */
.delivery-filter__dropdown {
    position: absolute;
    top: calc(100% + 6px);
    left: 0;
    min-width: 180px;
    background: #fff;
    border: 1.5px solid #e5e7eb;
    border-radius: 10px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.09), 0 2px 6px rgba(0, 0, 0, 0.05);
    overflow: hidden;
    z-index: 50;
}

/* Options */
.delivery-filter__option {
    display: flex;
    align-items: center;
    gap: 9px;
    padding: 9px 12px;
    font-size: 0.8rem;
    font-weight: 500;
    color: #374151;
    cursor: pointer;
    transition: background 0.15s;
}

.delivery-filter__option:hover {
    background: #f9fafb;
}

.delivery-filter__option--selected {
    background: #f0fdf4;
    color: #15803d;
    font-weight: 600;
}

.delivery-filter__option-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    flex-shrink: 0;
}

.delivery-filter__option-dot--all {
    background: #d1d5db;
}

.delivery-filter__option-dot--yes {
    background: #22c55e;
    box-shadow: 0 0 0 2px rgba(34, 197, 94, 0.2);
}

.delivery-filter__check {
    margin-left: auto;
    color: #22c55e;
}

/* Dropdown animation */
.dropdown-enter-active {
    transition: opacity 0.15s ease, transform 0.15s ease;
}
.dropdown-leave-active {
    transition: opacity 0.1s ease, transform 0.1s ease;
}
.dropdown-enter-from,
.dropdown-leave-to {
    opacity: 0;
    transform: translateY(-4px);
}

@keyframes pulse-dot {
    0%, 100% { box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.2); }
    50%       { box-shadow: 0 0 0 5px rgba(34, 197, 94, 0.08); }
}

.outline-select {
    height: 42px;
    border-radius: 999px; /* fully rounded like pill buttons */
    padding: 0 14px;
    background: #fff;
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    cursor: pointer;

    /* optional: nicer text alignment */
    line-height: 42px;
}

/* optional: keep focus similar to buttons */
.outline-select:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.12);
    border-color: #22c55e;
}
</style>
