<?php
require_once '../config/config.php';
requireAuth();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCSRF();
    
    $order = json_decode($_POST['order'] ?? '[]', true);
    
    if (!empty($order)) {
        $pdo = getDBConnection();
        
        try {
            $pdo->beginTransaction();
            
            $stmt = $pdo->prepare("UPDATE fields SET display_order = ? WHERE id = ?");
            
            foreach ($order as $item) {
                $stmt->execute([$item['order'], $item['id']]);
            }
            
            $pdo->commit();
            
            echo json_encode(['success' => true, 'message' => 'Order saved']);
        } catch (PDOException $e) {
            $pdo->rollBack();
            error_log("Field order update error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Failed to save order']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid order data']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
