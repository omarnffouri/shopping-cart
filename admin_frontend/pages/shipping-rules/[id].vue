<template>
  <PartialsDataPage
      ref="dataPage"
      set-api="setDeliverySlotConfig"
      get-api="getDeliverySlotConfig"
      route-name="delivery-slot-config"
      empty-store-variable="allDeliveryConfigs"
      name="Delivery Slot Configuration"
      :validation-keys="['type_name', 'type_code', 'price']"
      :result="result"
      gate="delivery_slot_config"
      @result="resultUpdated"
  >
    <template v-slot:form="{hasError}">
      <div v-if="loading" class="spinner-wrapper">
        <spinner :radius="60" color="primary" class="mr-15" />
      </div>


      <div v-if="errors.general" class="error mb-20">
        {{ errors.general }}
      </div>

      <div class="section-wrapper mb-30">
        <h4 class="mb-20">General Information</h4>

        <div class="input-grid mb-20">
          <div class="input-wrapper">
            <label>Type Name</label>
            <input
                type="text"
                placeholder="Type Name"
                name="type_name"
                v-model="result.type_name"
                ref="type_name"
                :class="{invalid: (!!!result.type_name && hasError) || errors.type_name}"
            >
            <span class="error" v-if="!!!result.type_name && hasError">
              Type Name is required
            </span>
            <span class="error" v-if="errors.type_name">
              {{ errors.type_name[0] }}
            </span>
          </div>

          <div class="input-wrapper">
            <label>Type Code</label>
            <input
                type="text"
                placeholder="Type Code"
                name="type_code"
                v-model="result.type_code"
                :class="{invalid: (!!!result.type_code && hasError) || errors.type_code}"
            >
            <span class="error" v-if="!!!result.type_code && hasError">
              Type Code is required
            </span>
            <span class="error" v-if="errors.type_code">
              {{ errors.type_code[0] }}
            </span>
          </div>

          <div class="input-wrapper price-wrapper">
            <label>Price</label>
            <div class="price-input-group">
              <input
                  type="number"
                  step="any"
                  placeholder="Price"
                  v-model="result.price"
                  :class="{invalid: (!!!result.price && hasError) || errors.price}"
              >
              <dropdown
                  class="currency-dropdown"
                  :selected-key="result.currency"
                  :options="currencies"
                  key-name="code"
                  @clicked="selectedCurrency"
              />
            </div>
            <span class="error" v-if="!!!result.price && hasError">
              Price is required
            </span>
            <span class="error" v-if="errors.price">
              {{ errors.price[0] }}
            </span>
          </div>
        </div>

        <div class="input-grid-4">
          <div class="input-wrapper">
            <label class="label-with-info">
              Cutoff Time
              <button
                  type="button"
                  class="info-btn"
                  @click="toggleCutoffInfo"
                  aria-label="Cutoff time info"
                  title="More info"
              >
                ℹ️
              </button>

              <span v-if="showCutoffInfo" class="info-popover">
                  After this cutoff time, this delivery type will no longer be available for selection.
              </span>
            </label>

            <input type="time" v-model="result.cutoff_time">

            <span class="error" v-if="errors.cutoff_time">{{ errors.cutoff_time[0] }}</span>
          </div>


          <div class="input-wrapper">
            <label>Display Order</label>
            <input type="number" placeholder="Display Order" v-model="result.display_order">
            <span class="error" v-if="errors.display_order">{{ errors.display_order[0] }}</span>
          </div>

          <div class="toggle-wrapper">
            <label>Available Today</label>
            <div class="toggle-group">
              <input
                  type="checkbox"
                  id="available-today"
                  :true-value="1"
                  :false-value="0"
                  v-model="result.available_today"
                  class="toggle-switch"
              />
              <label for="available-today" class="toggle-label"></label>
            </div>
          </div>

          <div class="toggle-wrapper">
            <label>Is Active</label>
            <div class="toggle-group">
              <input
                  type="checkbox"
                  id="is-active"
                  :true-value="1"
                  :false-value="0"
                  v-model="result.is_active"
                  class="toggle-switch"
              />
              <label for="is-active" class="toggle-label"></label>
            </div>
          </div>
        </div>
      </div>

      <div class="section-wrapper mb-30">
        <h4 class="mb-20">Delivery Location</h4>
        <p class="text-muted mb-20">
          Select the state{{ isCreateMode ? 's' : '' }} where this delivery type applies.
        </p>

        <div class="input-wrapper">
          <label>State{{ isCreateMode ? 's' : '' }}</label>
          <CustomDropdown
              :key="dropdownKey"
              :options="uaeStatesObj"
              :selectedKey="result.state"
              :multiSelect="isCreateMode"
              keyName="name"
              @clicked="onStateSelected"
          />

          <div v-if="isCreateMode && result.state && result.state.length > 0" class="selected-states-tags mt-10">
            <span
                v-for="stateCode in result.state"
                :key="stateCode"
                class="state-tag"
            >
              {{ uaeStatesObj[stateCode]?.name }}
              <button
                  type="button"
                  @click.prevent="removeState(stateCode)"
                  class="remove-tag"
              >
                ×
              </button>
            </span>
          </div>

          <span class="error" v-if="errors.state">{{ errors.state[0] }}</span>
        </div>
      </div>

      <div class="section-wrapper">
        <div class="mb-20">
          <h4>Dynamic Time Slots</h4>
          <p class="text-muted">Configure specific delivery windows and cutoff logic.</p>
        </div>

        <div class="slots-wrapper">
          <div class="table-wrapper">
            <table class="time-slots-table">
              <thead>
              <tr>
                <th>SLOT NAME</th>
                <th>START TIME</th>
                <th>END TIME</th>
                <th>AVAILABLE IF BEFORE</th>
                <th>ORDER</th>
                <th>IS ACTIVE</th>
                <th class="action-column">ACTIONS</th>
              </tr>
              </thead>
              <tbody>
              <tr v-for="(slot, index) in activeTimeSlots" :key="slot.id || slot.slot_id || index">
                <td>
                  <input
                      type="text"
                      class="table-input auto-generated"
                      :value="generateSlotName(slot.start_time, slot.end_time)"
                      disabled
                      placeholder="Auto-generated from times"
                  >
                </td>
                <td>
                  <div class="time-input-wrapper">
                    <span class="time-icon">🕐</span>
                    <input
                        type="time"
                        class="table-input time-input"
                        v-model="slot.start_time"
                        @input="updateSlotName(slot)"
                    >
                  </div>
                </td>
                <td>
                  <div class="time-input-wrapper">
                    <span class="time-icon">🕐</span>
                    <input
                        type="time"
                        class="table-input time-input"
                        v-model="slot.end_time"
                        @input="updateSlotName(slot)"
                    >
                  </div>
                </td>
                <td>
                  <div class="time-input-wrapper">
                    <span class="time-icon">🕐</span>
                    <input
                        type="time"
                        class="table-input time-input"
                        v-model="slot.available_if_before"
                        @input="markSlotAsModified(slot)"
                    >
                  </div>
                </td>
                <td>
                  <input
                      type="number"
                      class="table-input"
                      v-model="slot.display_order"
                      @input="markSlotAsModified(slot)"
                  >
                </td>
                <td>
                  <div class="toggle-group centered">
                    <input
                        type="checkbox"
                        :id="`slot-active-${index}`"
                        :true-value="1"
                        :false-value="0"
                        v-model="slot.is_active"
                        @change="markSlotAsModified(slot)"
                        class="toggle-switch"
                    />
                    <label :for="`slot-active-${index}`" class="toggle-label"></label>
                  </div>
                </td>
                <td class="action-column">
                  <div class="action-buttons-row">
                    <button
                        v-if="slot.modified && slot.id"
                        class="icon-btn update-btn"
                        @click.prevent="updateTimeSlot(slot)"
                        :disabled="slot.updating">
                      <svg v-if="!slot.updating" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M1.5 0h11.586a1.5 1.5 0 0 1 1.06.44l1.415 1.414A1.5 1.5 0 0 1 16 2.914V14.5a1.5 1.5 0 0 1-1.5 1.5h-13A1.5 1.5 0 0 1 0 14.5v-13A1.5 1.5 0 0 1 1.5 0M1 1.5v13a.5.5 0 0 0 .5.5H2v-4.5A1.5 1.5 0 0 1 3.5 9h9a1.5 1.5 0 0 1 1.5 1.5V15h.5a.5.5 0 0 0 .5-.5V2.914a.5.5 0 0 0-.146-.353l-1.415-1.415A.5.5 0 0 0 13.086 1H13v3.5A1.5 1.5 0 0 1 11.5 6h-7A1.5 1.5 0 0 1 3 4.5V1H1.5a.5.5 0 0 0-.5.5m9.5-.5a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5z"/>
                      </svg>
                      <span v-if="slot.updating" class="spinner-small"></span>
                    </button>
                    <button
                        class="icon-btn delete-btn"
                        @click.prevent="deleteTimeSlot(slot)"
                        :disabled="slot.deleting"
                    >
                      <svg v-if="!slot.deleting" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M5.5 5.5A.5.5 0 0 1 6 5h4a.5.5 0 0 1 0 1H6a.5.5 0 0 1-.5-.5zm1 2A.5.5 0 0 1 7 7h2a.5.5 0 0 1 0 1H7a.5.5 0 0 1-.5-.5zm1 2A.5.5 0 0 1 8 9h0a.5.5 0 0 1 0 1h0a.5.5 0 0 1 0-1zm5-5a.5.5 0 0 1 .5.5V13a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5.5A.5.5 0 0 1 3.5 5h1a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 .5.5h1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1h-10a.5.5 0 0 1-.5-.5z"/>
                      </svg>
                      <span v-if="slot.deleting" class="spinner-small"></span>
                    </button>
                  </div>
                </td>
              </tr>
              </tbody>
            </table>
          </div>

          <button class="add-slot-btn mt-20" @click.prevent="addTimeSlot">⊕ Add Slot</button>

          <div class="action-buttons mt-30">
            <button class="cancel-btn" @click.prevent="cancelChanges">Cancel</button>
            <button class="create-btn" @click.prevent="saveChanges">Update Delivery Configuration</button>
          </div>
        </div>
      </div>
    </template>
  </PartialsDataPage>

  <div v-if="showSlotDeleteWarning" class="modal-overlay">
    <div class="modal">
      <h3>Delete Time Slot?</h3>

      <p>
        This time slot will be deleted <strong>immediately</strong> and
        <strong>cannot be undone</strong>.
      </p>

      <div class="modal-actions">
        <button class="btn-confirm" @click="confirmDeleteSlot">
          Yes, Delete
        </button>

        <button class="btn-cancel" @click="cancelDeleteSlot">
          Cancel
        </button>
      </div>
    </div>
  </div>

