<?php 
require_once 'config/connect.php';

$response = array(); // Initialize response array

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve POST data
    $transactionId = $_POST['transaction_id'];
    $type = $_POST['edit_type'];
    $itemName = $_POST['edit_item_name'];
    $amount = $_POST['edit_amount'];
    $spendingDate = $_POST['edit_spending_date'];

    // Get the current timestamp for updating the record
    $updatedAt = date('Y-m-d H:i:s');

    // SQL query to update the transaction
    $sql = "UPDATE transactions 
            SET type = :type, 
                item_name = :item_name, 
                amount = :amount, 
                spending_date = :spending_date, 
                updated_at = :updated_at 
            WHERE transaction_id = :transaction_id";
    $stmt = $condb->prepare($sql);
    $stmt->bindParam(':type', $type);
    $stmt->bindParam(':item_name', $itemName);
    $stmt->bindParam(':amount', $amount);
    $stmt->bindParam(':spending_date', $spendingDate);
    $stmt->bindParam(':updated_at', $updatedAt);
    $stmt->bindParam(':transaction_id', $transactionId);

    // Execute the update and prepare response
    if ($stmt->execute()) {
        $response['success'] = true; // Update successful
    } else {
        $response['success'] = false; // Update failed
        $response['message'] = 'ไม่สามารถอัปเดตได้!'; // Error message
    }
} else {
    $response['success'] = false; // Not a POST request
    $response['message'] = 'ผิดพลาดในการรับข้อมูล!';
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
