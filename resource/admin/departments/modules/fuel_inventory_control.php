<?php
/**
 * Fuel Inventory Control Module
 * Handles all fuel inventory operations including CRUD and FIFO calculations
 */
?>

<!-- Add New Entry Form -->
<div class="bg-gray-50 p-6 rounded-lg shadow-md mb-6">
    <h3 class="text-xl font-semibold mb-4 text-gray-800">Add New Entry</h3>
    <form id="add-entry-form" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="date" class="block text-sm font-medium text-gray-700">Date</label>
                <input type="date" id="date" name="date" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div>
                <label for="type" class="block text-sm font-medium text-gray-700">Transaction Type</label>
                <select id="type" name="type" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="received">Fuel Received</option>
                    <option value="used">Fuel Used</option>
                </select>
            </div>
        </div>
        <div id="supplier-group" class="hidden">
            <label for="supplier" class="block text-sm font-medium text-gray-700">Supplier</label>
            <input type="text" id="supplier" name="supplier" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>
        <div id="vehicle-group" class="hidden">
            <label for="vehicle" class="block text-sm font-medium text-gray-700">Vehicle/Equipment</label>
            <input type="text" id="vehicle" name="vehicle" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="quantity" class="block text-sm font-medium text-gray-700">Quantity (Liters)</label>
                <input type="number" id="quantity" name="quantity" min="0.01" step="0.01" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div id="price-group" class="hidden">
                <label for="price" class="block text-sm font-medium text-gray-700">Price per Liter (Kyats)</label>
                <input type="number" id="price" name="price" min="0.01" step="0.01" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
        </div>
        <div>
            <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
            <textarea id="notes" name="notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
        </div>
        <div class="flex justify-end space-x-4">
            <button type="reset" class="px-6 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">Clear</button>
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Add Entry</button>
        </div>
    </form>
</div>

<!-- Inventory Records -->
<div class="bg-white p-6 rounded-lg shadow-md">
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-xl font-semibold text-gray-800">Inventory Records</h3>
        <div class="flex space-x-2">
            <button onclick="exportInventoryPDF()" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 text-sm font-medium">
                <i class="fas fa-file-pdf mr-2"></i>Export PDF
            </button>
            <button onclick="exportInventoryExcel()" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 text-sm font-medium">
                <i class="fas fa-file-excel mr-2"></i>Export Excel
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="mb-6 p-4 border border-gray-200 rounded-lg">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 items-end">
            <div>
                <label for="list-start-date" class="block text-sm font-medium text-gray-700">Start Date</label>
                <input type="date" id="list-start-date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            </div>
            <div>
                <label for="list-end-date" class="block text-sm font-medium text-gray-700">End Date</label>
                <input type="date" id="list-end-date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            </div>
            <div>
                <label for="list-type-filter" class="block text-sm font-medium text-gray-700">Type</label>
                <select id="list-type-filter" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="">All</option>
                    <option value="received">Received</option>
                    <option value="used">Used</option>
                </select>
            </div>
            <div class="flex space-x-2">
                <button onclick="applyListFilters()" class="flex-1 py-2 px-4 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm font-medium">Filter</button>
                <button onclick="resetListFilters()" class="flex-1 py-2 px-4 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 text-sm font-medium">Reset</button>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantity (L)</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price/L</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Supplier/Vehicle</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Notes</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody id="inventory-table-body" class="bg-white divide-y divide-gray-200">
                <!-- Data loaded via JavaScript -->
            </tbody>
        </table>
    </div>
</div>
