/* M&E Dashboard JavaScript Functionality */

/**
 * Show/Hide Pages
 */
function showPage(pageName) {
  // Hide all pages
  const pages = document.querySelectorAll('.me-page');
  pages.forEach(page => page.classList.remove('active'));

  // Show selected page
  const selectedPage = document.getElementById(pageName + '-page');
  if (selectedPage) {
    selectedPage.classList.add('active');
  }

  // Update active tab
  const tabs = document.querySelectorAll('.me-tab');
  tabs.forEach(tab => tab.classList.remove('active'));
  event.target.closest('.me-tab').classList.add('active');
}

/**
 * Dashboard Filters
 */
function applyDashboardFilter() {
  const startDate = document.getElementById('start-date').value;
  const endDate = document.getElementById('end-date').value;

  console.log('Applying dashboard filter:', { startDate, endDate });
  alert('Dashboard filters applied successfully!');
}

function resetDashboardFilter() {
  document.getElementById('start-date').value = '';
  document.getElementById('end-date').value = '';
  console.log('Dashboard filters reset');
}

/**
 * Fuel Inventory Functions
 */

// Show add fuel modal/form
function showAddFuelModal() {
  const recordId = document.getElementById('fuel-record-id');
  const formTitle = document.getElementById('inventory-form-title');

  if (recordId) recordId.value = '';
  if (formTitle) formTitle.textContent = 'Add New Fuel Record';

  // Scroll to form
  const form = document.getElementById('inventory-form');
  if (form) {
    form.scrollIntoView({ behavior: 'smooth' });
    form.reset();
  }
}

// Clear inventory form
function clearInventoryForm() {
  const form = document.getElementById('inventory-form');
  if (form) {
    form.reset();
  }
  const recordId = document.getElementById('fuel-record-id');
  if (recordId) recordId.value = '';
}

// Edit fuel record
function editFuelRecord(id) {
  console.log('Editing fuel record:', id);
  
  // In a real scenario, you would fetch the record data via AJAX
  // For now, we'll show a simple alert
  alert('Edit fuel record ' + id + ' functionality coming soon. Load record data via AJAX from server.');

  // Scroll to form
  const form = document.getElementById('inventory-form');
  if (form) {
    form.scrollIntoView({ behavior: 'smooth' });
  }
}

// Delete fuel record
function deleteFuelRecord(id) {
  if (confirm('Are you sure you want to delete this fuel record?')) {
    console.log('Deleting fuel record:', id);
    alert('Fuel record ' + id + ' deleted successfully!');
    // In production, send AJAX request to delete from server
  }
}

// Export inventory
function exportInventory() {
  alert('Export inventory to CSV/Excel functionality coming soon!');
  console.log('Exporting inventory data...');
}

// Apply inventory filters
function applyInventoryFilter() {
  const typeFilter = document.getElementById('inventory-type-filter').value;
  const startDate = document.getElementById('inventory-start-date').value;
  const endDate = document.getElementById('inventory-end-date').value;

  console.log('Applying inventory filter:', { typeFilter, startDate, endDate });
  alert('Inventory filters applied successfully!');
}

// Reset inventory filters
function resetInventoryFilter() {
  document.getElementById('inventory-type-filter').value = '';
  document.getElementById('inventory-start-date').value = '';
  document.getElementById('inventory-end-date').value = '';
  console.log('Inventory filters reset');
}

/**
 * Equipment Functions
 */

// Clear equipment form
function clearEquipmentForm() {
  const form = document.getElementById('equipment-form');
  if (form) {
    form.reset();
  }
  document.getElementById('equipment-id').value = '';
}

// Edit equipment
function editEquipment(id) {
  console.log('Editing equipment:', id);
  alert('Edit equipment ' + id + ' functionality coming soon. Load record data via AJAX from server.');

  // Scroll to form
  const form = document.getElementById('equipment-form');
  if (form) {
    form.scrollIntoView({ behavior: 'smooth' });
  }
}

// Delete equipment
function deleteEquipment(id) {
  if (confirm('Are you sure you want to delete this equipment?')) {
    console.log('Deleting equipment:', id);
    alert('Equipment ' + id + ' deleted successfully!');
    // In production, send AJAX request to delete from server
  }
}

// Filter equipment
function filterEquipment() {
  const searchTerm = document.getElementById('search-equipment').value.toLowerCase();
  const tableRows = document.querySelectorAll('#equipment-tbody tr');

  tableRows.forEach(row => {
    const text = row.textContent.toLowerCase();
    row.style.display = text.includes(searchTerm) ? '' : 'none';
  });
}

/**
 * Form Submission Handlers
 */

// Inventory Form Submission
const inventoryForm = document.getElementById('inventory-form');
if (inventoryForm) {
  inventoryForm.addEventListener('submit', function(e) {
    e.preventDefault();

    // Get form data
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);

    console.log('Submitting inventory form:', data);

    // In production, send via AJAX to server
    alert('Fuel record saved successfully!');

    // Reset form
    this.reset();
  });
}

// Equipment Form Submission
const equipmentForm = document.getElementById('equipment-form');
if (equipmentForm) {
  equipmentForm.addEventListener('submit', function(e) {
    e.preventDefault();

    // Get form data
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);

    console.log('Submitting equipment form:', data);

    // In production, send via AJAX to server
    alert('Equipment saved successfully!');

    // Reset form
    clearEquipmentForm();
  });
}

/**
 * Initialize Charts (if Chart.js is available)
 */
document.addEventListener('DOMContentLoaded', function() {
  // This will be populated when you add Chart.js library
  console.log('M&E Dashboard initialized');

  // Initialize any tooltips or popovers
  initializeTooltips();
});

/**
 * Initialize Bootstrap Tooltips
 */
function initializeTooltips() {
  const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  tooltipTriggerList.map(function(tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });
}

/**
 * Utility: Format Date
 */
function formatDate(dateString) {
  const options = { year: 'numeric', month: 'short', day: 'numeric' };
  return new Date(dateString).toLocaleDateString('en-US', options);
}

/**
 * Utility: Format Currency
 */
function formatCurrency(amount) {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD'
  }).format(amount);
}

/**
 * AJAX Helper Function
 */
function sendAjaxRequest(action, data) {
  const formData = new FormData();
  formData.append('action', action);

  // Append all data fields
  for (const [key, value] of Object.entries(data)) {
    formData.append(key, value);
  }

  return fetch('', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(data => {
    console.log('AJAX Response:', data);
    return data;
  })
  .catch(error => {
    console.error('AJAX Error:', error);
    alert('An error occurred. Please try again.');
  });
}

/**
 * Example: Load Dashboard Data via AJAX
 */
function loadDashboardData(filters = {}) {
  sendAjaxRequest('get_dashboard_data', filters).then(response => {
    if (response) {
      console.log('Dashboard updated with new data');
      // Update dashboard elements with response data
    }
  });
}
