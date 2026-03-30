<?php

include '../auth.php';
include '../../db.php';
include 'diesel_utils.php';
include 'equipment_utils.php';

$role = strtolower(trim($_SESSION['role'] ?? ''));
$department = strtolower(trim($_SESSION['department'] ?? ''));

if(!in_array($role, ['admin', 'manager'])){
    header("Location: /admin/login.php");
    exit();
}

if($role === 'manager' && ($department !== 'm&e' && $department !== 'me')){
    echo "Access Denied";
    exit();
}

$upcomingMaintenance = getUpcomingMaintenance($conn);
$dashboardData = getDieselDashboardData($conn, []);
$dieselRecords = getDieselRecords($conn);

// FIFO Fuel Inventory Logic - Sort by date ASC (oldest first for consumption)
$dieselRecordsFIFO = $dieselRecords;
usort($dieselRecordsFIFO, function($a, $b) {
    return strtotime($a['date']) - strtotime($b['date']);
});

// Calculate FIFO Current Stock
function calculateFIFOStock($records) {
    $stock = 0;
    foreach ($records as $record) {
        if (strtolower($record['transactionType']) === 'received' || strtolower($record['transactionType']) === 'fuel received') {
            $stock += $record['quantityLiters'];
        } else {
            $stock -= $record['quantityLiters'];
        }
    }
    return max(0, $stock);
}

$currentFIFOStock = calculateFIFOStock($dieselRecordsFIFO);

// Handle AJAX requests
if(isset($_POST['action'])) {
    header('Content-Type: application/json');

    if($_POST['action'] === 'get_dashboard_data') {
        $filters = [];
        if(!empty($_POST['start_date'])) $filters['startDate'] = $_POST['start_date'];
        if(!empty($_POST['end_date'])) $filters['endDate'] = $_POST['end_date'];

        $data = getDieselDashboardData($conn, $filters);
        echo json_encode($data);
        exit;
    }

    if($_POST['action'] === 'save_record') {
        $result = saveDieselRecord($conn, $_POST);
        echo json_encode($result);
        exit;
    }

    if($_POST['action'] === 'delete_record') {
        $result = deleteDieselRecord($conn, $_POST['id']);
        echo json_encode($result);
        exit;
    }

    if($_POST['action'] === 'get_records') {
        $records = getDieselRecords($conn);
        echo json_encode($records);
        exit;
    }
}

