<template>
  <div class="page-container">
    <div v-if="defaultDeliveryType" class="card default-card">
      <div class="card-header">
        <div class="header-content">
          <h2 class="page-title">Default Delivery Type</h2>
          <span class="badge-default">DEFAULT</span>
        </div>
      </div>
      <div class="default-content">
        <div class="default-info">
          <div class="info-item">
            <span class="info-label">Type Name</span>
            <span class="info-value">{{ defaultDeliveryType.type_name }}</span>
          </div>
        </div>
        <div class="default-actions">
          <button class="btn-action primary" @click="$router.push('/shipping-rules/' + defaultDeliveryType.id)">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
              <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
            </svg>
            Edit Default
          </button>

          <button class="btn-action delete" @click="deleteItem(defaultDeliveryType.id, true)">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
              <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
            </svg>
            Delete Default
          </button>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header">
        <div class="header-content">
          <h2 class="page-title">Last State-Specific Delivery Types</h2>
          <span class="count-badge">{{ stateDeliveryTypes.length }}</span>
        </div>
        <button class="btn-new" @click="$router.push('/shipping-rules/create')">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="12" y1="5" x2="12" y2="19"></line>
            <line x1="5" y1="12" x2="19" y2="12"></line>
          </svg>
          Add New Delivery Type
        </button>
      </div>

      <div class="table-container">
        <table class="modern-table">
          <thead>
          <tr>
            <th>
              <div class="th-content">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                  <circle cx="12" cy="10" r="3"></circle>
                </svg>
                State
              </div>
            </th>
            <th>
              <div class="th-content">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                  <circle cx="8.5" cy="7" r="4"></circle>
                  <polyline points="17 11 19 13 23 9"></polyline>
                </svg>
                Last Delivery Type
              </div>
            </th>
            <th class="text-center">
              <div class="th-content center">
                Actions
              </div>
            </th>
          </tr>
          </thead>
          <tbody>
          <tr v-for="item in stateDeliveryTypes" :key="item.id" class="table-row">
            <td>
              <div class="state-badge">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                  <circle cx="12" cy="10" r="3"></circle>
                </svg>
                {{ item.state || '-' }}
              </div>
            </td>
            <td>
              <span class="type-name">{{ item.type_name }}</span>
            </td>
            <td>
              <div class="actions">
                <button class="btn-action-icon view" @click="handleView(item)" title="View Details">
                 View
                </button>
              </div>
            </td>
          </tr>
          </tbody>
        </table>

        <div v-if="!loading && stateDeliveryTypes.length === 0" class="empty-state">
          <div class="empty-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
              <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
              <circle cx="12" cy="10" r="3"></circle>
            </svg>
          </div>
          <h3>No state-specific delivery types</h3>
        </div>

        <div v-if="loading" class="loading-state">
          <div class="spinner"></div>
          <p>Loading delivery types...</p>
        </div>
      </div>

      <div v-if="pagination && pagination.data && pagination.data.length > 0" class="pagination">
        <div class="pagination-info">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
            <polyline points="14 2 14 8 20 8"></polyline>
          </svg>
          Showing {{ pagination.from }}-{{ pagination.to }} of {{ pagination.total }} entries
        </div>
        <div class="pagination-buttons">
          <button
              v-for="(link, index) in pagination.links"
              :key="index"
              :disabled="!link.url || link.active"
              @click="fetchData(link.url)"
              class="page-btn"
              :class="{ 'active': link.active, 'disabled': !link.url }"
              v-html="link.label"
          ></button>
        </div>
      </div>
    </div>
  </div>

  <div v-if="showDeleteWarning" class="modal-overlay">
    <div class="modal">
      <h3>Warning!</h3>
      <p>You are about to delete the <strong>default delivery type</strong>. After this, you will not have any default delivery type. Are you sure?</p>
      <div class="modal-actions">
        <button class="btn-action primary" @click="confirmDelete">Yes, Delete</button>
        <button class="btn-action" @click="cancelDelete">Cancel</button>
      </div>
    </div>
  </div>

</template>

<script>
import Service from "~/services/service";
import {useAuthStore} from "~/store/auth.js";

