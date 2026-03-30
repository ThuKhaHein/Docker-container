<?php
/**
 * Generator Record Module
 * Handles generator operation and maintenance records
 */
?>

<div class="bg-white p-6 rounded-lg shadow-md">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">Generator Record</h2>
    
    <div class="mb-6">
        <button onclick="openGeneratorForm()" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm font-medium">
            <i class="fas fa-plus mr-2"></i>Add New Generator Record
        </button>
    </div>

    <div class="bg-gray-50 p-6 rounded-lg mb-6 hidden" id="generator-form-container">
        <h3 class="text-lg font-semibold mb-4 text-gray-800">Add Generator Record</h3>
        <form id="generator-form" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="generator-date" class="block text-sm font-medium text-gray-700">Date</label>
                    <input type="date" id="generator-date" name="date" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label for="generator-time" class="block text-sm font-medium text-gray-700">Time</label>
                    <input type="time" id="generator-time" name="time" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="generator-type" class="block text-sm font-medium text-gray-700">Record Type</label>
                    <select id="generator-type" name="type" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Select Type</option>
                        <option value="startup">Startup</option>
                        <option value="shutdown">Shutdown</option>
                        <option value="maintenance">Maintenance</option>
                        <option value="inspection">Inspection</option>
                    </select>
                </div>
                <div>
                    <label for="generator-runtime" class="block text-sm font-medium text-gray-700">Runtime (Hours)</label>
                    <input type="number" id="generator-runtime" name="runtime" min="0" step="0.5" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
            </div>
            <div>
                <label for="generator-notes" class="block text-sm font-medium text-gray-700">Remarks/Notes</label>
                <textarea id="generator-notes" name="notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
            </div>
            <div class="flex justify-end space-x-4">
                <button type="button" onclick="closeGeneratorForm()" class="px-6 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Save</button>
            </div>
        </form>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-green-50 p-4 rounded-lg">
            <h3 class="text-sm font-medium text-green-800">Total Records</h3>
            <p id="generator-total" class="text-2xl font-bold text-green-900">0</p>
        </div>
        <div class="bg-yellow-50 p-4 rounded-lg">
            <h3 class="text-sm font-medium text-yellow-800">Total Runtime</h3>
            <p id="generator-runtime-total" class="text-2xl font-bold text-yellow-900">0 hrs</p>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Runtime</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Notes</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody id="generator-table-body" class="bg-white divide-y divide-gray-200">
                <tr>
                    <td colspan="6" class="text-center py-4 text-gray-500">No generator records yet.</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
