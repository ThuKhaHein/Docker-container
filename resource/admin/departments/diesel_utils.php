<?php

// Diesel Inventory Utility Functions

function initializeDieselDatabase($conn) {
    $sql = "CREATE TABLE IF NOT EXISTS diesel_inventory (
        id VARCHAR(36) PRIMARY KEY,
        timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
        date DATE NOT NULL,
        transaction_type VARCHAR(50) NOT NULL,
        quantity_liters DECIMAL(10, 2) NOT NULL,
        price_per_liter DECIMAL(10, 2),
        source_or_destination VARCHAR(255) NOT NULL,
        notes TEXT,
        INDEX idx_date (date),
        INDEX idx_type (transaction_type)
    )";
    
    if (!$conn->query($sql)) {
        error_log("Error creating diesel_inventory table: " . $conn->error);
    }
}

function generateUUID() {
    return sprintf(
        '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}

function getDieselRecords($conn) {
    initializeDieselDatabase($conn);
    
    $sql = "SELECT * FROM diesel_inventory ORDER BY date DESC, timestamp DESC";
    $result = $conn->query($sql);
    
    if (!$result || $result->num_rows === 0) {
        return [];
    }
    
    $records = [];
    while ($row = $result->fetch_assoc()) {
        $records[] = [
            'id' => $row['id'],
            'timestamp' => $row['timestamp'],
            'date' => $row['date'],
            'transactionType' => $row['transaction_type'],
            'quantityLiters' => floatval($row['quantity_liters']),
            'pricePerLiter' => $row['price_per_liter'] ? floatval($row['price_per_liter']) : null,
            'sourceOrDestination' => $row['source_or_destination'],
            'notes' => $row['notes']
        ];
    }
    
    return array_reverse($records);
}

function saveDieselRecord($conn, $record) {
    initializeDieselDatabase($conn);
    
    try {
        if (!empty($record['id'])) {
            // UPDATE
            $id = $conn->real_escape_string($record['id']);
            $date = $conn->real_escape_string($record['date']);
            $type = $conn->real_escape_string($record['transactionType']);
            $quantity = floatval($record['quantityLiters']);
            $price = !empty($record['pricePerLiter']) ? floatval($record['pricePerLiter']) : null;
            $source = $conn->real_escape_string($record['sourceOrDestination']);
            $notes = $conn->real_escape_string($record['notes'] ?? '');
            
            $sql = "UPDATE diesel_inventory SET 
                    date='$date', 
                    transaction_type='$type', 
                    quantity_liters=$quantity, 
                    price_per_liter=" . ($price !== null ? $price : "NULL") . ", 
                    source_or_destination='$source', 
                    notes='$notes' 
                    WHERE id='$id'";
            
            if ($conn->query($sql)) {
                return ['status' => 'success', 'message' => 'Record updated successfully.'];
            } else {
                throw new Exception($conn->error);
            }
        } else {
            // CREATE
            $newId = generateUUID();
            $date = $conn->real_escape_string($record['date']);
            $type = $conn->real_escape_string($record['transactionType']);
            $quantity = floatval($record['quantityLiters']);
            $price = !empty($record['pricePerLiter']) ? floatval($record['pricePerLiter']) : null;
            $source = $conn->real_escape_string($record['sourceOrDestination']);
            $notes = $conn->real_escape_string($record['notes'] ?? '');
            
            $sql = "INSERT INTO diesel_inventory 
                    (id, date, transaction_type, quantity_liters, price_per_liter, source_or_destination, notes) 
                    VALUES 
                    ('$newId', '$date', '$type', $quantity, " . ($price !== null ? $price : "NULL") . ", '$source', '$notes')";
            
            if ($conn->query($sql)) {
                return ['status' => 'success', 'message' => 'Record added successfully.'];
            } else {
                throw new Exception($conn->error);
            }
        }
    } catch (Exception $e) {
        return ['status' => 'error', 'message' => $e->getMessage()];
    }
}

function deleteDieselRecord($conn, $id) {
    try {
        $id = $conn->real_escape_string($id);
        $sql = "DELETE FROM diesel_inventory WHERE id='$id'";
        
        if ($conn->query($sql)) {
            return ['status' => 'success', 'message' => 'Record deleted successfully.'];
        } else {
            throw new Exception($conn->error);
        }
    } catch (Exception $e) {
        return ['status' => 'error', 'message' => $e->getMessage()];
    }
}

function getDieselDashboardData($conn, $filters = []) {
    initializeDieselDatabase($conn);
    
    $where = "1=1";
    
    if (!empty($filters['startDate'])) {
        $startDate = $conn->real_escape_string($filters['startDate']);
        $where .= " AND date >= '$startDate'";
    }
    
    if (!empty($filters['endDate'])) {
        $endDate = $conn->real_escape_string($filters['endDate']);
        $where .= " AND date <= '$endDate'";
    }
    
    if (!empty($filters['sourceDest'])) {
        $sourceDest = $conn->real_escape_string($filters['sourceDest']);
        $where .= " AND source_or_destination = '$sourceDest'";
    }
    
    $sql = "SELECT * FROM diesel_inventory WHERE $where ORDER BY date DESC, timestamp DESC";
    $result = $conn->query($sql);
    
    $data = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }
    
    // Recent Activity (last 5)
    $recentActivity = [];
    for ($i = 0; $i < min(5, count($data)); $i++) {
        $recentActivity[] = [
            'date' => $data[$i]['date'],
            'type' => $data[$i]['transaction_type'],
            'quantity' => floatval($data[$i]['quantity_liters']),
            'sourceDest' => $data[$i]['source_or_destination']
        ];
    }
    
    // Scorecards
    $allRecords = getDieselRecords($conn);
    $overallReceived = 0;
    $overallUsage = 0;
    
    foreach ($allRecords as $record) {
        if ($record['transactionType'] === 'Diesel Received') {
            $overallReceived += $record['quantityLiters'];
        } else if ($record['transactionType'] === 'Diesel Usage') {
            $overallUsage += $record['quantityLiters'];
        }
    }
    
    $currentStock = $overallReceived - $overallUsage;
    
    // Filtered calculations
    $totalReceived = 0;
    $totalUsage = 0;
    $usageCount = 0;
    $monthlyUsage = [];
    $usageByEquipment = [];
    $receivedBySupplier = [];
    
    foreach ($data as $record) {
        $type = $record['transaction_type'];
        $quantity = floatval($record['quantity_liters']);
        $sourceDest = $record['source_or_destination'];
        
        if ($type === 'Diesel Received') {
            $totalReceived += $quantity;
            $receivedBySupplier[$sourceDest] = ($receivedBySupplier[$sourceDest] ?? 0) + $quantity;
        } else if ($type === 'Diesel Usage') {
            $totalUsage += $quantity;
            $usageCount++;
            $usageByEquipment[$sourceDest] = ($usageByEquipment[$sourceDest] ?? 0) + $quantity;
            
            $monthYear = date('M Y', strtotime($record['date']));
            $monthlyUsage[$monthYear] = ($monthlyUsage[$monthYear] ?? 0) + $quantity;
        }
    }
    
    // Sort months chronologically
    uksort($monthlyUsage, function($a, $b) {
        return strtotime($a) - strtotime($b);
    });
    
    $avgUsage = $usageCount > 0 ? $totalUsage / $usageCount : 0;
    
    return [
        'scorecards' => [
            'currentStock' => number_format($currentStock, 2),
            'totalReceived' => number_format($totalReceived, 2),
            'totalUsage' => number_format($totalUsage, 2),
            'avgUsage' => number_format($avgUsage, 2)
        ],
        'monthlyUsage' => [
            'labels' => array_keys($monthlyUsage),
            'data' => array_values($monthlyUsage)
        ],
        'usageByEquipment' => [
            'labels' => array_keys($usageByEquipment),
            'data' => array_values($usageByEquipment)
        ],
        'receivedBySupplier' => [
            'labels' => array_keys($receivedBySupplier),
            'data' => array_values($receivedBySupplier)
        ],
        'recentActivity' => $recentActivity
    ];
}

?>