export default {
  data() {
    return {
      deliveryTypes: [],
      pagination: null,
      loading: false,
      showDeleteWarning: false,
      deleteId: null,
      deleteIsDefault: false
    }
  },
  computed: {
    defaultDeliveryType() {
      return this.deliveryTypes.find(item => item.is_default === 1);
    },
    stateDeliveryTypes() {
      return this.deliveryTypes.filter(item => item.is_default !== 1);
    }
  },
  async mounted() {
    const authStore = useAuthStore();
    this.token = authStore.token;

    await this.fetchData();
  },
  methods: {
    async fetchData(url = null) {
      this.loading = true;
      try {
        let params = null;
        if (url) {
          try {
            const urlObj = new URL(url);
            params = Object.fromEntries(urlObj.searchParams.entries());
          } catch (e) {
            if (url.includes('?')) {
              const queryString = url.split('?')[1];
              params = Object.fromEntries(new URLSearchParams(queryString));
            }
          }
        }

        const response = await Service.getRequest(params, this.token, 'getDeliveryTypes');

        if (response && response.data) {
          const apiData = response.data;
          if (apiData.data && Array.isArray(apiData.data.data)) {
            this.deliveryTypes = apiData.data.data;
            this.pagination = apiData.data;
          } else if (apiData.data && Array.isArray(apiData.data)) {
            this.deliveryTypes = apiData.data;
            this.pagination = apiData;
          } else {
            this.deliveryTypes = [];
            this.pagination = null;
          }
        } else {
          this.deliveryTypes = [];
          this.pagination = null;
        }
      } catch (e) {
        console.error('Error fetching delivery types:', e);
        this.deliveryTypes = [];
        this.pagination = null;
      } finally {
        this.loading = false;
      }
    },

    handleView(item) {
      if (item.is_default) {
        this.$router.push('/shipping-rules/' + item.id);
      } else {
        this.$router.push(`/shipping-rules/${item.state}-index`);
      }
    },

    async deleteItem(id, isDefault = false) {
      if (isDefault) {
        this.deleteId = id;
        this.deleteIsDefault = true;
        this.showDeleteWarning = true;
        return;
      }

      if(!confirm('Are you sure you want to delete this delivery type?')) return;
      await this.performDelete(id);
    },

    async confirmDelete() {
      if (this.deleteId) {
        await this.performDelete(this.deleteId);
        this.showDeleteWarning = false;
        this.deleteId = null;
        this.deleteIsDefault = false;
      }
    },

    cancelDelete() {
      this.showDeleteWarning = false;
      this.deleteId = null;
      this.deleteIsDefault = false;
    },

    async performDelete(id) {
      this.loading = true;
      try {
        await Service.deleteData(id, this.token, 'deleteDeliveryType', null, id);
        await this.fetchData();
      } catch (e) {
        console.error('Error deleting:', e);
        alert(`Failed to delete - ${e}`);
      } finally {
        this.loading = false;
      }
    }
  }
}
</script>

<style scoped>
.page-container {
  padding: 24px;
  background: #f8fafc;
  min-height: 100vh;
}

.default-card {
  margin-bottom: 24px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border: none;
  box-shadow: 0 10px 25px rgba(102, 126, 234, 0.2);
}

.default-card .card-header {
  border-bottom: 1px solid rgba(255, 255, 255, 0.2);
}

.default-card .page-title {
  font-size: 16px;
  font-weight: 600;
}

.default-content {
  padding: 24px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 24px;
}

.default-info {
  display: flex;
  gap: 48px;
  flex: 1;
}

.info-item {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.info-label {
  font-size: 12px;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.info-value {
  font-size: 16px;
  font-weight: 600;
}

.default-actions {
  display: flex;
  gap: 12px;
}

.card {
  background: white;
  border-radius: 12px;
  border: 1px solid #e5e7eb;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
  overflow: hidden;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 24px;
  border-bottom: 1px solid #e5e7eb;
  background: #fafbfc;
}

.header-content {
  display: flex;
  align-items: center;
  gap: 12px;
}

.page-title {
  font-size: 18px;
  font-weight: 700;
  color: #111827;
  margin: 0;
}

.count-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 24px;
  height: 24px;
  padding: 0 8px;
  background: #e0e7ff;
  color: #4338ca;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 700;
}

.badge-default {
  display: inline-flex;
  align-items: center;
  padding: 4px 12px;
  background: rgba(251, 191, 36, 0.9);
  color: white;
  border-radius: 6px;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.5px;
}

.btn-new {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 20px;
  background: #3b82f6;
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);
}

.btn-new:hover {
  background: #2563eb;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
}

.btn-new:active {
  transform: translateY(0);
}

.modern-table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
}

