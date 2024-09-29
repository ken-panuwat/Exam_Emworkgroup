<?php 
require_once 'config/connect.php';

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

    echo "<script src=\"https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js\"></script>";
    // Execute the update and handle success or failure
    if ($stmt->execute()) {
        // Redirect to index.php on success using SweetAlert
        echo "<script>
                swal({
                    title: 'สำเร็จ!',
                    text: 'อัปเดตรายการเรียบร้อยแล้ว!',
                    icon: 'success',
                    button: 'ตกลง'
                }).then(function() {
                    window.location.href = 'index.php'; // Redirect to index.php
                });
            </script>";
    } else {
        // Go back to the previous page on failure using SweetAlert
        echo "<script>
                swal({
                    title: 'ผิดพลาด!',
                    text: 'ไม่สามารถอัปเดตได้!',
                    icon: 'error',
                    button: 'ตกลง'
                }).then(function() {
                    window.history.back(); // Go back to the previous page
                });
            </script>";
    }
    }

// Fetching transaction details for the edit form, if needed
if (isset($_GET['id'])) {
    $transactionId = $_GET['id'];
    $sql = "SELECT * FROM transactions WHERE transaction_id = :id";
    $stmt = $condb->prepare($sql);
    $stmt->bindParam(':id', $transactionId);
    $stmt->execute();
    $transaction = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>