</template>

<script setup>
import {useLanguageStore} from "~/store/language";
import {onMounted, onBeforeUnmount, ref, computed} from "vue";
import countriesData from '~/../backend/storage/resources/countries.json';
import Service from "~/services/service";
import {useAuthStore} from "~/store/auth.js";
import CustomDropdown from "~/components/CustomStateDropdown.vue";

definePageMeta({ middleware: ['common-middleware', 'auth'], layout: 'default' });

const showSlotDeleteWarning = ref(false);
const slotToDelete = ref(null);

const router = useRouter();
const route = useRoute();
const languageStore = useLanguageStore();
const userStore = useAuthStore();
const { token } = userStore;

const loading = ref(false);
const errors = ref({});
const dropdownKey = ref(0);
const showCutoffInfo = ref(false);

const toggleCutoffInfo = () => {
  showCutoffInfo.value = !showCutoffInfo.value;
};

const onDocClick = (e) => {
  const pop = e.target.closest?.('.label-with-info');
  if (!pop) showCutoffInfo.value = false;
};


const currencies = ref([
  {code: 'AED', name: 'AED'},
  {code: 'USD', name: 'USD'},
  {code: 'EUR', name: 'EUR'},
  {code: 'GBP', name: 'GBP'},
]);

const result = ref({
  id: '',
  type_name: '',
  type_code: '',
  description: '',
  price: 0,
  currency: 'AED',
  min_hours_advance: 0,
  cutoff_time: '',
  display_order: 1,
  available_today: 0,
  available_if_before: '',
  is_active: 1,
  country: 'AE',
  state: [],
  time_slots: [{
    slot_id: '',
    slot_name: '',
    start_time: '',
    end_time: '',
    available_if_before: '',
    display_order: 1,
    is_active: 1,
    deleted: false,
    modified: false,
    updating: false,
    deleting: false,
  }],
});

