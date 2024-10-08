<?php
require_once 'config/connect.php';

$thaiMonthMap = [
    'มกราคม' => 1, 'กุมภาพันธ์' => 2, 'มีนาคม' => 3, 'เมษายน' => 4,
    'พฤษภาคม' => 5, 'มิถุนายน' => 6, 'กรกฎาคม' => 7, 'สิงหาคม' => 8,
    'กันยายน' => 9, 'ตุลาคม' => 10, 'พฤศจิกายน' => 11, 'ธันวาคม' => 12
];

$searchMonth = '';
if (isset($_POST['search_month'])) {
    $searchMonth = $_POST['search_month'];

    // Convert Thai month name to number
    $searchMonth = isset($thaiMonthMap[$searchMonth]) ? $thaiMonthMap[$searchMonth] : '';
}

// Query to fetch transactions
$sql = "SELECT * FROM transactions" . ($searchMonth ? " WHERE MONTH(spending_date) = :month" : "");
$stmt = $condb->prepare($sql);
$stmt->bindParam(':month', $searchMonth);
$stmt->execute();
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

function formatThaiDate($date) {
    $dateTime = new DateTime($date);
    $day = $dateTime->format('j');
    $month = $dateTime->format('n');
    $year = $dateTime->format('Y') + 543;

    $thaiMonths = [
        1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม', 4 => 'เมษายน',
        5 => 'พฤษภาคม', 6 => 'มิถุนายน', 7 => 'กรกฎาคม', 8 => 'สิงหาคม',
        9 => 'กันยายน', 10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม'
    ];

    return $day . ' ' . $thaiMonths[$month] . ' ' . $year;
}

$sqlGraphData = "SELECT 
                    MONTH(spending_date) AS month, 
                    SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) AS total_income,
                    SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) AS total_expense
                 FROM transactions
                 GROUP BY MONTH(spending_date)";
$stmtGraph = $condb->prepare($sqlGraphData);
$stmtGraph->execute();
$graphData = $stmtGraph->fetchAll(PDO::FETCH_ASSOC);

// Prepare data for JavaScript
$months = [];
$incomeData = [];
$expenseData = [];



$thaiMonths = [
  1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม', 4 => 'เมษายน',
  5 => 'พฤษภาคม', 6 => 'มิถุนายน', 7 => 'กรกฎาคม', 8 => 'สิงหาคม',
  9 => 'กันยายน', 10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม'
];

foreach ($graphData as $data) {
  $months[] = $thaiMonths[$data['month']];
   
  $incomeData[] = $data['total_income'];
  $expenseData[] = $data['total_expense'];
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Income & Expense Tracking</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f8f9fa;
        display: flex;
        justify-content: flex-start;
        align-items: flex-start;
        height: auto;
        margin: 0;
        padding: 20px;
    }

    .card {
        background-color: white;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        width: 450px;
        padding: 20px;
        margin-right: 20px;
        margin-left: 20px;
        margin-bottom: 20px;
    }

    .card-header {
        margin-bottom: 20px;
    }

    .form-group {
        margin-bottom: 15px;
    }

    .form-control {
        width: 100%;
        padding: 10px;
        border: 1px solid #ced4da;
        border-radius: 4px;
        box-sizing: border-box;
    }

    .button-group {
        display: flex;
        justify-content: space-between;
        margin-top: 15px;
    }

    .btn {
        flex: 1;
        margin-right: 10px;
        padding: 12px;
        font-size: 16px;
        border: none;
    }

    .btn:last-child {
        margin-right: 0;
    }

    .btn-primary {
        background-color: #007bff;
        color: white;
    }

    .btn-secondary {
        background-color: #6c757d;
        color: white;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }

    th,
    td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
    }

    th {
        background-color: #f2f2f2;
    }

    .icon-button {
        width: 30px;
        height: 30px;
        display: inline-block;
    }

    .icon-button img {
        width: 100%;
        height: auto;
    }

    .modal {
        display: none;
        position: fixed;
        z-index: 1;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgb(0, 0, 0);
        background-color: rgba(0, 0, 0, 0.4);
        padding-top: 60px;
    }

    .modal-content {
        background-color: #fefefe;
        margin: 5% auto;
        padding: 20px;
        border: 1px solid #888;
        width: 80%;
    }

    .close {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
    }

    .close:hover,
    .close:focus {
        color: black;
        text-decoration: none;
        cursor: pointer;
    }
    </style>
