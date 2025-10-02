<template>
  <div class="contact-manager">
    <div class="header">
      <h1>Contact Manager</h1>
      <div class="controls">
        <input 
          v-model="searchQuery" 
          @input="searchContacts"
          type="text" 
          placeholder="Search contacts..." 
          class="search-input"
        />
        <button @click="showAddModal = true" class="btn btn-primary">
          Add Contact
        </button>
      </div>
    </div>

    <div class="contact-grid" v-if="!loading">
      <div 
        v-for="contact in contacts" 
        :key="contact.id" 
        class="contact-card"
      >
        <div class="contact-image">
          <img 
            :src="contact.image_url || '/images/default-avatar.png'" 
            :alt="contact.name"
            @error="handleImageError"
          />
        </div>
        <div class="contact-info">
          <h3>{{ contact.name }}</h3>
          <p class="email">{{ contact.email }}</p>
          <p class="phone" v-if="contact.phone">{{ contact.phone }}</p>
          <p class="address" v-if="contact.address">{{ contact.address }}</p>
          <p class="notes" v-if="contact.notes">{{ contact.notes }}</p>
        </div>
        <div class="contact-actions">
          <button @click="editContact(contact)" class="btn btn-sm btn-secondary">
            Edit
          </button>
          <button @click="deleteContact(contact.id)" class="btn btn-sm btn-danger">
            Delete
          </button>
        </div>
      </div>
    </div>

    <div v-if="loading" class="loading">
      <div class="spinner"></div>
      <p>Loading contacts...</p>
    </div>

    <div v-if="!loading && contacts.length === 0" class="empty-state">
      <p>No contacts found.</p>
    </div>

    <!-- Pagination -->
    <div v-if="pagination && pagination.last_page > 1" class="pagination">
      <button 
        @click="loadPage(pagination.current_page - 1)"
        :disabled="pagination.current_page <= 1"
        class="btn btn-sm"
      >
        Previous
      </button>
      <span class="page-info">
        Page {{ pagination.current_page }} of {{ pagination.last_page }}
      </span>
      <button 
        @click="loadPage(pagination.current_page + 1)"
        :disabled="pagination.current_page >= pagination.last_page"
        class="btn btn-sm"
      >
        Next
      </button>
    </div>

    <!-- Add/Edit Modal -->
    <div v-if="showAddModal || showEditModal" class="modal-overlay" @click="closeModal">
      <div class="modal" @click.stop>
        <div class="modal-header">
          <h2>{{ showAddModal ? 'Add Contact' : 'Edit Contact' }}</h2>
          <button @click="closeModal" class="close-btn">&times;</button>
        </div>
        <form @submit.prevent="saveContact" class="modal-body">
          <div class="form-group">
            <label for="name">Name *</label>
            <input 
              v-model="form.name" 
              type="text" 
              id="name" 
              required 
              class="form-control"
            />
          </div>
          <div class="form-group">
            <label for="email">Email *</label>
            <input 
              v-model="form.email" 
              type="email" 
              id="email" 
              required 
              class="form-control"
            />
          </div>
          <div class="form-group">
            <label for="phone">Phone</label>
            <input 
              v-model="form.phone" 
              type="text" 
              id="phone" 
              class="form-control"
            />
          </div>
          <div class="form-group">
            <label for="address">Address</label>
            <textarea 
              v-model="form.address" 
              id="address" 
              class="form-control"
              rows="3"
            ></textarea>
          </div>
          <div class="form-group">
            <label for="notes">Notes</label>
            <textarea 
              v-model="form.notes" 
              id="notes" 
              class="form-control"
              rows="3"
            ></textarea>
          </div>
          <div class="form-group">
            <label for="image">Image</label>
            <input 
              @change="handleImageUpload"
              type="file" 
              id="image" 
              accept="image/*"
              class="form-control"
            />
          </div>
          <div class="modal-footer">
            <button type="button" @click="closeModal" class="btn btn-secondary">
              Cancel
            </button>
            <button type="submit" class="btn btn-primary" :disabled="saving">
              {{ saving ? 'Saving...' : 'Save' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'ContactList',
  data() {
    return {
      contacts: [],
      loading: true,
      searchQuery: '',
      showAddModal: false,
      showEditModal: false,
      saving: false,
      pagination: null,
      form: {
        name: '',
        email: '',
        phone: '',
        address: '',
        notes: '',
        image: null
      },
      editingContact: null
    }
  },
  mounted() {
    this.loadContacts()
  },
  methods: {
    async loadContacts(page = 1) {
      this.loading = true
      try {
        const params = {
          page,
          per_page: 12
        }
        
        if (this.searchQuery) {
          params.search = this.searchQuery
        }

        const response = await axios.get('/api/contacts', { params })
        this.contacts = response.data.data.data
        this.pagination = response.data.data
      } catch (error) {
        console.error('Error loading contacts:', error)
        this.$emit('error', 'Failed to load contacts')
      } finally {
        this.loading = false
      }
    },
    
    async searchContacts() {
      // Debounce search
      clearTimeout(this.searchTimeout)
      this.searchTimeout = setTimeout(() => {
        this.loadContacts(1)
      }, 300)
    },
    
    async loadPage(page) {
      this.loadContacts(page)
    },
    
    editContact(contact) {
      this.editingContact = contact
      this.form = { ...contact }
      this.showEditModal = true
    },
    
    async saveContact() {
      this.saving = true
      try {
        const formData = new FormData()
        Object.keys(this.form).forEach(key => {
          if (this.form[key] !== null && this.form[key] !== '') {
            formData.append(key, this.form[key])
          }
        })

        if (this.showAddModal) {
          await axios.post('/api/contacts', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
          })
        } else {
          await axios.put(`/api/contacts/${this.editingContact.id}`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
          })
        }
        
        this.closeModal()
        this.loadContacts(this.pagination?.current_page || 1)
        this.$emit('success', 'Contact saved successfully')
      } catch (error) {
        console.error('Error saving contact:', error)
        this.$emit('error', 'Failed to save contact')
      } finally {
        this.saving = false
      }
    },
    
    async deleteContact(id) {
      if (confirm('Are you sure you want to delete this contact?')) {
        try {
          await axios.delete(`/api/contacts/${id}`)
          this.loadContacts(this.pagination?.current_page || 1)
          this.$emit('success', 'Contact deleted successfully')
        } catch (error) {
          console.error('Error deleting contact:', error)
          this.$emit('error', 'Failed to delete contact')
        }
      }
    },
    
    handleImageUpload(event) {
      this.form.image = event.target.files[0]
    },
    
    handleImageError(event) {
      event.target.src = '/images/default-avatar.png'
    },
    
    closeModal() {
      this.showAddModal = false
      this.showEditModal = false
      this.editingContact = null
      this.form = {
        name: '',
        email: '',
        phone: '',
        address: '',
        notes: '',
        image: null
      }
    }
  }
}
</script>

