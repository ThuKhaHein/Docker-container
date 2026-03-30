<?php
/**
 * To Do Task Module
 * Handles task management and tracking for M&E department
 */
?>

<div class="bg-white p-6 rounded-lg shadow-md">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">To Do Task</h2>
    
    <div class="flex gap-4 mb-6">
        <button onclick="openTaskForm()" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm font-medium">
            <i class="fas fa-plus mr-2"></i>Add New Task
        </button>
        <select id="task-filter" onchange="filterTasks(this.value)" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium">
            <option value="">All Tasks</option>
            <option value="pending">Pending</option>
            <option value="in_progress">In Progress</option>
            <option value="completed">Completed</option>
            <option value="overdue">Overdue</option>
        </select>
    </div>

    <div class="bg-gray-50 p-6 rounded-lg mb-6 hidden" id="task-form-container">
        <h3 class="text-lg font-semibold mb-4 text-gray-800">Add Task</h3>
        <form id="task-form" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="task-title" class="block text-sm font-medium text-gray-700">Task Title</label>
                    <input type="text" id="task-title" name="title" required placeholder="Enter task title" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label for="task-priority" class="block text-sm font-medium text-gray-700">Priority</label>
                    <select id="task-priority" name="priority" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="low">Low</option>
                        <option value="medium" selected>Medium</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="task-due-date" class="block text-sm font-medium text-gray-700">Due Date</label>
                    <input type="date" id="task-due-date" name="due_date" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label for="task-assigned-to" class="block text-sm font-medium text-gray-700">Assigned To</label>
                    <input type="text" id="task-assigned-to" name="assigned_to" placeholder="Name or email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
            </div>
            <div>
                <label for="task-description" class="block text-sm font-medium text-gray-700">Description</label>
                <textarea id="task-description" name="description" rows="4" placeholder="Enter task details" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
            </div>
            <div class="flex justify-end space-x-4">
                <button type="button" onclick="closeTaskForm()" class="px-6 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Save Task</button>
            </div>
        </form>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-blue-50 p-4 rounded-lg">
            <h3 class="text-sm font-medium text-blue-800">Total Tasks</h3>
            <p id="task-total" class="text-2xl font-bold text-blue-900">0</p>
        </div>
        <div class="bg-yellow-50 p-4 rounded-lg">
            <h3 class="text-sm font-medium text-yellow-800">Pending</h3>
            <p id="task-pending" class="text-2xl font-bold text-yellow-900">0</p>
        </div>
        <div class="bg-purple-50 p-4 rounded-lg">
            <h3 class="text-sm font-medium text-purple-800">In Progress</h3>
            <p id="task-in-progress" class="text-2xl font-bold text-purple-900">0</p>
        </div>
        <div class="bg-green-50 p-4 rounded-lg">
            <h3 class="text-sm font-medium text-green-800">Completed</h3>
            <p id="task-completed" class="text-2xl font-bold text-green-900">0</p>
        </div>
    </div>

    <div class="space-y-4" id="task-list">
        <div class="text-center py-8 text-gray-500">
            <i class="fas fa-tasks text-3xl text-gray-300 mb-2"></i>
            <p>No tasks yet. Create one to get started.</p>
        </div>
    </div>
</div>

<style>
.task-card {
    border-left: 4px solid;
    transition: all 0.3s ease;
}
.task-card.priority-urgent {
    border-left-color: #dc2626;
}
.task-card.priority-high {
    border-left-color: #ea580c;
}
.task-card.priority-medium {
    border-left-color: #f59e0b;
}
.task-card.priority-low {
    border-left-color: #10b981;
}
.task-card:hover {
    shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
}
.status-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 600;
}
.status-pending {
    background-color: #fef3c7;
    color: #92400e;
}
.status-in_progress {
    background-color: #e9d5ff;
    color: #581c87;
}
.status-completed {
    background-color: #d1fae5;
    color: #065f46;
}
.status-overdue {
    background-color: #fee2e2;
    color: #7f1d1d;
}
</style>
