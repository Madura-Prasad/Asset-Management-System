<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

if (!isLoggedIn() || !isAdmin()) {
    header("Location: " . APP_URL . "/index.php");
    exit;
}
// Process form submissions FIRST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Process your form data
    
    // Then redirect
    ob_end_clean();
    $_SESSION['message'] = "Device updated successfully";
    $_SESSION['message_type'] = "success";
    header("Location: devices.php");
}

$availableOnly = isset($_GET['available']) && $_GET['available'] == 1;
$devices = getDevices($availableOnly);

$pageTitle = "Manage Devices";
require_once '../includes/header.php';
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Manage Devices</h1>
        <a href="?action=add" class="btn btn-primary">Add New Device</a>
    </div>
    
    <div class="card mb-4">
        <div class="card-body">
            <form method="get" class="row g-3" id="filterForm">
                <div class="col-md-4">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>" placeholder="Search devices...">
                </div>
                
                <div class="col-md-3">
                    <label for="type" class="form-label">Device Type</label>
                    <select class="form-select" id="type" name="type">
                        <option value="">All Types</option>
                        <?php foreach (getDeviceTypes() as $type): ?>
                            <option value="<?php echo $type['id']; ?>" 
                                <?php echo (isset($_GET['type']) && $_GET['type'] == $type['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($type['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label for="availability" class="form-label">Availability</label>
                    <select class="form-select" id="availability" name="availability">
                        <option value="">All</option>
                        <option value="1" <?php echo (isset($_GET['availability']) && $_GET['availability'] == '1') ? 'selected' : ''; ?>>Available</option>
                        <option value="0" <?php echo (isset($_GET['availability']) && $_GET['availability'] == '0') ? 'selected' : ''; ?>>Assigned</option>
                    </select>
                </div>
                
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">Filter</button>
                    <a href="devices.php" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>
    
    <div class="card">
        <div class="card-body">
            <?php if (empty($devices)): ?>
                <p class="text-center">No devices found</p>
            <?php else: ?>
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
                                <tr data-type="<?php echo htmlspecialchars($device['type_name'] ?? ''); ?>"
                                    data-available="<?php echo $device['is_available'] ? '1' : '0'; ?>">
                                    <td><?php echo htmlspecialchars($device['asset_no']); ?></td>
                                    <td><?php echo htmlspecialchars($device['name']); ?></td>
                                    <td><?php echo htmlspecialchars($device['type_name'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($device['brand'] ?? 'N/A'); ?></td>
                                    <td>
                                        <?php if ($device['cpu'] || $device['ram'] || $device['storage']): ?>
                                            <?php echo htmlspecialchars(implode(', ', array_filter([$device['cpu'], $device['ram'], $device['storage']]))); ?>
                                        <?php else: ?>
                                            N/A
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($device['is_available']): ?>
                                            <span class="badge bg-success">Available</span>
                                        <?php else: ?>
                                            <span class="badge bg-primary">Assigned to <?php echo htmlspecialchars($device['first_name'] . ' ' . $device['surname']); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="devices.php?action=edit&id=<?php echo $device['id']; ?>" 
                                               class="btn btn-outline-primary" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <?php if (!$device['is_available']): ?>
                                                <a href="devices.php?action=unassign&id=<?php echo $device['id']; ?>" 
                                                   class="btn btn-outline-warning" title="Unassign"
                                                   onclick="return confirm('Are you sure you want to unassign this device?')">
                                                    <i class="bi bi-person-x"></i>
                                                </a>
                                            <?php endif; ?>
                                            <a href="devices.php?action=delete&id=<?php echo $device['id']; ?>" 
                                               class="btn btn-outline-danger" title="Delete"
                                               onclick="return confirm('Are you sure you want to delete this device?')">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
// Handle add/edit device form
if (isset($_GET['action']) && in_array($_GET['action'], ['add', 'edit'])) {
    $device = null;
    $title = 'Add New Device';
    
    if ($_GET['action'] == 'edit' && isset($_GET['id'])) {
        $device = getDeviceById($_GET['id']);
        if (!$device) {
            $_SESSION['message'] = "Device not found";
            $_SESSION['message_type'] = "danger";
            header("Location: devices.php");
            exit;
        }
        $title = 'Edit Device';
    }
    
    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = [
            'asset_no' => trim($_POST['asset_no']),
            'serial_no' => trim($_POST['serial_no']),
            'name' => trim($_POST['name']),
            'brand' => trim($_POST['brand']),
            'product_type_id' => $_POST['product_type_id'] ?: null,
            'cpu' => trim($_POST['cpu']),
            'ram' => trim($_POST['ram']),
            'storage' => trim($_POST['storage']),
            'specifications' => trim($_POST['specifications']),
            'is_available' => isset($_POST['is_available']) ? 1 : 0
        ];
        
        if ($_GET['action'] == 'add') {
            $result = addDevice($data);
        } else {
            $result = updateDevice($_GET['id'], $data);
        }
        
        $_SESSION['message'] = $result['message'];
        $_SESSION['message_type'] = $result['success'] ? 'success' : 'danger';
        header("Location: devices.php");
        exit;
    }
    ?>
    
    <!-- Modal -->
    <div class="modal fade" id="deviceModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><?php echo $title; ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="asset_no" class="form-label">Asset Number*</label>
                                <input type="text" class="form-control" id="asset_no" name="asset_no" 
                                       value="<?php echo $device ? htmlspecialchars($device['asset_no']) : ''; ?>" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="serial_no" class="form-label">Serial Number*</label>
                                <input type="text" class="form-control" id="serial_no" name="serial_no" 
                                       value="<?php echo $device ? htmlspecialchars($device['serial_no']) : ''; ?>" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="name" class="form-label">Device Name*</label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="<?php echo $device ? htmlspecialchars($device['name']) : ''; ?>" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="brand" class="form-label">Brand</label>
                                <input type="text" class="form-control" id="brand" name="brand" 
                                       value="<?php echo $device ? htmlspecialchars($device['brand']) : ''; ?>">
                            </div>
                            
                            <div class="col-md-6">
                                <label for="product_type_id" class="form-label">Device Type</label>
                                <select class="form-select" id="product_type_id" name="product_type_id">
                                    <option value="">Select Type</option>
                                    <?php foreach (getDeviceTypes() as $type): ?>
                                        <option value="<?php echo $type['id']; ?>" 
                                            <?php echo ($device && $device['product_type_id'] == $type['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($type['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Availability</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="is_available" name="is_available" 
                                           <?php echo (!$device || $device['is_available']) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="is_available">Available</label>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <label for="cpu" class="form-label">CPU</label>
                                <input type="text" class="form-control" id="cpu" name="cpu" 
                                       value="<?php echo $device ? htmlspecialchars($device['cpu']) : ''; ?>">
                            </div>
                            
                            <div class="col-md-4">
                                <label for="ram" class="form-label">RAM</label>
                                <input type="text" class="form-control" id="ram" name="ram" 
                                       value="<?php echo $device ? htmlspecialchars($device['ram']) : ''; ?>">
                            </div>
                            
                            <div class="col-md-4">
                                <label for="storage" class="form-label">Storage</label>
                                <input type="text" class="form-control" id="storage" name="storage" 
                                       value="<?php echo $device ? htmlspecialchars($device['storage']) : ''; ?>">
                            </div>
                            
                            <div class="col-12">
                                <label for="specifications" class="form-label">Specifications</label>
                                <textarea class="form-control" id="specifications" name="specifications" rows="3"><?php 
                                    echo $device ? htmlspecialchars($device['specifications']) : ''; 
                                ?></textarea>
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
    
    <script>
        // Show modal on page load
        document.addEventListener('DOMContentLoaded', function() {
            var deviceModal = new bootstrap.Modal(document.getElementById('deviceModal'));
            deviceModal.show();
            
            // Close modal and redirect when modal is hidden
            document.getElementById('deviceModal').addEventListener('hidden.bs.modal', function () {
                window.location.href = 'devices.php';
            });
        });
    </script>
    <?php
}

// Handle delete/unassign actions
if (isset($_GET['action']) && in_array($_GET['action'], ['delete', 'unassign']) && isset($_GET['id'])) {
    if ($_GET['action'] == 'delete') {
        $result = deleteDevice($_GET['id']);
    } else {
        $result = unassignDevice($_GET['id']);
    }
    
    // Store the result message in session
    $_SESSION['message'] = $result['message'];
    $_SESSION['message_type'] = $result['success'] ? 'success' : 'danger';
    
    // Clear output buffer and redirect
    ob_end_clean();
    echo '<script>window.location.href = "devices.php";</script>';
    exit;
}
?>

<script>
// Front-end filtering system
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search');
    const typeSelect = document.getElementById('type');
    const availabilitySelect = document.getElementById('availability');
    const deviceRows = document.querySelectorAll('#devicesTableBody tr');
    
    // Filter function
    function filterDevices() {
        const searchTerm = searchInput.value.toLowerCase();
        const selectedType = typeSelect.options[typeSelect.selectedIndex].text;
        const selectedAvailability = availabilitySelect.value;
        
        deviceRows.forEach(row => {
            const rowText = row.textContent.toLowerCase();
            const rowType = row.getAttribute('data-type');
            const rowAvailable = row.getAttribute('data-available');
            
            // Check search term
            const matchesSearch = searchTerm === '' || rowText.includes(searchTerm);
            
            // Check type filter
            const matchesType = typeSelect.value === '' || selectedType === rowType;
            
            // Check availability filter
            const matchesAvailability = selectedAvailability === '' || 
                                     (selectedAvailability === '1' && rowAvailable === '1') || 
                                     (selectedAvailability === '0' && rowAvailable === '0');
            
            // Show/hide row based on all filters
            row.style.display = (matchesSearch && matchesType && matchesAvailability) ? '' : 'none';
        });
    }
    
    // Add event listeners for all filter inputs
    searchInput.addEventListener('input', filterDevices);
    typeSelect.addEventListener('change', filterDevices);
    availabilitySelect.addEventListener('change', filterDevices);
    
    // Initial filter in case page loads with values
    filterDevices();
});
</script>

<?php
require_once '../includes/footer.php';
?>