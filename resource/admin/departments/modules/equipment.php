<?php
/**
 * Equipment Module
 * Handles equipment inventory, maintenance tracking, and lifecycle
 */
?>

<div class="bg-white p-6 rounded-lg shadow-md">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">Equipment</h2>
    
    <div class="mb-6">
        <button onclick="openEquipmentForm()" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm font-medium">
            <i class="fas fa-plus mr-2"></i>Add New Equipment
        </button>
    </div>

    <div class="bg-gray-50 p-6 rounded-lg mb-6 hidden" id="equipment-form-container">
        <h3 class="text-lg font-semibold mb-4 text-gray-800">Add Equipment</h3>
        <form id="equipment-form" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="equipment-name" class="block text-sm font-medium text-gray-700">Equipment Name</label>
                    <input type="text" id="equipment-name" name="name" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label for="equipment-code" class="block text-sm font-medium text-gray-700">Equipment Code/ID</label>
                    <input type="text" id="equipment-code" name="code" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="equipment-category" class="block text-sm font-medium text-gray-700">Category</label>
                    <select id="equipment-category" name="category" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Select Category</option>
                        <option value="generator">Generator</option>
                        <option value="pump">Pump</option>
                        <option value="compressor">Compressor</option>
                        <option value="engine">Engine</option>
                        <option value="others">Others</option>
                    </select>
                </div>
                <div>
                    <label for="equipment-status" class="block text-sm font-medium text-gray-700">Status</label>
                    <select id="equipment-status" name="status" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="active">Active</option>
                        <option value="maintenance">Under Maintenance</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="equipment-date" class="block text-sm font-medium text-gray-700">Installation Date</label>
                    <input type="date" id="equipment-date" name="date" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label for="equipment-location" class="block text-sm font-medium text-gray-700">Location</label>
                    <input type="text" id="equipment-location" name="location" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
            </div>
            <div>
                <label for="equipment-notes" class="block text-sm font-medium text-gray-700">Remarks</label>
                <textarea id="equipment-notes" name="notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
            </div>
            <div class="flex justify-end space-x-4">
                <button type="button" onclick="closeEquipmentForm()" class="px-6 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Save</button>
            </div>
        </form>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-blue-50 p-4 rounded-lg">
            <h3 class="text-sm font-medium text-blue-800">Total Equipment</h3>
            <p id="equipment-total" class="text-2xl font-bold text-blue-900">0</p>
        </div>
        <div class="bg-green-50 p-4 rounded-lg">
            <h3 class="text-sm font-medium text-green-800">Active</h3>
            <p id="equipment-active" class="text-2xl font-bold text-green-900">0</p>
        </div>
        <div class="bg-yellow-50 p-4 rounded-lg">
            <h3 class="text-sm font-medium text-yellow-800">Maintenance</h3>
            <p id="equipment-maintenance" class="text-2xl font-bold text-yellow-900">0</p>
        </div>
        <div class="bg-red-50 p-4 rounded-lg">
            <h3 class="text-sm font-medium text-red-800">Inactive</h3>
            <p id="equipment-inactive" class="text-2xl font-bold text-red-900">0</p>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Equipment Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Location</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody id="equipment-table-body" class="bg-white divide-y divide-gray-200">
                <tr>
                    <td colspan="6" class="text-center py-4 text-gray-500">No equipment records yet.</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
