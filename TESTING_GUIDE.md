# M&E Portal Navigation Testing Guide

## Overview
The M&E Portal has been refactored into a modular architecture with separate PHP files for each section. All tabs should now be clickable and show the appropriate content.

## Expected Behavior

### Navigation Tabs (Top of Page)
The portal has 6 main tabs:
1. **Dashboard** - Shows statistics, charts, and administrative overview
2. **Fuel Inventory Control** - Fuel management with add form and inventory table
3. **Meter Reading** - Meter reading tracking system
4. **Generator Record** - Generator operation and maintenance logging
5. **Equipment** - Equipment inventory and status management
6. **To Do Task** - Task management system with filtering

### Tab Interaction Flow

#### When User Clicks a Tab:
1. The `showPage()` function is called with the page ID and button element
2. All pages (with class `.page`) are hidden by adding the `hidden` class
3. The selected page is shown by removing the `hidden` class
4. The button styling updates to show it's active:
   - Active: `text-blue-600` + `border-b-2 border-blue-600`
   - Inactive: `text-gray-600` + `border-b-2 border-transparent`

#### Each Module Contains:
- **Header** with module title and action buttons
- **Form Container** (hidden by default) for adding new records
  - Can be opened with button click
  - Can be closed with "Cancel" button
- **Statistics Cards** showing key metrics for that section
- **Data Table** displaying records for that section
- **Filtering/Export Options** (where applicable)

## Module Descriptions

### 1. Dashboard
- Location: Embedded in me.php starting at line 226
- Features:
  - Current diesel stock (FIFO calculated)
  - Total fuel received
  - Total fuel usage
  - FIFO method status indicator
  - Date range filters
  - Monthly usage chart
  - Equipment usage breakdown
  - Supplier distribution chart
  - Export to PDF/Excel

### 2. Fuel Inventory Control
- Location: modules/fuel_inventory_control.php
- Features:
  - Add new fuel records form
  - Transaction type selector (Received/Used)
  - Conditional supplier/vehicle field display
  - Inventory records table with filtering
  - Edit/Delete functionality
  - Export capabilities (PDF & Excel)

### 3. Meter Reading
- Location: modules/meter_reading.php
- Features:
  - Date and equipment name fields
  - Meter reading value (in hours/units)
  - Notes field
  - Reading table with date, equipment, value, and notes
  - Total readings count
  - Edit/Delete actions

### 4. Generator Record
- Location: modules/generator_record.php
- Features:
  - Date and time fields
  - Record type selector (Startup/Shutdown/Maintenance/Inspection)
  - Runtime hours tracking
  - Remarks/notes field
  - Total records and runtime statistics
  - Complete operational log table

### 5. Equipment
- Location: modules/equipment.php
- Features:
  - Equipment name and code fields
  - Category selector (Generator/Pump/Compressor/Engine/Others)
  - Status tracking (Active/Maintenance/Inactive)
  - Installation date and location
  - Key metrics: Total equipment, Active, Under Maintenance, Inactive
  - Comprehensive equipment table with all details

### 6. To Do Task
- Location: modules/todo_task.php
- Features:
  - Task title and priority selector
  - Due date and assignment fields
  - Task description
  - Status filtering (All/Pending/In Progress/Completed/Overdue)
  - Task cards with color-coded priority levels
  - Task statistics dashboard

## Testing Checklist

### Navigation Tests
- [ ] Click Dashboard tab - should show dashboard content
- [ ] Click Fuel Inventory Control tab - should show fuel form and table
- [ ] Click Meter Reading tab - should show meter reading form and table
- [ ] Click Generator Record tab - should show generator form and log
- [ ] Click Equipment tab - should show equipment form and inventory
- [ ] Click To Do Task tab - should show task management interface
- [ ] Tab styling changes when clicked (blue border appears)
- [ ] Previous page is hidden when new tab is clicked

