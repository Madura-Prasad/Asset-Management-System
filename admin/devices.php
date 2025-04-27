<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

if (!isLoggedIn() || !isAdmin()) {
    header("Location: " . APP_URL . "/index.php");
    exit;
}

$pageTitle = "Manage Devices";
require_once '../includes/header.php';

// Get all devices initially
$devices = getDevices(false);
$deviceTypes = getDeviceTypes();
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Manage Devices</h1>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#deviceModal" data-action="add">
            Add New Device
        </button>
    </div>
    
    <!-- Filter Card -->
    <div class="card mb-4">
        <div class="card-body">
            <form id="filterForm" class="row g-3">
                <div class="col-md-4">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" class="form-control" id="search" placeholder="Search by name or asset no">
                </div>
                
                <div class="col-md-3">
                    <label for="typeFilter" class="form-label">Device Type</label>
                    <select class="form-select" id="typeFilter">
                        <option value="">All Types</option>
                        <?php foreach ($deviceTypes as $type): ?>
                            <option value="<?php echo $type['id']; ?>">
                                <?php echo htmlspecialchars($type['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label for="availabilityFilter" class="form-label">Availability</label>
                    <select class="form-select" id="availabilityFilter">
                        <option value="">All</option>
                        <option value="available">Available</option>
                        <option value="assigned">Assigned</option>
                    </select>
                </div>
                
                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" id="resetFilters" class="btn btn-outline-secondary">Reset</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Results Counter -->
    <div id="resultCount" class="text-muted mb-3">Showing <?php echo count($devices); ?> devices</div>
    
    <!-- Devices Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Asset No</th>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Brand</th>
                            <th>Specs</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="devicesTableBody">
                        <?php foreach ($devices as $device): ?>
                            <tr data-type-id="<?php echo $device['product_type_id'] ?? ''; ?>" 
                                data-status="<?php echo $device['is_available'] ? 'available' : 'assigned'; ?>"
                                data-search="<?php echo htmlspecialchars(strtolower($device['asset_no'] . ' ' . $device['name'])); ?>">
                                <td><?php echo htmlspecialchars($device['asset_no']); ?></td>
                                <td><?php echo htmlspecialchars($device['name']); ?></td>
                                <td><?php echo htmlspecialchars($device['type_name'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($device['brand'] ?? 'N/A'); ?></td>
                                <td>
    <?php if ($device['cpu'] || $device['ram'] || $device['storage']): ?>
        <?php echo htmlspecialchars(implode(', ', array_filter(array($device['cpu'], $device['ram'], $device['storage'])))); ?>
    <?php else: ?>
        N/A
    <?php endif; ?>
</td>


                                <td>
                                    <?php if ($device['is_available']): ?>
                                        <span class="badge bg-success">Available</span>
                                    <?php else: ?>
                                        <span class="badge bg-primary">Assigned</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary edit-btn" 
                                                data-id="<?php echo $device['id']; ?>"
                                                title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <?php if (!$device['is_available']): ?>
                                            <button class="btn btn-outline-warning unassign-btn" 
                                                    data-id="<?php echo $device['id']; ?>"
                                                    title="Unassign">
                                                <i class="bi bi-person-x"></i>
                                            </button>
                                        <?php endif; ?>
                                        <button class="btn btn-outline-danger delete-btn" 
                                                data-id="<?php echo $device['id']; ?>"
                                                title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Device Modal -->
<div class="modal fade" id="deviceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Add New Device</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="deviceForm" method="post">
                <input type="hidden" id="deviceId" name="id" value="">
                <input type="hidden" name="action" id="formAction" value="add">
                
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="asset_no" class="form-label">Asset Number*</label>
                            <input type="text" class="form-control" id="asset_no" name="asset_no" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="serial_no" class="form-label">Serial Number*</label>
                            <input type="text" class="form-control" id="serial_no" name="serial_no" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="name" class="form-label">Device Name*</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="brand" class="form-label">Brand</label>
                            <input type="text" class="form-control" id="brand" name="brand">
                        </div>
                        
                        <div class="col-md-6">
                            <label for="product_type_id" class="form-label">Device Type</label>
                            <select class="form-select" id="product_type_id" name="product_type_id">
                                <option value="">Select Type</option>
                                <?php foreach ($deviceTypes as $type): ?>
                                    <option value="<?php echo $type['id']; ?>">
                                        <?php echo htmlspecialchars($type['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Availability</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_available" name="is_available" checked>
                                <label class="form-check-label" for="is_available">Available</label>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <label for="cpu" class="form-label">CPU</label>
                            <input type="text" class="form-control" id="cpu" name="cpu">
                        </div>
                        
                        <div class="col-md-4">
                            <label for="ram" class="form-label">RAM</label>
                            <input type="text" class="form-control" id="ram" name="ram">
                        </div>
                        
                        <div class="col-md-4">
                            <label for="storage" class="form-label">Storage</label>
                            <input type="text" class="form-control" id="storage" name="storage">
                        </div>
                        
                        <div class="col-12">
                            <label for="specifications" class="form-label">Specifications</label>
                            <textarea class="form-control" id="specifications" name="specifications" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Device</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmModalTitle">Confirm Action</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="confirmModalBody">
                Are you sure you want to perform this action?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmAction">Confirm</button>
            </div>
        </div>
    </div>
</div>

<!-- Loading Indicator -->
<div id="loadingIndicator" class="d-none" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; display: flex; justify-content: center; align-items: center;">
    <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Filtering functionality
    const searchInput = document.getElementById('search');
    const typeFilter = document.getElementById('typeFilter');
    const availabilityFilter = document.getElementById('availabilityFilter');
    const resetBtn = document.getElementById('resetFilters');
    const devicesTableBody = document.getElementById('devicesTableBody');
    const resultCount = document.getElementById('resultCount');
    
    function filterDevices() {
        const searchTerm = searchInput.value.toLowerCase();
        const typeValue = typeFilter.value;
        const availabilityValue = availabilityFilter.value;
        
        const rows = devicesTableBody.querySelectorAll('tr');
        let visibleCount = 0;
        
        rows.forEach(row => {
            const matchesSearch = !searchTerm || 
                row.getAttribute('data-search').includes(searchTerm);
            const matchesType = !typeValue || 
                row.getAttribute('data-type-id') === typeValue;
            const matchesAvailability = !availabilityValue || 
                row.getAttribute('data-status') === availabilityValue;
            
            if (matchesSearch && matchesType && matchesAvailability) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });
        
        resultCount.textContent = `Showing ${visibleCount} of ${rows.length} devices`;
    }
    
    // Event listeners for filtering
    searchInput.addEventListener('input', filterDevices);
    typeFilter.addEventListener('change', filterDevices);
    availabilityFilter.addEventListener('change', filterDevices);
    
    resetBtn.addEventListener('click', function() {
        searchInput.value = '';
        typeFilter.value = '';
        availabilityFilter.value = '';
        filterDevices();
    });
    
    // Device modal handling
    const deviceModal = new bootstrap.Modal(document.getElementById('deviceModal'));
    const deviceForm = document.getElementById('deviceForm');
    const modalTitle = document.getElementById('modalTitle');
    const formAction = document.getElementById('formAction');
    const deviceIdInput = document.getElementById('deviceId');
    
    // Edit button click handlers
    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const deviceId = this.getAttribute('data-id');
            // In a real app, you would fetch the device data via AJAX
            // For this example, we'll simulate it
            modalTitle.textContent = 'Edit Device';
            formAction.value = 'edit';
            deviceIdInput.value = deviceId;
            
            // Simulate loading device data
            document.getElementById('asset_no').value = 'ASSET-' + deviceId;
            document.getElementById('serial_no').value = 'SERIAL-' + deviceId;
            document.getElementById('name').value = 'Device ' + deviceId;
            document.getElementById('brand').value = 'Brand ' + (deviceId % 3 + 1);
            document.getElementById('product_type_id').value = deviceId % 3 + 1;
            document.getElementById('is_available').checked = deviceId % 2 === 0;
            document.getElementById('cpu').value = 'CPU ' + (deviceId % 4 + 1) + '.0GHz';
            document.getElementById('ram').value = (deviceId % 8 + 4) + 'GB';
            document.getElementById('storage').value = (deviceId % 4 + 1) * 256 + 'GB SSD';
            document.getElementById('specifications').value = 'Sample specifications for device ' + deviceId;
            
            deviceModal.show();
        });
    });
    
    // Add new device button
    document.querySelector('[data-action="add"]').addEventListener('click', function() {
        modalTitle.textContent = 'Add New Device';
        formAction.value = 'add';
        deviceIdInput.value = '';
        deviceForm.reset();
    });
    
    // Form submission
    deviceForm.addEventListener('submit', function(e) {
        e.preventDefault();
        // In a real app, you would submit via AJAX
        alert('Device saved successfully!');
        deviceModal.hide();
        // Reload or update the table in a real app
    });
    
    // Delete and unassign buttons
    const confirmModal = new bootstrap.Modal(document.getElementById('confirmModal'));
    const confirmActionBtn = document.getElementById('confirmAction');
    let currentAction = null;
    let currentDeviceId = null;
    
    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            currentDeviceId = this.getAttribute('data-id');
            currentAction = 'delete';
            document.getElementById('confirmModalTitle').textContent = 'Confirm Deletion';
            document.getElementById('confirmModalBody').textContent = 
                'Are you sure you want to delete this device? This action cannot be undone.';
            confirmModal.show();
        });
    });
    
    document.querySelectorAll('.unassign-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            currentDeviceId = this.getAttribute('data-id');
            currentAction = 'unassign';
            document.getElementById('confirmModalTitle').textContent = 'Confirm Unassign';
            document.getElementById('confirmModalBody').textContent = 
                'Are you sure you want to unassign this device from the user?';
            confirmModal.show();
        });
    });
    
    confirmActionBtn.addEventListener('click', function() {
        // In a real app, you would perform the action via AJAX
        alert(`${currentAction} action performed on device ${currentDeviceId}`);
        confirmModal.hide();
        // Reload or update the table in a real app
    });
    
    // Show loading indicator for demo purposes
    function showLoading() {
        document.getElementById('loadingIndicator').classList.remove('d-none');
        setTimeout(() => {
            document.getElementById('loadingIndicator').classList.add('d-none');
        }, 1000);
    }
    
    // Simulate loading for actions
    [deviceModal._element, confirmModal._element].forEach(modal => {
        modal.addEventListener('show.bs.modal', showLoading);
    });
});
</script>

<?php
require_once '../includes/footer.php';
?>