const uaeStatesObj = computed(() => {
  const uae = countriesData['AE'];
  if (!uae || !uae.states) return {}

  return {
    default: { code: '', name: 'select a state' },
    ...Object.fromEntries(Object.values(uae.states).map(s => [s.code, s]))
  }
});

const onStateSelected = (evt) => {
  result.value.state = evt.key;
  result.value.country = 'AE';
};

const activeTimeSlots = computed(() => result.value.time_slots ? result.value.time_slots.filter(i => !i.deleted) : []);

const selectedCurrency = (evt) => {
  result.value.currency = evt.key;
};

// Generate slot name from start and end times
const generateSlotName = (startTime, endTime) => {
  if (!startTime || !endTime) return '';

  // Convert 24-hour format to 12-hour format with AM/PM
  const formatTime = (time) => {
    const [hours, minutes] = time.split(':');
    const hour = parseInt(hours);
    const period = hour >= 12 ? 'PM' : 'AM';
    const displayHour = hour === 0 ? 12 : hour > 12 ? hour - 12 : hour;
    return `${String(displayHour).padStart(2, '0')}:${minutes} ${period}`;
  };

  return `${formatTime(startTime)} - ${formatTime(endTime)}`;
};

// Update slot name when start or end time changes
const updateSlotName = (slot) => {
  slot.slot_name = generateSlotName(slot.start_time, slot.end_time);
  markSlotAsModified(slot);
};

