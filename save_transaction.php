<?php
require_once 'config/connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Receive values from the form
    $type = $_POST['type'];
    $item_name = $_POST['item_name'];
    $amount = $_POST['amount'];
    $spending_date = $_POST['spending_date'];

    // Prepare SQL for inserting data
    $sql = "INSERT INTO transactions (type, item_name, amount, spending_date)
            VALUES (:type, :item_name, :amount, :spending_date)";

    // Prepare statement and bind parameters
    $stmt = $condb->prepare($sql);
    $stmt->bindParam(':type', $type);
    $stmt->bindParam(':item_name', $item_name);
    $stmt->bindParam(':amount', $amount);
    $stmt->bindParam(':spending_date', $spending_date);

    // Execute query and prepare response
    $response = [];
    if ($stmt->execute()) {
        // Success
        $response['success'] = true;
    } else {
        // Error
        $response['success'] = false;
    }

    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode($response);
}
?>