<style scoped>
.contact-manager {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 30px;
  flex-wrap: wrap;
  gap: 20px;
}

.header h1 {
  color: #333;
  margin: 0;
}

.controls {
  display: flex;
  gap: 15px;
  align-items: center;
}

.search-input {
  padding: 10px 15px;
  border: 2px solid #ddd;
  border-radius: 8px;
  font-size: 16px;
  min-width: 250px;
}

.search-input:focus {
  outline: none;
  border-color: #007bff;
}

.contact-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
  gap: 20px;
  margin-bottom: 30px;
}

.contact-card {
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  padding: 20px;
  transition: transform 0.2s, box-shadow 0.2s;
}

.contact-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 15px rgba(0, 0, 0, 0.15);
}

.contact-image {
  text-align: center;
  margin-bottom: 15px;
}

.contact-image img {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  object-fit: cover;
  border: 3px solid #f0f0f0;
}

.contact-info h3 {
  margin: 0 0 10px 0;
  color: #333;
  font-size: 1.2em;
}

.contact-info p {
  margin: 5px 0;
  color: #666;
  font-size: 0.9em;
}

.contact-info .email {
  color: #007bff;
  font-weight: 500;
}

.contact-actions {
  margin-top: 15px;
  display: flex;
  gap: 10px;
}

.btn {
  padding: 8px 16px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 14px;
  transition: background-color 0.2s;
}

.btn-primary {
  background-color: #007bff;
  color: white;
}

.btn-primary:hover {
  background-color: #0056b3;
}

.btn-secondary {
  background-color: #6c757d;
  color: white;
}

.btn-secondary:hover {
  background-color: #545b62;
}

.btn-danger {
  background-color: #dc3545;
  color: white;
}

.btn-danger:hover {
  background-color: #c82333;
}

.btn-sm {
  padding: 6px 12px;
  font-size: 12px;
}

.loading, .empty-state {
  text-align: center;
  padding: 40px;
  color: #666;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 4px solid #f3f3f3;
  border-top: 4px solid #007bff;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 20px;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 15px;
  margin-top: 30px;
}

.page-info {
  color: #666;
  font-size: 14px;
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal {
  background: white;
  border-radius: 12px;
  width: 90%;
  max-width: 500px;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px;
  border-bottom: 1px solid #eee;
}

.modal-header h2 {
  margin: 0;
  color: #333;
}

.close-btn {
  background: none;
  border: none;
  font-size: 24px;
  cursor: pointer;
  color: #666;
}

.modal-body {
  padding: 20px;
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  margin-bottom: 5px;
  font-weight: 500;
  color: #333;
}

.form-control {
  width: 100%;
  padding: 10px;
  border: 2px solid #ddd;
  border-radius: 6px;
  font-size: 14px;
}

.form-control:focus {
  outline: none;
  border-color: #007bff;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  padding: 20px;
  border-top: 1px solid #eee;
}

@media (max-width: 768px) {
  .header {
    flex-direction: column;
    align-items: stretch;
  }
  
  .controls {
    flex-direction: column;
  }
  
  .search-input {
    min-width: auto;
  }
  
  .contact-grid {
    grid-template-columns: 1fr;
  }
}
</style>