const markSlotAsModified = (slot) => {
  if (slot.id) {
    slot.modified = true;
  }
};

const updateTimeSlot = async (slot) => {
  if (!slot.id) return;

  slot.updating = true;

  try {
    const lang = languageStore.langCode;
    const payload = {
      slot_name: slot.slot_name,
      start_time: slot.start_time,
      end_time: slot.end_time,
      available_if_before: slot.available_if_before,
      display_order: String(slot.display_order),
      is_active: String(slot.is_active)
    };

    const headers = { 'X-lang': lang };

    const res = await Service.updateRequest(
        slot.id,
        payload,
        token,
        'updateTimeSlot',
        lang,
        headers
    );

    if (res && res.data) {
      slot.modified = false;
      console.log('Time slot updated successfully');
    } else {
      console.error('Failed to update time slot:', res);
    }
  } catch (e) {
    console.error('Error updating time slot:', e);
  } finally {
    slot.updating = false;
  }
};

const deleteTimeSlot = (slot) => {
  if (!slot.id) {
    const index = result.value.time_slots.indexOf(slot);
    if (index > -1) {
      result.value.time_slots.splice(index, 1);
    }
    return;
  }

  slotToDelete.value = slot;
  showSlotDeleteWarning.value = true;
};

const addTimeSlot = () => {
  if(!result.value.time_slots) result.value.time_slots = [];
  const lastOrder = Math.max(...result.value.time_slots.map(s => s.display_order || 0), 0);
  const nextId = Math.max(...result.value.time_slots.map(s => s.slot_id || 0), 0) + 1;

  result.value.time_slots.push({
    slot_id: nextId,
    slot_name: '',
    start_time: '',
    end_time: '',
    available_if_before: '',
    display_order: lastOrder + 1,
    is_active: 1,
    deleted: false,
    modified: false,
    updating: false,
    deleting: false,
  });
};

const isCreateMode = computed(() => {
  const id = route.params.id;
  return !id || id === 'create';
});

const cancelChanges = () => router.push('/shipping-rules');

