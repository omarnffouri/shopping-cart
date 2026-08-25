<template>
  <div class="page-container">
    <button class="btn-back" @click="$router.back()">
      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <line x1="19" y1="12" x2="5" y2="12"></line>
        <polyline points="12 19 5 12 12 5"></polyline>
      </svg>
      Back
    </button>

    <div v-if="!loading && deliveryTypes.length > 0">
      <div v-for="type in deliveryTypes" :key="type.id" class="delivery-card">
        <div class="card-header">
          <div class="header-left">
            <h2>{{ type.type_name }}</h2>
            <span class="type-code">{{ type.type_code }}</span>
          </div>
          <div class="header-right">
            <span class="price">{{ type.price }} {{ type.currency }}</span>
            <div class="action-buttons">
              <button class="btn-edit" @click="editDeliveryType(type.id)">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                  <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                </svg>
                Edit
              </button>

              <button class="btn-delete" @click="deleteItem(type.id)" title="Delete">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <polyline points="3 6 5 6 21 6"></polyline>
                  <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                  <line x1="10" y1="11" x2="10" y2="17"></line>
                  <line x1="14" y1="11" x2="14" y2="17"></line>
                </svg>
              </button>
            </div>
          </div>
        </div>

        <div class="card-body">
          <div class="info-grid">
            <div class="info-item">
              <span class="label">Min Hours Advance</span>
              <span class="value">{{ type.min_hours_advance }} hours</span>
            </div>
            <div class="info-item">
              <span class="label">Cutoff Time</span>
              <span class="value">{{ type.cutoff_time }}</span>
            </div>
            <div class="info-item">
              <span class="label">Available Today</span>
              <span class="value">{{ type.available_for_today ? 'Yes' : 'No' }}</span>
            </div>
          </div>

          <div class="time-slots" v-if="type.time_slots && type.time_slots.length > 0">
            <h3>Time Slots</h3>
            <div class="slots-grid">
              <div v-for="slot in type.time_slots" :key="slot.id" class="slot">
                <div class="slot-name">{{ slot.slot_name }}</div>
                <div class="slot-time">{{ slot.start_time }} - {{ slot.end_time }}</div>
                <div v-if="slot.available_if_before" class="slot-note">
                  Order before {{ slot.available_if_before }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-if="!loading && deliveryTypes.length === 0" class="empty-state">
      <p>No delivery types found for {{ state }}</p>
    </div>

    <div v-if="loading" class="loading-state">
      <div class="spinner"></div>
    </div>
  </div>

  <div v-if="showDeleteWarning" class="modal-overlay">
    <div class="modal">
      <h3>Warning!</h3>

      <p>
        You are about to delete this delivery type for
        <strong>{{ state }}</strong>.
        This action cannot be undone.
      </p>

      <div class="modal-actions">
        <button class="btn-confirm" @click="confirmDelete">
          Yes, Delete
        </button>

        <button class="btn-cancel" @click="cancelDelete">
          Cancel
        </button>
      </div>
    </div>
  </div>

</template>

<script>
import Service from "~/services/service";
import { useAuthStore } from "~/store/auth.js";

export default {
  data() {
    return {
      state: '',
      deliveryTypes: [],
      loading: false,

      showDeleteWarning: false,
      deleteId: null,
    }
  },
  async mounted() {
    const authStore = useAuthStore();
    this.token = authStore.token;

    // Get state from route parameter
    this.state = this.$route.params.code;

    await this.fetchDeliveryTypes();
  },
  methods: {
    async fetchDeliveryTypes() {
      this.loading = true;
      try {
        const response = await Service.getRequest(
            null,
            this.token,
            'getDeliveryTypesByState',
            null,
            this.state
        );

        if (response && response.data) {
          this.deliveryTypes = response.data;
        } else {
          this.deliveryTypes = [];
        }
      } catch (e) {
        console.error('Error fetching delivery types:', e);
        this.deliveryTypes = [];
      } finally {
        this.loading = false;
      }
    },
    editDeliveryType(id) {
      this.$router.push(`/shipping-rules/${id}`);
    },

    deleteItem(id) {
      this.deleteId = id;
      this.showDeleteWarning = true;
    },

    cancelDelete() {
      this.showDeleteWarning = false;
      this.deleteId = null;
    },

    async confirmDelete() {
      if (!this.deleteId) return;

      this.loading = true;

      try {
        await Service.deleteData(this.deleteId, this.token, 'deleteDeliveryType', null, this.deleteId);

        this.deliveryTypes = this.deliveryTypes.filter(item => item.id !== this.deleteId);

        if (this.deliveryTypes.length === 0) {
          this.$router.push('/shipping-rules');
        }

      } catch (e) {
        console.error('Error deleting:', e);
        alert(`Failed to delete - ${e}`);
      } finally {
        this.loading = false;
        this.showDeleteWarning = false;
        this.deleteId = null;
      }
    },

  }
}
</script>

<style scoped>
.page-container {
  min-height: 100vh;
  background: #f8f9fa;
}

.btn-back {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 8px 16px;
  background: white;
  color: #495057;
  border: 1px solid #dee2e6;
  border-radius: 6px;
  font-size: 14px;
  cursor: pointer;
  margin-bottom: 12px;
}

.btn-back:hover {
  background: #f8f9fa;
}

.delivery-card {
  background: white;
  border: 1px solid #e9ecef;
  border-radius: 8px;
  margin-bottom: 20px;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 24px;
  border-bottom: 1px solid #e9ecef;
}

.header-left {
  display: flex;
  align-items: center;
  gap: 12px;
}

.header-left h2 {
  font-size: 18px;
  font-weight: 600;
  color: #212529;
  margin: 0;
}

.type-code {
  padding: 4px 10px;
  background: #e9ecef;
  border-radius: 4px;
  font-size: 13px;
  color: #495057;
}

.card-body {
  padding: 24px;
}

.info-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
  margin-bottom: 24px;
}

