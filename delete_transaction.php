<?php
require_once 'config/connect.php';

header('Content-Type: application/json');

// Initialize response array
$response = [];

// Check if the transaction ID is provided in the POST data
if (isset($_POST['id'])) {
    $transactionId = $_POST['id']; // Get the transaction ID from POST data

    // Delete the transaction
    $sql = "DELETE FROM transactions WHERE transaction_id = :id";
    $stmt = $condb->prepare($sql);
    $stmt->bindParam(':id', $transactionId);

    if ($stmt->execute()) {
        $response['success'] = true; // Set success to true if deletion is successful
    } else {
        $response['error'] = 'Failed to execute statement'; // Optional: add a specific error message
    }
} else {
    $response['error'] = 'Transaction ID not provided'; // Optional: error if ID is not set
}

echo json_encode($response);
?>
