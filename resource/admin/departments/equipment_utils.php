<?php
// Equipment Management Functions
// Include database connection
require_once '../../db.php';

/**
 * Get all equipment records
 */
function getEquipmentRecords($conn) {
    $sql = "SELECT * FROM equipment ORDER BY created_at DESC";
    $result = $conn->query($sql);

    $equipment = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $equipment[] = $row;
        }
    }
    return $equipment;
}

/**
 * Get equipment record by ID
 */
function getEquipmentById($conn, $id) {
    $sql = "SELECT * FROM equipment WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->fetch_assoc();
}

/**
 * Save equipment record (insert or update)
 */
function saveEquipmentRecord($conn, $data) {
    if (isset($data['id']) && !empty($data['id'])) {
        // Update existing record
        $sql = "UPDATE equipment SET name = ?, type = ?, model = ?, serial_number = ?, purchase_date = ?, purchase_price = ?, location = ?, status = ?, notes = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssdsssi",
            $data['name'],
            $data['type'],
            $data['model'],
            $data['serial_number'],
            $data['purchase_date'],
            $data['purchase_price'],
            $data['location'],
            $data['status'],
            $data['notes'],
            $data['id']
        );
    } else {
        // Insert new record
        $sql = "INSERT INTO equipment (name, type, model, serial_number, purchase_date, purchase_price, location, status, notes, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssdsss",
            $data['name'],
            $data['type'],
            $data['model'],
            $data['serial_number'],
            $data['purchase_date'],
            $data['purchase_price'],
            $data['location'],
            $data['status'],
            $data['notes']
        );
    }

    $result = $stmt->execute();
    $stmt->close();

    return $result;
}

/**
 * Delete equipment record
 */
function deleteEquipmentRecord($conn, $id) {
    $sql = "DELETE FROM equipment WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $result = $stmt->execute();
    $stmt->close();

    return $result;
}

/**
 * Get equipment dashboard data
 */
function getEquipmentDashboardData($conn) {
    $data = [
        'totalEquipment' => 0,
        'activeEquipment' => 0,
        'maintenanceDue' => 0,
        'equipmentByType' => []
    ];

    // Total equipment
    $sql = "SELECT COUNT(*) as total FROM equipment";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $data['totalEquipment'] = $result->fetch_assoc()['total'];
    }

    // Active equipment
    $sql = "SELECT COUNT(*) as active FROM equipment WHERE status = 'active'";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $data['activeEquipment'] = $result->fetch_assoc()['active'];
    }

    // Equipment by type
    $sql = "SELECT type, COUNT(*) as count FROM equipment GROUP BY type";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data['equipmentByType'][] = $row;
        }
    }

    return $data;
}

/**
 * Get maintenance records for equipment
 */
function getMaintenanceRecords($conn, $equipmentId = null) {
    $sql = "SELECT m.*, e.name as equipment_name FROM equipment_maintenance m
            LEFT JOIN equipment e ON m.equipment_id = e.id";

    if ($equipmentId) {
        $sql .= " WHERE m.equipment_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $equipmentId);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $result = $conn->query($sql);
    }

    $maintenance = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $maintenance[] = $row;
        }
    }
    return $maintenance;
}

/**
 * Save maintenance record
 */
function saveMaintenanceRecord($conn, $data) {
    if (isset($data['id']) && !empty($data['id'])) {
        // Update existing record
        $sql = "UPDATE equipment_maintenance SET equipment_id = ?, maintenance_type = ?, description = ?, scheduled_date = ?, completed_date = ?, cost = ?, performed_by = ?, notes = ?, status = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issssdsssi",
            $data['equipment_id'],
            $data['maintenance_type'],
            $data['description'],
            $data['scheduled_date'],
            $data['completed_date'],
            $data['cost'],
            $data['performed_by'],
            $data['notes'],
            $data['status'],
            $data['id']
        );
    } else {
        // Insert new record
        $sql = "INSERT INTO equipment_maintenance (equipment_id, maintenance_type, description, scheduled_date, completed_date, cost, performed_by, notes, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issssdsss",
            $data['equipment_id'],
            $data['maintenance_type'],
            $data['description'],
            $data['scheduled_date'],
            $data['completed_date'],
            $data['cost'],
            $data['performed_by'],
            $data['notes'],
            $data['status']
        );
    }

    $result = $stmt->execute();
    $stmt->close();

    return $result;
}

/**
 * Delete maintenance record
 */
function deleteMaintenanceRecord($conn, $id) {
    $sql = "DELETE FROM equipment_maintenance WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $result = $stmt->execute();
    $stmt->close();

    return $result;
}

/**
 * Get upcoming maintenance
 */
function getUpcomingMaintenance($conn, $days = 30) {
    $sql = "SELECT m.*, e.name as equipment_name, DATEDIFF(m.scheduled_date, CURDATE()) as days_until
            FROM equipment_maintenance m
            LEFT JOIN equipment e ON m.equipment_id = e.id
            WHERE m.status = 'scheduled'
            AND m.scheduled_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)
            ORDER BY m.scheduled_date ASC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $days);
    $stmt->execute();
    $result = $stmt->get_result();

    $maintenance = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $maintenance[] = $row;
        }
    }
    return $maintenance;
}
?>