</head>

<body>
    <!-- บันทึกรายการ รายรับ/รายจ่าย -->
    <div class="card">
        <div class="card-header">
            <h3>บันทึกรายการ รายรับ/รายจ่าย</h3>
        </div>
        <div class="card-body">
            <form id="transactionForm" method="POST">
                <div class="form-group">
                    <label for="type">ประเภท</label>
                    <select id="type" name="type" class="form-control" required>
                        <option value="">เลือกประเภทรายการ</option>
                        <option value="income">รายรับ</option>
                        <option value="expense">รายจ่าย</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="item_name">ชื่อรายการ</label>
                    <input type="text" id="item_name" name="item_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="amount">จำนวนเงิน</label>
                    <input type="number" step="0.01" id="amount" name="amount" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="spending_date">วันที่ใช้จ่าย</label>
                    <input type="date" id="spending_date" name="spending_date" class="form-control" required>
                </div>
                <div class="button-group">
                    <button type="submit" class="btn btn-primary">บันทึก</button>
                    <button type="reset" class="btn btn-secondary">ยกเลิก</button>
                </div>
            </form>
        </div>
    </div>

    <!-- แสดงผลข้อมูลรายการ รายรับ/รายจ่าย-->
    <div class="card" style="width: 1000px; margin-right: auto;">
        <div class="card-header">
            <h3>แสดงผลข้อมูลรายการ รายรับ/รายจ่าย</h3>
        </div>
        <div class="card-body">
            <form method="POST">
                <label for="search_month">ค้นหา</label>
                <input type="text" id="search_month" name="search_month" placeholder="ระบุเดือน" required>
                <button type="submit" class="btn btn-primary">ค้นหา</button>
                <button type="button" class="btn btn-secondary" style="background-color: red; color: white;"
                    onclick="clearForm()">clear</button>
            </form>

            <table>
                <thead>
                    <tr>
                        <th style="text-align: center;">ลำดับ</th>
                        <th style="text-align: center;">ประเภท</th>
                        <th style="text-align: center;">ชื่อรายการ</th>
                        <th style="text-align: center;">จำนวนเงิน</th>
                        <th style="text-align: center;">วันที่ใช้จ่าย</th>
                        <th style="text-align: center;">แก้ไข</th>
                        <th style="text-align: center;">ลบ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($transactions): ?>
                    <?php foreach ($transactions as $transaction): ?>
                    <tr>
                        <td><?php echo $transaction['transaction_id']; ?></td>
                        <td><?php echo $transaction['type'] === 'income' ? 'รายรับ' : 'รายจ่าย'; ?></td>
                        <td><?php echo $transaction['item_name']; ?></td>
                        <td><?php echo number_format($transaction['amount'], 2); ?></td>
                        <td><?php echo formatThaiDate($transaction['spending_date']); ?></td>
                        <td style="text-align: center;">
                            <button class="icon-button"
                                onclick="openEditModal(<?php echo htmlspecialchars(json_encode($transaction)); ?>)">
                                <i class="fas fa-edit"></i>
                            </button>
                        </td>
                        <td style="text-align: center;">
                            <button class="icon-button"
                                onclick="deleteTransaction(<?php echo $transaction['transaction_id']; ?>)">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align: center;">ไม่มีรายการ</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content" style="width: 400px; margin: auto;">
            <span class="close" onclick="closeEditModal()">&times;</span>
            <h2>แก้ไขรายการ</h2>
            <form id="editForm" onsubmit="return updateTransaction(event);">
                <!-- Update event handler -->
                <input type="hidden" id="edit_transaction_id" name="transaction_id">
                <div class="form-group">
                    <label for="edit_type">ประเภท</label>
                    <select id="edit_type" name="edit_type" class="form-control" required>
                        <option value="income">รายรับ</option>
                        <option value="expense">รายจ่าย</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="edit_item_name">ชื่อรายการ</label>
                    <input type="text" id="edit_item_name" name="edit_item_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="edit_amount">จำนวนเงิน</label>
                    <input type="number" step="0.01" id="edit_amount" name="edit_amount" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="edit_spending_date">วันที่ใช้จ่าย</label>
                    <input type="date" id="edit_spending_date" name="edit_spending_date" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary">บันทึกการแก้ไข</button>
                <button type="button" class="btn btn-secondary" onclick="closeEditModal()"
                    style="margin-left: 10px;">ยกเลิก</button> <!-- Cancel button -->
            </form>
        </div>
    </div>


    <div class="container">
        <div class="row">
            <div class="col-md-6 offset-md-1">
                <!-- Add offset to create space on the left -->
                <div class="card">
                    <div class="card-header">
                        <h3>รายงานสรุปรายรับ-รายจ่ายรายเดือน</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <label for="summary_month">เลือกเดือนเพื่อดูรายงาน</label>
                            <select id="summary_month" name="summary_month" class="form-control" required>
                                <option value="">เลือกเดือน</option>
                                <?php foreach ($thaiMonthMap as $monthName => $monthNumber): ?>
                                <option value="<?php echo $monthNumber; ?>"><?php echo $monthName; ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="btn btn-primary" style="margin-top: 10px;">แสดงรายงาน</button>
                        </form>

                        <?php
                    if (isset($_POST['summary_month'])) {
                        $selectedMonth = $_POST['summary_month'];

                        // Fetch total income, total expense, and balance for the selected month
                        $sqlSummary = "SELECT 
                                        SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) AS total_income, 
                                        SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) AS total_expense
                                      FROM transactions
                                      WHERE MONTH(spending_date) = :month";
                        $stmtSummary = $condb->prepare($sqlSummary);
                        $stmtSummary->bindParam(':month', $selectedMonth);
                        $stmtSummary->execute();
                        $summary = $stmtSummary->fetch(PDO::FETCH_ASSOC);

                        $totalIncome = $summary['total_income'] ?? 0;
                        $totalExpense = $summary['total_expense'] ?? 0;
                        $balance = $totalIncome - $totalExpense;
                    ?>
                        <div style="margin-top: 20px;">
                            <h4>รายงานเดือน: <?php echo array_search($selectedMonth, $thaiMonthMap); ?></h4>
                            <p>รายรับรวม: <?php echo number_format($totalIncome, 2); ?> บาท</p>
                            <p>รายจ่ายรวม: <?php echo number_format($totalExpense, 2); ?> บาท</p>
                            <p>ยอดคงเหลือ: <?php echo number_format($balance, 2); ?> บาท</p>
                        </div>
                        <?php } ?>
                    </div>
                </div>
            </div>

            <div class="col-md-5">
                <!-- Change column size to 5 to fit the layout -->
                <div class="card">
                    <div class="card-header">
                        <h3>กราฟแสดงรายรับ-รายจ่ายรายเดือน</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="incomeExpenseChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>
    <script>
    $(document).ready(function() {
        $('#transactionForm').on('submit', function(e) {
            e.preventDefault(); // Prevent the default form submission

            $.ajax({
                url: 'save_transaction.php',
                type: 'POST',
                data: $(this).serialize(), // Serialize the form data
                dataType: 'json', // Expecting JSON response
                success: function(response) {
                    // Clear the form fields
                    $('#transactionForm')[0].reset();

                    if (response.success) {
                        swal({
                            title: 'สำเร็จ!',
                            text: 'บันทึกรายการสำเร็จ!',
                            icon: 'success',
                            button: 'ตกลง'
                        }).then(function() {
                            window.location.href = 'index.php'; // Redirect to index
                        });
                    } else {
                        swal({
                            title: 'เกิดข้อผิดพลาด!',
                            text: 'ไม่สามารถบันทึกรายการได้!',
                            icon: 'error',
                            button: 'ตกลง'
                        }).then(function() {
                            window.location.href = 'index.php'; // Redirect to index
                        });
                    }
                },
                error: function() {
                    // Clear the form fields
                    $('#transactionForm')[0].reset();

                    swal({
                        title: 'เกิดข้อผิดพลาด!',
                        text: 'ไม่สามารถติดต่อเซิร์ฟเวอร์ได้!',
                        icon: 'error',
                        button: 'ตกลง'
                    });
                }
            });
        });
    });


    function openEditModal(transaction) {
        document.getElementById('edit_transaction_id').value = transaction.transaction_id;
        document.getElementById('edit_type').value = transaction.type;
        document.getElementById('edit_item_name').value = transaction.item_name;
        document.getElementById('edit_amount').value = transaction.amount;
        document.getElementById('edit_spending_date').value = transaction.spending_date;
        document.getElementById('editModal').style.display = 'block';
    }

    function closeEditModal() {
        document.getElementById('editModal').style.display = 'none';
    }

    // Clear form fields
    function clearForm() {
        document.getElementById('search_month').value = '';
        const tableRows = document.querySelectorAll('table tbody tr');
        tableRows.forEach(row => row.style.display = '');
        window.location.href = window.location.href.split('?')[0];
    }

    const ctx = document.getElementById('incomeExpenseChart').getContext('2d');
    const incomeExpenseChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($months); ?>,
            datasets: [{
                    label: 'รายรับ',
                    data: <?php echo json_encode($incomeData); ?>,
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                },
                {
                    label: 'รายจ่าย',
                    data: <?php echo json_encode($expenseData); ?>,
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1
                }
            ]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'จำนวนเงิน (บาท)'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'เดือน'
                    }
                }
            },
        }
    });

    function updateTransaction(event) {
        event.preventDefault(); // Prevent default form submission

        const formData = new FormData(document.getElementById('editForm')); // Get form data

        fetch('edit_transaction.php', { // Send to the PHP file for processing
                method: 'POST',
                body: formData
            })
            .then(response => response.json()) // Expect JSON response
            .then(data => {
                if (data.success) {
                    swal({
                        title: 'สำเร็จ!',
                        text: 'อัปเดตรายการเรียบร้อยแล้ว!',
                        icon: 'success',
                        button: 'ตกลง'
                    }).then(() => {
                        location.reload(); // Reload the page to show updated data
                    });
                } else {
                    swal({
                        title: 'ผิดพลาด!',
                        text: data.message,
                        icon: 'error',
                        button: 'ตกลง'
                    });
                }
            })
            .catch(error => console.error('Error:', error));
    }

    function deleteTransaction(transactionId) {
        // Confirm deletion
        if (confirm('คุณแน่ใจว่าจะลบรายการนี้?')) {
            const formData = new FormData();
            formData.append('id', transactionId); // Append the ID

            fetch('delete_transaction.php', {
                    method: 'POST',
                    body: formData // Send the form data
                })
                .then(response => response.json())
                .then(data => {
                    console.log(data); // Log the response data for debugging

                    // Check if deletion was successful
                    if (data.success) {
                        swal({
                            title: 'สำเร็จ!',
                            text: 'ลบรายการเรียบร้อยแล้ว!',
                            icon: 'success',
                            button: 'ตกลง'
                        }).then(function() {
                            window.location.href = 'index.php'; // Redirect to index page
                        });
                    } else {
                        swal({
                            title: 'เกิดข้อผิดพลาด!',
                            text: data.error ||
                            'ไม่สามารถลบรายการได้!', // Use the error message if available
                            icon: 'error',
                            button: 'ตกลง'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    swal({
                        title: 'เกิดข้อผิดพลาด!',
                        text: 'ไม่สามารถลบรายการได้!',
                        icon: 'error',
                        button: 'ตกลง'
                    });
                });
        }
    }
    </script>
</body>

</html>