.modern-table thead {
  background: linear-gradient(to bottom, #f9fafb, #f3f4f6);
}

.modern-table th {
  padding: 16px 24px;
  text-align: left;
  font-size: 12px;
  font-weight: 700;
  color: #374151;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  border-bottom: 2px solid #e5e7eb;
  position: sticky;
  top: 0;
  background: #f9fafb;
  z-index: 10;
}

.th-content {
  display: flex;
  align-items: center;
  gap: 8px;
}

.th-content.center {
  justify-content: center;
}

.modern-table th.text-center {
  text-align: center;
}

.table-row {
  border-bottom: 1px solid #f3f4f6;
  transition: all 0.2s;
}

.table-row:hover {
  background: #f9fafb;
  transform: scale(1.001);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}

.table-row:last-child {
  border-bottom: none;
}

.modern-table td {
  padding: 16px 24px;
  font-size: 14px;
  color: #374151;
  vertical-align: middle;
}

.state-badge {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 6px 16px;
  background: #eff6ff;
  color: #1e40af;
  border-radius: 8px;
  font-weight: 600;
  font-size: 13px;
  border: 1px solid #dbeafe;
}

.type-name {
  font-weight: 600;
  color: #111827;
  font-size: 14px;
}

.actions {
  display: flex;
  gap: 4px;
  justify-content: center;
  align-items: center;
}

.btn-action {
  display: flex;
  align-items: center;
  padding: 10px 20px;
  border: none;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-action.primary {
  color: #667eea;
}

.btn-action.delete {
  color: #f30f0f;
}

.btn-action.primary:hover {
  border-color: white;
  transform: translateY(-1px);
}

.btn-action-icon {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  border: none;
  background: transparent;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s;
  opacity: 0.6;
}

.btn-action-icon .tooltip {
  position: absolute;
  bottom: calc(100% + 8px);
  left: 50%;
  transform: translateX(-50%);
  padding: 6px 10px;
  background: #1f2937;
  font-size: 11px;
  font-weight: 500;
  border-radius: 4px;
  white-space: nowrap;
  opacity: 0;
  pointer-events: none;
  transition: all 0.2s;
}

.btn-action-icon .tooltip::after {
  content: '';
  position: absolute;
  top: 100%;
  left: 50%;
  transform: translateX(-50%);
  border: 4px solid transparent;
  border-top-color: #1f2937;
}

.btn-action-icon:hover .tooltip {
  opacity: 1;
  transform: translateX(-50%) translateY(-2px);
}

.btn-action-icon:hover {
  opacity: 1;
  background: rgba(0, 0, 0, 0.04);
}

.btn-action-icon:active {
  transform: scale(0.95);
}

.btn-action-icon.view {
  color: #3b82f6;
}

.btn-action-icon.view:hover {
  background: #eff6ff;
}

.btn-action-icon.edit {
  color: #10b981;
}

.btn-action-icon.edit:hover {
  background: #d1fae5;
}

.btn-action-icon.delete {
  color: #ef4444;
}

.btn-action-icon.delete:hover {
  background: #fee2e2;
}

/* Empty State */
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 80px 20px;
  color: #6b7280;
}

.empty-icon {
  width: 96px;
  height: 96px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f3f4f6;
  border-radius: 50%;
  margin-bottom: 24px;
}

.empty-icon svg {
  color: #9ca3af;
}

.empty-state h3 {
  margin: 0 0 8px 0;
  font-size: 18px;
  font-weight: 600;
  color: #374151;
}

.empty-state p {
  margin: 0;
  font-size: 14px;
  color: #6b7280;
  max-width: 400px;
  text-align: center;
}

.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 16px;
  padding: 80px 20px;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 4px solid #e5e7eb;
  border-top-color: #3b82f6;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

.loading-state p {
  margin: 0;
  font-size: 14px;
  color: #6b7280;
  font-weight: 500;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.pagination {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 24px;
  border-top: 1px solid #e5e7eb;
  background: #fafbfc;
}

.pagination-info {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 13px;
  color: #6b7280;
  font-weight: 500;
}

.pagination-info svg {
  color: #9ca3af;
}

.pagination-buttons {
  display: flex;
  gap: 6px;
}

.page-btn {
  min-width: 36px;
  height: 36px;
  padding: 0 12px;
  border: 1px solid #d1d5db;
  background: white;
  color: #6b7280;
  border-radius: 6px;
  font-size: 13px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.15s;
}

.page-btn:hover:not(.disabled):not(.active) {
  background: #f3f4f6;
  border-color: #9ca3af;
  transform: translateY(-1px);
}

.page-btn.active {
  background: #3b82f6;
  color: white;
  border-color: #3b82f6;
  box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);
}

.page-btn.disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

@media (max-width: 768px) {
  .page-container {
    padding: 16px;
  }

  .card-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 16px;
  }

  .btn-new {
    width: 100%;
    justify-content: center;
  }

  .default-content {
    flex-direction: column;
    align-items: stretch;
  }

  .default-info {
    flex-direction: column;
    gap: 20px;
  }

  .default-actions {
    width: 100%;
  }

  .btn-action.primary {
    width: 100%;
    justify-content: center;
  }

  .modern-table th,
  .modern-table td {
    padding: 12px 16px;
  }

  .pagination {
    flex-direction: column;
    gap: 16px;
    align-items: stretch;
  }

  .pagination-buttons {
    justify-content: center;
    flex-wrap: wrap;
  }
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(31, 41, 55, 0.6);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal {
  background: white;
  padding: 24px 32px;
  border-radius: 12px;
  max-width: 400px;
  width: 90%;
  text-align: center;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
}

.modal h3 {
  font-size: 18px;
  font-weight: 700;
  margin-bottom: 16px;
  color: #b91c1c;
}

.modal p {
  font-size: 14px;
  color: #374151;
  margin-bottom: 24px;
}

.modal-actions {
  display: flex;
  justify-content: center;
  gap: 12px;
}

.modal-actions .btn-action.primary {
  background: #b91c1c;
  color: white;
}

.modal-actions .btn-action.primary:hover {
  background: #991b1b;
}

.modal-actions .btn-action {
  border: 1px solid #d1d5db;
  background: white;
  color: #374151;
}

.modal-actions .btn-action:hover {
  background: #f3f4f6;
}
</style>