const saveChanges = async () => {
  loading.value = true;
  errors.value = {};
  try {
    const lang = languageStore.langCode;
    const id = route.params.id;

    let stateValue;
    if (isCreateMode.value) {
      stateValue = Array.isArray(result.value.state)
          ? result.value.state
          : (result.value.state ? [result.value.state] : []);
    } else {
      stateValue = result.value.state || '';
    }

    const payload = {
      type_name: result.value.type_name,
      price: String(result.value.price),
      currency: result.value.currency,
      country: result.value.country || '',
      ...(isCreateMode.value
              ? { states: stateValue }
              : { state: stateValue }
      ),
      type_code: result.value.type_code,
      description: result.value.description || '',
      available_for_today: !!result.value.available_today,
      cutoff_time: result.value.cutoff_time,
      available_if_before: result.value.available_if_before,
      display_order: String(result.value.display_order),
      is_active: !!result.value.is_active,
      min_hours_advance: String(result.value.min_hours_advance),

      time_slots: result.value.time_slots
          .filter(s => !s.deleted)
          .map((s, idx) => ({
            id: s.id || null,
            delivery_type_id: result.value.id || null,
            slot_name: s.slot_name || generateSlotName(s.start_time, s.end_time) || `Slot ${idx + 1}`,
            start_time: s.start_time,
            end_time: s.end_time,
            available_if_before: s.available_if_before,
            display_order: Number(s.display_order) || idx + 1,
            is_active: !!s.is_active,
            created_at: new Date().toISOString().slice(0, 19).replace('T', ' '),
            updated_at: new Date().toISOString().slice(0, 19).replace('T', ' ')
          }))
    };

    if (isCreateMode.value) {
      if (!result.value.state || result.value.state.length === 0) {
        errors.value.state = ['Please select at least one state.'];
        loading.value = false;
        return;
      }
    } else {
      if (!result.value.state) {
        errors.value.state = ['Please select a state.'];
        loading.value = false;
        return;
      }
    }

    const headers = { 'X-lang': lang };
    let res;

    if (id && id !== 'create') {
      res = await Service.updateRequest(id, payload, token, 'getDeliveryTypes', lang, headers);
    } else {
      res = await Service.setRequest(payload, token, 'getDeliveryTypes', lang, headers);
    }

    if (res && res.data) {
      router.push('/shipping-rules');
    } else {
      if (res && !res.data) {
        errors.value = res.errors;
      }
      if (res.message) {
        errors.value.general = res.message;
      }
    }
  } catch (e) {
    if (e.response && e.response.data) {
      if (e.response.data.errors) {
        errors.value = e.response.data.errors;
      }
      if (e.response.data.message) {
        errors.value.general = e.response.data.message;
      }
    } else {
      errors.value.general = 'An unexpected error occurred';
    }
  } finally {
    loading.value = false;
  }
};

const resultUpdated = (res) => {
  if(res) {
    Object.assign(result.value, res);
    dropdownKey.value++;
  }
};

onBeforeUnmount(() => {
  document.removeEventListener('click', onDocClick);
});

onMounted(async () => {
  if (!languageStore.langCode) {
    languageStore.setLangCode(languageStore.defaultLanguage.code)
  }

  document.addEventListener('click', onDocClick);

  const id = route.params.id;
  if (id && id !== 'create') {
    loading.value = true;
    try {
      const data = await Service.getByIdRequest(id, token, 'getDeliveryTypes', languageStore.langCode);

      if (data) {
        result.value = {
          ...result.value,
          id: data.id,
          type_name: data.type_name,
          type_code: data.type_code,
          description: data.description || '',
          price: data.price,
          currency: data.currency || 'AED',
          country: data.country || 'AE',
          state: data.state || '',
          min_hours_advance: data.min_hours_advance || 0,
          cutoff_time: data.cutoff_time || '',
          display_order: data.display_order || 1,
          available_today: data.available_for_today || 0,
          available_if_before: data.available_if_before || '',
          is_active: data.is_active || 1,
          time_slots: data.time_slots && data.time_slots.length > 0
              ? data.time_slots.map(slot => ({
                id: slot.id,
                slot_id: slot.id,
                delivery_type_id: slot.delivery_type_id,
                slot_name: slot.slot_name,
                start_time: slot.start_time,
                end_time: slot.end_time,
                available_if_before: slot.available_if_before || '',
                display_order: slot.display_order || 1,
                is_active: slot.is_active || 1,
                deleted: false,
                modified: false,
                updating: false,
                deleting: false,
              }))
              : [{
                slot_id: '',
                slot_name: '',
                start_time: '',
                end_time: '',
                available_if_before: '',
                display_order: 1,
                is_active: 1,
                deleted: false,
                modified: false,
                updating: false,
                deleting: false,
              }]
        };
        dropdownKey.value++;
      }
    } catch(e) {
      console.error('Error fetching delivery type:', e);
      errors.value.general = 'Failed to load delivery type data';
    } finally {
      loading.value = false;
    }
  } else {
    result.value.state = [];
  }
});

const cancelDeleteSlot = () => {
  showSlotDeleteWarning.value = false;
  slotToDelete.value = null;
};