include '../templates/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>M&E Portal</title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- jsPDF and jsPDF-AutoTable CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>

    <!-- SheetJS (xlsx) CDN for Excel Export -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

    <style>
        /* Custom styles for professional look */
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f4f7f6;
        }
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

        /* Toast Notification styles */
        .toast {
            visibility: hidden;
            min-width: 250px;
            margin-left: -125px;
            color: #fff;
            text-align: center;
            border-radius: 8px;
            padding: 16px;
            position: fixed;
            z-index: 101;
            left: 50%;
            bottom: 30px;
            font-size: 17px;
            opacity: 0;
            transition: opacity 0.3s, bottom 0.3s, visibility 0.3s;
        }
        .toast.show {
            visibility: visible;
            opacity: 1;
            bottom: 50px;
        }
        .toast.success { background-color: #28a745; }
        .toast.error { background-color: #dc3545; }

        /* Modal Animation */
        .modal-hidden {
            display: none;
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        ::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
    </style>
</head>
<body class="bg-gray-100">

    <!-- Main App Container -->
    <div class="min-h-screen flex flex-col">
        <!-- Header -->
        <header class="bg-white shadow-md p-4 flex justify-between items-center sticky top-0 z-40">
            <div class="flex items-center space-x-3">
                <i class="fas fa-gas-pump text-2xl text-blue-500"></i>
                <h1 class="text-2xl font-bold text-gray-800">M&E Portal</h1>
            </div>
            <div class="flex items-center space-x-4">
                <span class="text-gray-600 text-sm">
                    <i class="fas fa-user-circle mr-2"></i><?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?>
                </span>
                <?php if (strtolower($role) === 'admin'): ?>
                    <a href="/admin/dashboard.php" class="text-blue-600 hover:text-blue-800 font-medium">
                        <i class="fas fa-arrow-left mr-1"></i>Back to Admin
                    </a>
                <?php else: ?>
                    <a href="/admin/logout.php" class="text-gray-600 hover:text-red-500 font-medium">
                        <i class="fas fa-sign-out-alt mr-1"></i>Logout
                    </a>
                <?php endif; ?>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto p-4 md:p-6">
            
            <!-- Updated Navigation Tabs -->
            <div class="mb-6 flex flex-wrap gap-2 border-b border-gray-200">
                <button onclick="return showPage('dashboard', event)" class="nav-tab active px-4 py-2 font-medium text-blue-600 border-b-2 border-blue-600 hover:text-blue-700">
                    <i class="fas fa-chart-line mr-2"></i>Dashboard
                </button>
                <button onclick="return showPage('fuel-inventory-control', event)" class="nav-tab px-4 py-2 font-medium text-gray-600 border-b-2 border-transparent hover:text-gray-800">
                    <i class="fas fa-gas-pump mr-2"></i>Fuel Inventory Control
                </button>
                <button onclick="return showPage('meter-reading', event)" class="nav-tab px-4 py-2 font-medium text-gray-600 border-b-2 border-transparent hover:text-gray-800">
                    <i class="fas fa-tachometer-alt mr-2"></i>Meter Reading
                </button>
                <button onclick="return showPage('generator-record', event)" class="nav-tab px-4 py-2 font-medium text-gray-600 border-b-2 border-transparent hover:text-gray-800">
                    <i class="fas fa-bolt mr-2"></i>Generator Record
                </button>
                <button onclick="return showPage('equipment', event)" class="nav-tab px-4 py-2 font-medium text-gray-600 border-b-2 border-transparent hover:text-gray-800">
                    <i class="fas fa-cogs mr-2"></i>Equipment
                </button>
                <button onclick="return showPage('to-do-task', event)" class="nav-tab px-4 py-2 font-medium text-gray-600 border-b-2 border-transparent hover:text-gray-800">
                    <i class="fas fa-tasks mr-2"></i>To Do Task
                </button>
            </div>

            <!-- 2. METER READING PAGE -->
            <div id="meter-reading-page" class="page hidden">
                <?php include 'modules/meter_reading.php'; ?>
            </div>

            <!-- 3. GENERATOR RECORD PAGE -->
            <div id="generator-record-page" class="page hidden">
                <?php include 'modules/generator_record.php'; ?>
            </div>

            <!-- 4. TO DO TASK PAGE -->
            <div id="to-do-task-page" class="page hidden">
                <?php include 'modules/todo_task.php'; ?>
            </div>

            <!-- 1. DASHBOARD PAGE -->
            <div id="dashboard-page" class="page">
                <!-- Dashboard Stats -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                    <div class="bg-white p-5 rounded-lg shadow-md flex items-center justify-between">
                        <div>
                            <h3 class="text-gray-500 font-medium">Current Stock (Liters)</h3>
                            <p id="current-stock" class="text-3xl font-bold text-gray-800"><?php echo number_format($currentFIFOStock, 2); ?></p>
                        </div>
                        <i class="fas fa-tint text-4xl text-blue-500"></i>
                    </div>
                    <div class="bg-white p-5 rounded-lg shadow-md flex items-center justify-between">
                        <div>
                            <h3 class="text-gray-500 font-medium">Total Received (Liters)</h3>
                            <p id="total-inflow" class="text-3xl font-bold text-gray-800"><?php echo number_format($dashboardData['scorecards']['totalReceived'] ?? 0, 2); ?></p>
                        </div>
                        <i class="fas fa-arrow-circle-up text-4xl text-green-500"></i>
                    </div>
                    <div class="bg-white p-5 rounded-lg shadow-md flex items-center justify-between">
                        <div>
                            <h3 class="text-gray-500 font-medium">Total Usage (Liters)</h3>
                            <p id="total-outflow" class="text-3xl font-bold text-gray-800"><?php echo number_format($dashboardData['scorecards']['totalUsage'] ?? 0, 2); ?></p>
                        </div>
                        <i class="fas fa-arrow-circle-down text-4xl text-red-500"></i>
                    </div>
                    <div class="bg-white p-5 rounded-lg shadow-md flex items-center justify-between">
                        <div>
                            <h3 class="text-gray-500 font-medium">FIFO Method</h3>
                            <p class="text-xl font-bold text-gray-800"><i class="fas fa-check-circle text-green-500 mr-2"></i>Active</p>
                        </div>
                        <i class="fas fa-cubes text-4xl text-purple-500"></i>
                    </div>
                </div>

                <!-- Dashboard Filters -->
                <div class="bg-white p-4 rounded-lg shadow-md mb-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 items-end">
                        <div>
                            <label for="dash-start-date" class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                            <input type="date" id="dash-start-date" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label for="dash-end-date" class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                            <input type="date" id="dash-end-date" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div class="flex space-x-2">
                            <button onclick="applyDashboardFilter()" class="w-full inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                <i class="fas fa-filter mr-2"></i>Apply
                            </button>
                            <button onclick="resetDashboardFilter()" class="w-full inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                <i class="fas fa-redo mr-2"></i>Reset
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Charts -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="bg-white p-6 rounded-lg shadow-md">
                        <h3 class="text-xl font-semibold mb-4 text-gray-700">Monthly Fuel Usage</h3>
                        <canvas id="monthlyUsageChart" height="300"></canvas>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow-md">
                        <h3 class="text-xl font-semibold mb-4 text-gray-700">Usage by Equipment</h3>
                        <canvas id="usageByEquipmentChart" height="300"></canvas>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow-md">
                        <h3 class="text-xl font-semibold mb-4 text-gray-700">Received from Suppliers</h3>
                        <canvas id="receivedBySupplierChart" height="300"></canvas>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow-md">
                        <h3 class="text-xl font-semibold mb-4 text-gray-700">Recent Activity</h3>
                        <div class="space-y-3">
                            <?php if (!empty($dashboardData['recentActivity'])): ?>
                                <?php foreach (array_slice($dashboardData['recentActivity'], 0, 5) as $activity): ?>
                                    <div class="flex items-center justify-between py-2 border-b border-gray-200 last:border-b-0">
                                        <div>
                                            <p class="font-medium text-gray-800"><?php echo htmlspecialchars($activity['type']); ?></p>
                                            <p class="text-xs text-gray-500"><?php echo htmlspecialchars($activity['date']); ?></p>
                                        </div>
                                        <span class="text-lg font-bold text-blue-600"><?php echo htmlspecialchars($activity['quantity']); ?> L</span>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-gray-500 text-center py-4">No recent activity</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. FUEL INVENTORY PAGE -->
            <div id="fuel-inventory-control-page" class="page hidden">
                <?php include 'modules/fuel_inventory_control.php'; ?>
            </div>

            <!-- Edit Modal -->
            <div id="edit-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
                <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
                    <div class="mt-3 text-center">
                        <h3 class="text-2xl leading-6 font-bold text-gray-900 mb-6">Edit Entry</h3>
                        <form id="edit-entry-form" class="space-y-6 text-left">
                            <input type="hidden" id="edit-id">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="edit-date" class="block text-sm font-medium text-gray-700">Date</label>
                                    <input type="date" id="edit-date" name="date" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label for="edit-type" class="block text-sm font-medium text-gray-700">Transaction Type</label>
                                    <select id="edit-type" name="type" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="received">Fuel Received</option>
                                        <option value="used">Fuel Used</option>
                                    </select>
                                </div>
                            </div>
                            <div id="edit-supplier-group" class="hidden">
                                <label for="edit-supplier" class="block text-sm font-medium text-gray-700">Supplier</label>
                                <input type="text" id="edit-supplier" name="supplier" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <div id="edit-vehicle-group" class="hidden">
                                <label for="edit-vehicle" class="block text-sm font-medium text-gray-700">Vehicle/Equipment</label>
                                <input type="text" id="edit-vehicle" name="vehicle" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="edit-quantity" class="block text-sm font-medium text-gray-700">Quantity (Liters)</label>
                                    <input type="number" id="edit-quantity" name="quantity" min="0.01" step="0.01" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                <div id="edit-price-group" class="hidden">
                                    <label for="edit-price" class="block text-sm font-medium text-gray-700">Price per Liter (Kyats)</label>
                                    <input type="number" id="edit-price" name="price" min="0.01" step="0.01" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                            </div>
                            <div>
                                <label for="edit-notes" class="block text-sm font-medium text-gray-700">Notes</label>
                                <textarea id="edit-notes" name="notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                            </div>
                            <div class="flex justify-end space-x-4">
                                <button id="cancel-edit" type="button" class="px-6 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">Cancel</button>
                                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- 4. EQUIPMENT PAGE -->
            <div id="equipment-page" class="page hidden">
                <?php include 'modules/equipment.php'; ?>
            </div>
        </main>
    </div>

    <!-- Edit Modal for Fuel Inventory -->
    <div id="edit-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 modal-hidden">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <h3 class="text-2xl leading-6 font-bold text-gray-900 mb-6">Edit Fuel Record</h3>
                <form id="edit-entry-form" class="space-y-6 text-left">
                    <input type="hidden" id="edit-fuel-id">
                    
                    <div>
                        <label for="edit-fuel-date" class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                        <input type="date" id="edit-fuel-date" name="date" required class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label for="edit-fuel-type" class="block text-sm font-medium text-gray-700 mb-1">Transaction Type</label>
                        <select id="edit-fuel-type" name="transactionType" required class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="Fuel Received">Fuel Received</option>
                            <option value="Fuel Used">Fuel Used</option>
                        </select>
                    </div>

                    <div>
                        <label for="edit-fuel-quantity" class="block text-sm font-medium text-gray-700 mb-1">Quantity (Liters)</label>
                        <input type="number" id="edit-fuel-quantity" name="quantityLiters" min="0.01" step="0.01" required class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label for="edit-fuel-source" class="block text-sm font-medium text-gray-700 mb-1">Source/Destination</label>
                        <input type="text" id="edit-fuel-source" name="sourceOrDestination" required class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div id="edit-price-group" class="hidden">
                        <label for="edit-fuel-price" class="block text-sm font-medium text-gray-700 mb-1">Price per Liter (Kyats)</label>
                        <input type="number" id="edit-fuel-price" name="pricePerLiter" min="0.01" step="0.01" class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label for="edit-fuel-notes" class="block text-sm font-medium text-gray-700 mb-1">Notes (Optional)</label>
                        <textarea id="edit-fuel-notes" name="notes" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"></textarea>
                    </div>

                    <div class="items-center px-4 py-3 flex justify-end space-x-4">
                        <button id="cancel-edit" type="button" class="px-6 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 focus:outline-none">Cancel</button>
                        <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="delete-confirm-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 modal-hidden">
        <div class="relative top-40 mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                    <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                </div>
                <h3 class="text-lg leading-6 font-medium text-gray-900 mt-4">Delete Fuel Record</h3>
                <div class="mt-2 px-7 py-3">
                    <p class="text-sm text-gray-500">Are you sure you want to delete this fuel record? This action cannot be undone.</p>
                </div>
                <div class="items-center px-4 py-3 flex justify-center space-x-4">
                    <button id="cancel-delete" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 w-24">Cancel</button>
                    <button id="confirm-delete" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 w-24">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="toast"></div>

    <script>
        // Global variables
        let allFuelRecords = <?php echo json_encode($dieselRecords); ?>;
        let currentlyDisplayedData = [];
        let deleteTargetId = null;
        let editingRecordId = null;
        let chartInstances = {};

        // Toast Notification
        function showToast(message, type = 'success') {
            const toast = document.getElementById('toast');
            toast.textContent = message;
            toast.className = `toast show ${type}`;
            setTimeout(() => { toast.className = toast.className.replace('show', ''); }, 3000);
        }

        // Page Navigation
        // Show/Hide Pages
        function showPage(pageId, event) {
            if (event) {
                event.preventDefault();
            }
            
            console.log('showPage called with pageId:', pageId);
            
            try {
                // Get the button element from the event
                const buttonElement = event ? event.target.closest('button') : null;
                
                // Hide all pages
                const pages = document.querySelectorAll('.page');
                pages.forEach(p => {
                    p.classList.add('hidden');
                });
                
                // Show selected page
                const pageElement = document.getElementById(pageId + '-page');
                console.log('Looking for element with id:', pageId + '-page', 'Found:', !!pageElement);
                
                if (pageElement) {
                    pageElement.classList.remove('hidden');
                    console.log('✓ Page shown:', pageId);
                } else {
                    console.error('✗ Page element not found:', pageId + '-page');
                }
                
                // Update tab styling
                const tabs = document.querySelectorAll('.nav-tab');
                tabs.forEach(tab => {
                    tab.classList.remove('active', 'text-blue-600', 'border-blue-600');
                    tab.classList.add('text-gray-600', 'border-transparent');
                });
                
                // Highlight active tab
                if (buttonElement) {
                    buttonElement.classList.add('active', 'text-blue-600', 'border-blue-600');
                    buttonElement.classList.remove('text-gray-600', 'border-transparent');
                    console.log('✓ Tab highlighted:', pageId);
                }
                
                return false;
            } catch (error) {
                console.error('Error in showPage:', error);
                return false;
            }
        }

        // Initialize on DOM ready
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM Content Loaded - Initializing');
            initializeFormListeners();
        }, { once: true });

        // Inventory Filtering
        function applyInventoryFilter() {
            const typeFilter = document.getElementById('inv-type-filter').value.toLowerCase();
            const startDate = document.getElementById('inv-start-date').value;
            const endDate = document.getElementById('inv-end-date').value;
            
            const rows = document.querySelectorAll('#inventory-table-body tr');
            rows.forEach(row => {
                if (row.textContent.includes('No fuel records')) return;
                
                let show = true;
                const typeCell = row.cells[1].textContent.toLowerCase();
                const dateCell = row.cells[0].textContent;
                
                if (typeFilter && !typeCell.includes(typeFilter)) show = false;
                if (startDate && dateCell < startDate) show = false;
                if (endDate && dateCell > endDate) show = false;
                
                row.style.display = show ? '' : 'none';
            });
        }

        function resetInventoryFilter() {
            document.getElementById('inv-type-filter').value = '';
            document.getElementById('inv-start-date').value = '';
            document.getElementById('inv-end-date').value = '';
            document.querySelectorAll('#inventory-table-body tr').forEach(row => row.style.display = '');
        }

        // Dashboard Filtering
        function applyDashboardFilter() {
            const startDate = document.getElementById('dash-start-date').value;
            const endDate = document.getElementById('dash-end-date').value;
            
            fetch(window.location.href, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=get_dashboard_data&start_date=${startDate}&end_date=${endDate}`
            })
            .then(resp => resp.json())
            .then(data => updateDashboard(data))
            .catch(err => {
                console.error(err);
                showToast('Error loading dashboard data', 'error');
            });
        }

        function resetDashboardFilter() {
            document.getElementById('dash-start-date').value = '';
            document.getElementById('dash-end-date').value = '';
            const dashData = <?php echo json_encode($dashboardData); ?>;
            updateDashboard(dashData);
        }

        function updateDashboard(data) {
            document.getElementById('current-stock').textContent = data.scorecards?.currentStock || '0';
            document.getElementById('total-inflow').textContent = data.scorecards?.totalReceived || '0';
            document.getElementById('total-outflow').textContent = data.scorecards?.totalUsage || '0';
            
            renderCharts(data);
        }

        function renderCharts(data) {
            renderChart('monthlyUsageChart', 'bar', data.monthlyUsage?.labels || [], data.monthlyUsage?.data || [], 'Fuel Usage (L)', '#ef4444');
            renderChart('usageByEquipmentChart', 'doughnut', data.usageByEquipment?.labels || [], data.usageByEquipment?.data || []);
            renderChart('receivedBySupplierChart', 'pie', data.receivedBySupplier?.labels || [], data.receivedBySupplier?.data || []);
        }

        function renderChart(canvasId, type, labels, data, label = '', color = null) {
            const canvas = document.getElementById(canvasId);
            if (!canvas) return;
            
            const ctx = canvas.getContext('2d');
            if (chartInstances[canvasId]) chartInstances[canvasId].destroy();
            
            const colors = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899', '#06b6d4'];
            
            chartInstances[canvasId] = new Chart(ctx, {
                type: type,
                data: {
                    labels: labels,
                    datasets: [{
                        label: label,
                        data: data,
                        backgroundColor: (type === 'doughnut' || type === 'pie') ? colors : (color || '#3b82f6'),
                        borderColor: (type === 'doughnut' || type === 'pie') ? '#fff' : 'rgba(0,0,0,0.1)',
                        borderWidth: (type === 'doughnut' || type === 'pie') ? 2 : 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: (type === 'doughnut' || type === 'pie'), position: 'bottom' }
                    },
                    scales: (type === 'bar') ? { y: { beginAtZero: true } } : {}
                }
            });
        }

        // Excel Export
        function exportInventoryExcel() {
            const rows = document.querySelectorAll('#inventory-table-body tr');
            const excelData = [];
            
            rows.forEach(row => {
                if (row.textContent.includes('No fuel records')) return;
                const cells = row.querySelectorAll('td');
                excelData.push({
                    Date: cells[0]?.textContent || '',
                    Type: cells[1]?.textContent || '',
                    'Quantity (L)': cells[2]?.textContent || '',
                    'Source/Destination': cells[3]?.textContent || '',
                    'Price/L': cells[4]?.textContent || '',
                    Notes: cells[5]?.textContent || ''
                });
            });

            if (excelData.length === 0) {
                showToast('No data to export', 'error');
                return;
            }

            const worksheet = XLSX.utils.json_to_sheet(excelData);
            const workbook = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(workbook, worksheet, 'Fuel Inventory');
            XLSX.writeFile(workbook, 'Fuel_Inventory_' + new Date().toISOString().split('T')[0] + '.xlsx');
            showToast('Exported to Excel successfully!');
        }

        // PDF Export
        function exportInventoryPDF() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();
            
            doc.autoTable({
                head: [['Date', 'Type', 'Quantity (L)', 'Source/Destination', 'Price/L', 'Notes']],
                body: Array.from(document.querySelectorAll('#inventory-table-body tr')).map(row => {
                    if (row.textContent.includes('No fuel records')) return null;
                    return Array.from(row.querySelectorAll('td')).slice(0, 6).map(cell => cell.textContent);
                }).filter(Boolean),
                startY: 30,
                theme: 'grid'
            });

            doc.text('M&E Fuel Inventory Report', 14, 15);
            doc.text('Generated: ' + new Date().toLocaleDateString('en-CA'), 14, 22);
            doc.save('Fuel_Inventory_' + new Date().toISOString().split('T')[0] + '.pdf');
            showToast('Exported to PDF successfully!');
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', () => {
            // Set today's date for the date input
            const dateInput = document.getElementById('date');
            if (dateInput) {
                dateInput.valueAsDate = new Date();
            }
            
            const dashData = <?php echo json_encode($dashboardData); ?>;
            renderCharts(dashData);
            loadInventoryData(); // Load data for fuel inventory control
            
            // Initialize form listeners
            initializeFormListeners();
        });

        // Fuel Inventory Control JavaScript
        let deleteTargetId = null;

        // Toast Notifications
        function showToast(message, type = 'success') {
            const toast = document.getElementById('toast');
            toast.textContent = message;
            toast.className = `toast show ${type}`;
            setTimeout(() => { toast.className = toast.className.replace('show', ''); }, 3000);
        }

        // Form Logic
        function handleTransactionTypeChange(type, supplierEl, vehicleEl, priceEl) {
            if (type === 'received') {
                supplierEl.classList.remove('hidden');
                vehicleEl.classList.add('hidden');
                if(vehicleEl.querySelector('input')) vehicleEl.querySelector('input').value = '';
                priceEl.parentElement.classList.remove('hidden');
                priceEl.required = true;
            } else if (type === 'used') {
                supplierEl.classList.add('hidden');
                if(supplierEl.querySelector('input')) supplierEl.querySelector('input').value = '';
                vehicleEl.classList.remove('hidden');
                priceEl.parentElement.classList.add('hidden');
                priceEl.required = false;
                priceEl.value = '';
            }
        }

        // Initialize event listeners and form handling
        function initializeFormListeners() {
            const typeElement = document.getElementById('type');
            const editTypeElement = document.getElementById('edit-type');
            
            if (typeElement) {
                typeElement.addEventListener('change', (e) => handleTransactionTypeChange(e.target.value, document.getElementById('supplier-group'), document.getElementById('vehicle-group'), document.getElementById('price')));
            }
            
            if (editTypeElement) {
                editTypeElement.addEventListener('change', (e) => handleTransactionTypeChange(e.target.value, document.getElementById('edit-supplier-group'), document.getElementById('edit-vehicle-group'), document.getElementById('edit-price')));
            }

            // Initialize form submission
            const addEntryForm = document.getElementById('add-entry-form');
            if (addEntryForm) {
                addEntryForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const formData = new FormData(this);
                    const data = {
                        date: formData.get('date'),
                        transactionType: formData.get('type') === 'received' ? 'Fuel Received' : 'Fuel Used',
                        quantityLiters: parseFloat(formData.get('quantity')),
                        sourceOrDestination: formData.get('supplier') || formData.get('vehicle') || '',
                        pricePerLiter: formData.get('type') === 'received' ? parseFloat(formData.get('price')) || 0 : 0,
                        notes: formData.get('notes') || ''
                    };

                    fetch(window.location.href, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: 'action=save_record&' + new URLSearchParams(data)
                    })
                    .then(response => response.json())
                    .then(result => {
                        if (result.success) {
                            showToast('Entry added successfully!');
                            this.reset();
                            document.getElementById('date').valueAsDate = new Date();
                            handleTransactionTypeChange('received', document.getElementById('supplier-group'), document.getElementById('vehicle-group'), document.getElementById('price'));
                            loadInventoryData();
                        } else {
                            showToast(result.message || 'Error adding entry.', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showToast('Error adding entry.', 'error');
                    });
                });
            }
        }

        // Load Inventory Data
        function loadInventoryData() {
            fetch(window.location.href, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=get_records'
            })
            .then(response => response.json())
            .then(data => renderTable(data))
            .catch(error => console.error('Error loading data:', error));
        }

        // Render Table
        function renderTable(data) {
            const tableBody = document.getElementById('inventory-table-body');
            tableBody.innerHTML = '';

            if (data.length === 0) {
                tableBody.innerHTML = `<tr><td colspan="7" class="text-center py-4 text-gray-500">No records found.</td></tr>`;
                return;
            }

            data.forEach(item => {
                const type = item.transactionType.toLowerCase().includes('received') ? 'received' : 'used';
                const supplierOrVehicle = type === 'received' ? (item.sourceOrDestination || '') : (item.sourceOrDestination || '');
                const row = document.createElement('tr');
                row.className = 'hover:bg-gray-50';

                row.innerHTML = `
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${item.date}</td>
                    <td class="px-6 py-4 whitespace-nowrap"><span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${type === 'received' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">${type === 'received' ? 'Received' : 'Usage'}</span></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${parseFloat(item.quantityLiters).toFixed(2)}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${type === 'received' ? (item.pricePerLiter ? parseFloat(item.pricePerLiter).toFixed(2) : 'N/A') : 'N/A'}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${supplierOrVehicle}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 max-w-xs truncate">${item.notes || ''}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <button class="text-indigo-600 hover:text-indigo-900 mr-3 edit-btn" data-id="${item.id}"><i class="fas fa-edit"></i> Edit</button>
                        <button class="text-red-600 hover:text-red-900 delete-btn" data-id="${item.id}"><i class="fas fa-trash"></i> Delete</button>
                    </td>
                `;
                tableBody.appendChild(row);
            });
        }

        // Edit Modal
        document.addEventListener('click', function(e) {
            if (e.target.closest('.edit-btn')) {
                const id = e.target.closest('.edit-btn').dataset.id;
                openEditModal(id);
            }
            if (e.target.closest('.delete-btn')) {
                const id = e.target.closest('.delete-btn').dataset.id;
                openDeleteModal(id);
            }
        });

        function openEditModal(id) {
            fetch(window.location.href, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=get_records'
            })
            .then(response => response.json())
            .then(data => {
                const item = data.find(d => d.id == id);
                if (item) {
                    document.getElementById('edit-id').value = item.id;
                    document.getElementById('edit-date').value = item.date;
                    document.getElementById('edit-type').value = item.transactionType.toLowerCase().includes('received') ? 'received' : 'used';
                    document.getElementById('edit-quantity').value = item.quantityLiters;
                    document.getElementById('edit-supplier').value = item.sourceOrDestination || '';
                    document.getElementById('edit-vehicle').value = item.sourceOrDestination || '';
                    document.getElementById('edit-price').value = item.pricePerLiter || '';
                    document.getElementById('edit-notes').value = item.notes || '';
                    handleTransactionTypeChange(document.getElementById('edit-type').value, document.getElementById('edit-supplier-group'), document.getElementById('edit-vehicle-group'), document.getElementById('edit-price'));
                    document.getElementById('edit-modal').classList.remove('hidden');
                }
            });
        }

        function closeEditModal() {
            document.getElementById('edit-modal').classList.add('hidden');
            document.getElementById('edit-entry-form').reset();
        }

        const cancelEditBtn = document.getElementById('cancel-edit');
        if (cancelEditBtn) {
            cancelEditBtn.addEventListener('click', closeEditModal);
        }

        const editEntryForm = document.getElementById('edit-entry-form');
        if (editEntryForm) {
            editEntryForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const data = {
                id: formData.get('id'),
                date: formData.get('date'),
                transactionType: formData.get('type') === 'received' ? 'Fuel Received' : 'Fuel Used',
                quantityLiters: parseFloat(formData.get('quantity')),
                sourceOrDestination: formData.get('supplier') || formData.get('vehicle') || '',
                pricePerLiter: formData.get('type') === 'received' ? parseFloat(formData.get('price')) || 0 : 0,
                notes: formData.get('notes') || ''
            };

            fetch(window.location.href, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=save_record&' + new URLSearchParams(data)
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    showToast('Entry updated successfully!');
                    closeEditModal();
                    loadInventoryData();
                } else {
                    showToast(result.message || 'Error updating entry.', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error updating entry.', 'error');
            });
            });
        }

        // Delete Modal
        function openDeleteModal(id) {
            deleteTargetId = id;
            document.getElementById('delete-confirm-modal').classList.remove('hidden');
        }

        function closeDeleteModal() {
            deleteTargetId = null;
            document.getElementById('delete-confirm-modal').classList.add('hidden');
        }

        const cancelDeleteBtn = document.getElementById('cancel-delete');
        if (cancelDeleteBtn) {
            cancelDeleteBtn.addEventListener('click', closeDeleteModal);
        }

        const confirmDeleteBtn = document.getElementById('confirm-delete');
        if (confirmDeleteBtn) {
            confirmDeleteBtn.addEventListener('click', function() {
                if (deleteTargetId) {
                    fetch(window.location.href, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: 'action=delete_record&id=' + deleteTargetId
                    })
                    .then(response => response.json())
                    .then(result => {
                        if (result.success) {
                            showToast('Entry deleted successfully!');
                            loadInventoryData();
                        } else {
                            showToast(result.message || 'Error deleting entry.', 'error');
                        }
                        closeDeleteModal();
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showToast('Error deleting entry.', 'error');
                        closeDeleteModal();
                    });
                }
            });
        }

        // Filters
        function applyListFilters() {
            const startDate = document.getElementById('list-start-date').value;
            const endDate = document.getElementById('list-end-date').value;
            const type = document.getElementById('list-type-filter').value;

            fetch(window.location.href, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=get_records'
            })
            .then(response => response.json())
            .then(data => {
                let filteredData = data;
                if (startDate || endDate || type) {
                    filteredData = data.filter(item => {
                        const itemDate = new Date(item.date);
                        const start = startDate ? new Date(startDate) : null;
                        const end = endDate ? new Date(endDate) : null;
                        const itemType = item.transactionType.toLowerCase().includes('received') ? 'received' : 'used';

                        if (start && itemDate < start) return false;
                        if (end && itemDate > end) return false;
                        if (type && itemType !== type) return false;
                        return true;
                    });
                }
                renderTable(filteredData);
            });
        }

        function resetListFilters() {
            document.getElementById('list-start-date').value = '';
            document.getElementById('list-end-date').value = '';
            document.getElementById('list-type-filter').value = '';
            loadInventoryData();
        }

        // Export Functions (adapted)
        function exportInventoryPDF() {
            const tableBody = document.getElementById('inventory-table-body');
            const rows = tableBody.querySelectorAll('tr');
            if (rows.length === 1 && rows[0].textContent.includes('No records found')) {
                showToast('No data to export.', 'error');
                return;
            }

            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();

            const body = Array.from(rows).map(row => {
                const cells = row.querySelectorAll('td');
                return Array.from(cells).slice(0, 6).map(cell => cell.textContent.trim());
            });

            doc.autoTable({
                head: [['Date', 'Type', 'Quantity (L)', 'Price/L', 'Supplier/Vehicle', 'Notes']],
                body: body,
                startY: 30,
                theme: 'grid'
            });

            doc.text('Fuel Inventory Control Report', 14, 15);
            doc.text('Generated: ' + new Date().toLocaleDateString('en-CA'), 14, 22);
            doc.save('Fuel_Inventory_' + new Date().toISOString().split('T')[0] + '.pdf');
            showToast('Exported to PDF successfully!');
        }

        function exportInventoryExcel() {
            const tableBody = document.getElementById('inventory-table-body');
            const rows = tableBody.querySelectorAll('tr');
            if (rows.length === 1 && rows[0].textContent.includes('No records found')) {
                showToast('No data to export.', 'error');
                return;
            }

            const excelData = Array.from(rows).map(row => {
                const cells = row.querySelectorAll('td');
                return {
                    Date: cells[0]?.textContent.trim() || '',
                    Type: cells[1]?.textContent.trim() || '',
                    'Quantity (L)': cells[2]?.textContent.trim() || '',
                    'Price/L': cells[3]?.textContent.trim() || '',
                    'Supplier/Vehicle': cells[4]?.textContent.trim() || '',
                    Notes: cells[5]?.textContent.trim() || ''
                };
            });

            const worksheet = XLSX.utils.json_to_sheet(excelData);
            const workbook = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(workbook, worksheet, 'Fuel Inventory');
            XLSX.writeFile(workbook, 'Fuel_Inventory_' + new Date().toISOString().split('T')[0] + '.xlsx');
            showToast('Exported to Excel successfully!');
        }

        document.getElementById('edit-fuel-type').addEventListener('change', (e) => {
            const editPriceGroup = document.getElementById('edit-price-group');
            if (e.target.value.toLowerCase().includes('received')) {
                editPriceGroup.classList.remove('hidden');
            } else {
                editPriceGroup.classList.add('hidden');
            }
        });

        // ====== Meter Reading Module Functions ======
        function openMeterReadingForm() {
            const container = document.getElementById('meter-form-container');
            if (container) container.classList.remove('hidden');
        }

        function closeMeterReadingForm() {
            const container = document.getElementById('meter-form-container');
            if (container) container.classList.add('hidden');
            const form = document.getElementById('meter-form');
            if (form) form.reset();
        }

        // ====== Generator Record Module Functions ======
        function openGeneratorForm() {
            const container = document.getElementById('generator-form-container');
            if (container) container.classList.remove('hidden');
        }

        function closeGeneratorForm() {
            const container = document.getElementById('generator-form-container');
            if (container) container.classList.add('hidden');
            const form = document.getElementById('generator-form');
            if (form) form.reset();
        }

        // ====== Equipment Module Functions ======
        function openEquipmentForm() {
            const container = document.getElementById('equipment-form-container');
            if (container) container.classList.remove('hidden');
        }

        function closeEquipmentForm() {
            const container = document.getElementById('equipment-form-container');
            if (container) container.classList.add('hidden');
            const form = document.getElementById('equipment-form');
            if (form) form.reset();
        }

        // ====== To Do Task Module Functions ======
        function openTaskForm() {
            const container = document.getElementById('task-form-container');
            if (container) container.classList.remove('hidden');
        }

        function closeTaskForm() {
            const container = document.getElementById('task-form-container');
            if (container) container.classList.add('hidden');
            const form = document.getElementById('task-form');
            if (form) form.reset();
        }

        function filterTasks(status) {
            // Task filtering logic to be implemented later
            console.log('Filter tasks by status:', status);
        }
    </script>

</body>
</html>

<?php include '../templates/footer.php'; ?>