### Form Tests (For Each Module)
- [ ] "Add New [Section]" button opens the form
  - Button in Meter Reading: "Add New Meter Reading"
  - Button in Generator Record: "Add New Generator Record"
  - Button in Equipment: "Add New Equipment"
  - Button in To Do Task: "Add New Task"
- [ ] "Cancel" button closes the form without saving
- [ ] Form resets after closing

### UI/UX Tests
- [ ] All icons display correctly (Font Awesome)
- [ ] Tailwind CSS classes render properly (colors, spacing, layout)
- [ ] Forms are responsive (look good on mobile/tablet/desktop)
- [ ] Tables are responsive with horizontal scroll on small screens
- [ ] Color scheme is consistent with M&E Portal branding

### Console Tests (Browser Developer Tools - F12)
- [ ] No JavaScript errors in console
- [ ] No 404 errors for module includes
- [ ] showPage() function logs can be verified with console.log()

## Quick Test Steps

1. **Open M&E Portal in Browser**
   - Navigate to: `/admin/departments/me.php`
   
2. **Test Each Tab in Sequence**
   - Start with Dashboard (should be default)
   - Click Fuel Inventory Control → verify content changes
   - Click Meter Reading → verify content changes
   - Click Generator Record → verify content changes
   - Click Equipment → verify content changes
   - Click To Do Task → verify content changes
   - Navigate back to Dashboard to ensure cycling works

3. **Test Form Interactions**
   - In each tab, click "Add New [Item]" button
   - Verify form appears
   - Click "Cancel" button
   - Verify form disappears
   - Repeat for each module

4. **Check Browser Console**
   - Open Developer Tools (F12)
   - Go to Console tab
   - Look for any red error messages
   - All should be clean

## Known Limitations (To Be Implemented in Next Phase)

- ✗ AJAX form submission not yet implemented
- ✗ Database persistence not yet implemented
- ✗ Data validation on client side only
- ✗ No authentication checks on module access
- ✗ Charts and statistics not yet hooked to new modules
- ✗ Export functions only available for Fuel Inventory

## File Structure Reference

```
/resource/admin/departments/
├── me.php                          [MAIN FILE - Navigation & Dashboard]
├── modules/
│   ├── fuel_inventory_control.php  [Fuel Management Module]
│   ├── meter_reading.php           [Meter Tracking Module]
│   ├── generator_record.php        [Generator Management Module]
│   ├── equipment.php               [Equipment Inventory Module]
│   └── todo_task.php               [Task Management Module]
├── diesel_utils.php                [Fuel-related backend functions]
├── equipment_utils.php             [Equipment-related backend functions]
└── [Other supporting files]
```

## JavaScript Functions Available

### Page Navigation
- `showPage(pageId, buttonElement)` - Main navigation function

### Module Form Control
- `openMeterReadingForm()` / `closeMeterReadingForm()`
- `openGeneratorForm()` / `closeGeneratorForm()`
- `openEquipmentForm()` / `closeEquipmentForm()`
- `openTaskForm()` / `closeTaskForm()`

### Task Filtering
- `filterTasks(status)` - Filter tasks by status

### Fuel Inventory (Existing Functions)
- `loadInventoryData()` - Load fuel records
- `renderTable()` - Display records in table
- `applyListFilters()` - Apply filters to fuel inventory
- `resetListFilters()` - Clear all filters
- `openEditModal()` / `closeEditModal()` 
- `deleteRecord()` 
- `exportInventoryPDF()` / `exportInventoryExcel()`

## Success Criteria

✅ All 6 tabs are clickable and functional
✅ Tab styling changes when active
✅ Content switches correctly between tabs
✅ Each module displays its form and table
✅ Forms can be opened and closed
✅ No JavaScript errors in console
✅ Responsive design works on all screen sizes
✅ Icons and styling are consistent
✅ Page title shows "M&E Portal"

---
*Last Updated: [Current Date]*
*Status: Ready for Testing*