const confirmDeleteSlot = async () => {
  const slot = slotToDelete.value;
  if (!slot) return;

  showSlotDeleteWarning.value = false;
  slot.deleting = true;

  try {
    const lang = languageStore.langCode;

    const res = await Service.deleteData(
        slot.id,
        token,
        'deleteTimeSlot',
        lang,
        slot.id
    );

    if (res && res.status === 'success') {
      const index = result.value.time_slots.indexOf(slot);
      if (index > -1) {
        result.value.time_slots.splice(index, 1);
      }
    } else {
      console.error('Failed to delete time slot:', res);
    }
  } catch (e) {
    console.error('Error deleting time slot:', e);
  } finally {
    slot.deleting = false;
    slotToDelete.value = null;
  }
};

const removeState = (stateCode) => {
  if (Array.isArray(result.value.state)) {
    result.value.state = result.value.state.filter(code => code !== stateCode);
  }
};

</script>

<style scoped>
.section-wrapper {
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  padding: 24px;
}

.input-grid {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr;
  gap: 16px;
}

.input-grid-4 {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 10px;
}

.price-wrapper .price-input-group {
  display: flex;
  gap: 8px;
}

.price-wrapper .price-input-group input {
  flex: 1;
}

.price-wrapper .currency-dropdown {
  width: 100px;
}

.toggle-wrapper {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.toggle-group {
  display: flex;
  align-items: center;
  gap: 8px;
}

.toggle-group.centered {
  justify-content: center;
}

.toggle-switch {
  position: relative;
  width: 48px;
  height: 26px;
  -webkit-appearance: none;
  appearance: none;
  background: #e5e7eb;
  outline: none;
  border-radius: 26px;
  cursor: pointer;
  transition: background 0.3s ease;
  border: none;
  box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.1);
}

.toggle-switch.sm {
  width: 36px;
  height: 20px;
}
.toggle-switch.sm::before {
  width: 14px;
  height: 14px;
  left: 3px;
}
.toggle-switch.sm:checked::before {
  left: 19px;
}

.toggle-switch:checked {
  background: #10b981;
}

