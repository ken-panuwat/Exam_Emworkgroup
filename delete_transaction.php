<?php
require_once 'config/connect.php';

if (isset($_GET['id'])) {
    $transactionId = $_GET['id'];

    // Delete the transaction
    $sql = "DELETE FROM transactions WHERE transaction_id = :id";
    $stmt = $condb->prepare($sql);
    $stmt->bindParam(':id', $transactionId);

    echo "<script src=\"https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js\"></script>";
    if ($stmt->execute()) {
        echo "<script>
                swal({
                    title: 'สำเร็จ!',
                    text: 'ลบรายการเรียบร้อยแล้ว!',
                    icon: 'success',
                    button: 'ตกลง'
                }).then(function() {
                    window.location.href = 'index.php'; // เปลี่ยนไปที่หน้า index
                });
              </script>";
    } else {
        echo "<script>
                swal({
                    title: 'เกิดข้อผิดพลาด!',
                    text: 'ไม่สามารถลบรายการได้!',
                    icon: 'error',
                    button: 'ตกลง'
                }).then(function() {
                    window.location.href = 'index.php'; // เปลี่ยนไปที่หน้า index
                });
              </script>";
    }
}
?>