.info-item {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.label {
  font-size: 13px;
  color: #6c757d;
}

.value {
  font-size: 15px;
  font-weight: 500;
  color: #212529;
}

.time-slots h3 {
  font-size: 16px;
  font-weight: 600;
  color: #212529;
  margin: 0 0 16px 0;
}

.slots-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
  gap: 12px;
}

.slot {
  padding: 16px;
  background: #f8f9fa;
  border: 1px solid #e9ecef;
  border-radius: 6px;
}

.slot-name {
  font-size: 14px;
  font-weight: 600;
  color: #212529;
  margin-bottom: 6px;
}

.slot-time {
  font-size: 14px;
  color: #495057;
  margin-bottom: 8px;
}

.slot-note {
  font-size: 12px;
  color: #6c757d;
}

.empty-state {
  padding: 60px 32px;
  text-align: center;
  color: #6c757d;
}

.loading-state {
  display: flex;
  justify-content: center;
  padding: 60px 32px;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 3px solid #e9ecef;
  border-top-color: #0d6efd;
  border-radius: 50%;
  animation: spin 0.6s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.header-right {
  display: flex;
  align-items: center;
  gap: 20px;
}

.action-buttons {
  display: flex;
  align-items: center;
  gap: 8px;
}

.price {
  font-size: 20px;
  font-weight: 600;
  color: #0d6efd;
}

.btn-edit {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 8px 16px;
  background: #0d6efd;
  color: white;
  border: none;
  border-radius: 6px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-edit:hover {
  background: #0b5ed7;
  transform: translateY(-1px);
  box-shadow: 0 2px 8px rgba(13, 110, 253, 0.3);
}

.btn-delete {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 8px;
  background: white;
  color: #dc3545;
  border: 1px solid #dc3545;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-delete:hover {
  background: #dc3545;
  color: white;
  transform: translateY(-1px);
  box-shadow: 0 2px 8px rgba(220, 53, 69, 0.3);
}

@media (max-width: 768px) {
  .card-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 16px;
  }

  .header-right {
    width: 100%;
    justify-content: space-between;
  }

  .action-buttons {
    gap: 8px;
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
</style>