.toggle-switch::before {
  content: '';
  position: absolute;
  width: 18px;
  height: 18px;
  border-radius: 50%;
  top: 50%;
  transform: translateY(-50%);
  left: 4px;
  background: #fff;
  transition: left 0.3s ease, box-shadow 0.3s ease, transform 0.3s ease;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.toggle-switch:checked::before {
  left: 26px;
}

.toggle-label {
  cursor: pointer;
}

.table-wrapper {
  overflow-x: auto;
}

.time-slots-table {
  width: 100%;
  border-collapse: collapse;
}

.time-slots-table th,
.time-slots-table td {
  padding: 12px;
  text-align: left;
  border-bottom: 1px solid #e5e7eb;
}

.time-slots-table th {
  background: #f9fafb;
  font-weight: 600;
  font-size: 12px;
  text-transform: uppercase;
  color: #6b7280;
}

.table-input {
  width: 100%;
  padding: 8px 12px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 14px;
}

.table-input.auto-generated {
  background-color: #f9fafb;
  color: #6b7280;
  cursor: not-allowed;
}

.time-input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.time-icon {
  position: absolute;
  left: 10px;
  font-size: 16px;
  pointer-events: none;
  color: #6b7280;
}

.time-input {
  padding-left: 35px !important;
}

.action-column {
  width: 100px;
  text-align: center;
}

.action-buttons-row {
  display: flex;
  gap: 8px;
  justify-content: center;
  align-items: center;
}

.icon-btn {
  background: none;
  border: none;
  cursor: pointer;
  font-size: 18px;
  padding: 4px 8px;
  transition: opacity 0.2s;
}

.icon-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.update-btn {
  color: #2563eb;
}

.update-btn:hover:not(:disabled) {
  opacity: 0.7;
}

.delete-btn {
  color: #ef4444;
}

.delete-btn:hover:not(:disabled) {
  opacity: 0.7;
}

.spinner-small {
  display: inline-block;
  width: 14px;
  height: 14px;
  border: 2px solid rgba(0, 0, 0, 0.1);
  border-top-color: currentColor;
  border-radius: 50%;
  animation: spin 0.6s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.add-slot-btn {
  width: 100%;
  background: #fff;
  color: #6b7280;
  border: 1px dashed #d1d5db;
  padding: 12px 16px;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 8px;
  justify-content: center;
  font-size: 14px;
}

.add-slot-btn:hover {
  background: #f9fafb;
  border-color: #9ca3af;
}

.action-buttons {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  padding-top: 20px;
  border-top: 1px solid #e5e7eb;
}

.cancel-btn {
  background: #fff;
  color: #374151;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 500;
  font-size: 14px;
}

.cancel-btn:hover {
  background: #f9fafb;
}

.create-btn {
  background: #2563eb;
  color: #fff;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 500;
  font-size: 14px;
}

.create-btn:hover {
  background: #1d4ed8;
}

.text-muted {
  color: #6b7280;
  font-size: 14px;
  margin-top: 4px;
}

.mb-20 {
  margin-bottom: 20px;
}

.mb-30 {
  margin-bottom: 30px;
}

.mt-20 {
  margin-top: 20px;
}

.mt-30 {
  margin-top: 30px;
}

h4 {
  font-size: 18px;
  font-weight: 600;
  margin: 0;
}

.input-wrapper label {
  display: block;
  font-size: 14px;
  font-weight: 500;
  color: #374151;
  margin-bottom: 6px;
}

.input-wrapper input,
.input-wrapper textarea {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 14px;
}

.input-wrapper input:focus,
.input-wrapper textarea:focus {
  outline: none;
  border-color: #2563eb;
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

.input-wrapper .invalid {
  border-color: #ef4444;
}

.input-wrapper .error {
  color: #ef4444;
  font-size: 12px;
  margin-top: 4px;
  display: block;
}

@media (max-width: 768px) {
  .input-grid,
  .input-grid-4 {
    grid-template-columns: 1fr;
  }
}

.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,.55);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 999;
}

.modal {
  background: white;
  padding: 28px;
  border-radius: 12px;
  max-width: 420px;
  width: 90%;
  text-align: center;
  box-shadow: 0 20px 40px rgba(0,0,0,.25);
  animation: pop .2s ease;
}

@keyframes pop {
  from { transform: scale(.9); opacity: 0; }
  to { transform: scale(1); opacity: 1; }
}

.modal h3 {
  color: #dc2626;
  font-size: 18px;
  margin-bottom: 12px;
}

.modal p {
  font-size: 14px;
  color: #374151;
  margin-bottom: 24px;
  line-height: 1.5;
}

.modal-actions {
  display: flex;
  justify-content: center;
  gap: 12px;
}

.btn-confirm {
  background: #dc2626;
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
}

.btn-confirm:hover {
  background: #b91c1c;
}

.btn-cancel {
  background: white;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  cursor: pointer;
}

.btn-cancel:hover {
  background: #f3f4f6;
}

.selected-states-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-top: 10px;
}

.state-tag {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: #e0e7ff;
  color: #3730a3;
  padding: 6px 10px;
  border-radius: 6px;
  font-size: 13px;
  font-weight: 500;
}

.remove-tag {
  background: none;
  border: none;
  color: #3730a3;
  font-size: 18px;
  line-height: 1;
  cursor: pointer;
  padding: 0;
  width: 18px;
  height: 18px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  transition: background 0.2s;
}

.remove-tag:hover {
  background: rgba(55, 48, 163, 0.1);
}

.mt-10 {
  margin-top: 10px;
}

.label-with-info {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  position: relative;
}

.info-btn {
  border: none;
  background: transparent;
  cursor: pointer;
  padding: 0;
  font-size: 14px;
  line-height: 1;
  opacity: 0.8;
}

.info-btn:hover {
  opacity: 1;
}

.info-popover {
  position: absolute;
  top: 28px;
  left: 0;
  z-index: 20;
  background: #111827;
  color: #fff;
  padding: 10px 12px;
  border-radius: 8px;
  font-size: 12px;
  max-width: 260px;
  box-shadow: 0 10px 25px rgba(0,0,0,.25);
}

.info-popover::before {
  content: "";
  position: absolute;
  top: -6px;
  left: 14px;
  border-left: 6px solid transparent;
  border-right: 6px solid transparent;
  border-bottom: 6px solid #111827;
}


.label-with-info {
  display: flex;
  align-items: center;
  gap: 6px;
  position: relative;
  margin-bottom: 6px;
  font-size: 14px;
  font-weight: 500;
  color: #374151;
  line-height: 1;
}

.info-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 14px;
  height: 14px;
  padding: 0;
  border: none;
  background: transparent;
  cursor: pointer;
  font-size: 12px;
  line-height: 14px;
  transform: translateY(-1px);
}
</style>