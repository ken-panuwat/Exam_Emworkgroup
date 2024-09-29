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

    // Execute query
    if ($stmt->execute()) {
        // Show success pop-up
        echo "<script src=\"https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js\"></script>";
        echo "<script>
                swal({
                    title: 'สำเร็จ!',
                    text: 'บันทึกรายการสำเร็จ!',
                    icon: 'success',
                    button: 'ตกลง'
                }).then(function() {
                    window.location.href = 'index.php'; // Redirect to index
                });
            </script>";
    } else {
        // Show error pop-up
        echo "<script src=\"https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js\"></script>";
        echo "<script>
                swal({
                    title: 'เกิดข้อผิดพลาด!',
                    text: 'ไม่สามารถบันทึกรายการได้!',
                    icon: 'error',
                    button: 'ตกลง'
                }).then(function() {
                    window.location.href = 'index.php'; // Redirect to index
                });
            </script>";
    }
}
?>
