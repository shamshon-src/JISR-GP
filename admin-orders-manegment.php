<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}
if (isset($_SESSION['statusUpdated']) && $_SESSION['statusUpdated'] === true) {
    echo "<script>document.getElementById('yourButtonId').innerText = 'تم تحديث الحالة';</script>";
    unset($_SESSION['statusUpdated']);
}
$first_name = $_SESSION['first_name'];
include("config.php");
if ($mysqli->connect_error) {
    die("فشل الاتصال بقاعدة البيانات: " . $mysqli->connect_error);
}
$admin_query = "SELECT first_name, last_name FROM users WHERE role = 'admin' LIMIT 1";
$admin_result = $mysqli->query($admin_query);
if ($admin_result) {
    $admin_name = $admin_result->fetch_assoc();
} else {
    die("فشل في جلب بيانات المدير");
}
$filter = isset($_GET['status']) ? $mysqli->real_escape_string($_GET['status']) : 'all';
$status_condition = '';
if ($filter === 'new') {
    $status_condition = "WHERE o.is_new = 1";
    $mysqli->query("UPDATE orders SET is_new = 0 WHERE TIMESTAMPDIFF(WEEK, OrderDate, NOW()) > 5 AND is_new = 1");
    $mysqli->query("UPDATE orders SET ShippingStatus = 'pending' WHERE OrderStatus = 'pending' AND ShippingStatus != 'shipped' AND OrderStatus != 'cancelled'");
} elseif ($filter === 'pending') {
    $status_condition = "WHERE o.ShippingStatus = 'pending'";
} elseif ($filter === 'cancelled') {
    $status_condition = "WHERE o.escrowStatus = 'cancelled'";
} elseif ($filter === 'completed') {
    $status_condition = "WHERE o.escrowStatus = 'released'";
}
$perPage = 100;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max($page, 1);
$offset = ($page - 1) * $perPage;
$orders_query = "
SELECT o.OrderID, u.first_name, u.last_name, u.email, o.TotalAmount, o.OrderDate, 
       o.OrderStatus, o.Address, o.escrowStatus, i.PaymentMethod, i.paymentStatus, 
       o.ShippingStatus
FROM Orders o 
JOIN Users u ON o.CustomerID = u.id 
JOIN Invoice i ON o.OrderID = i.OrderID 
" . ($status_condition ? $status_condition : '') . "
ORDER BY o.OrderID ASC 
LIMIT $perPage OFFSET $offset
";
$orders_result = $mysqli->query($orders_query);
if (!$orders_result) {
    die("فشل في جلب بيانات الطلبات");
}
$orders_count_query = "SELECT COUNT(*) AS total FROM orders o $status_condition";
$orders_count_result = $mysqli->query($orders_count_query);
if (!$orders_count_result) {
    die("فشل في حساب عدد الطلبات");
}
$orders_count = $orders_count_result->fetch_assoc()['total'];
$totalPages = ceil($orders_count / $perPage);
if (isset($_GET['updateStatus']) && isset($_GET['orderId'])) {
    $orderId = (int)$_GET['orderId'];
    $status = $mysqli->real_escape_string($_GET['updateStatus']);
    $validStatuses = ['shipped', 'pending', 'accepted', 'cancelled'];
    if (in_array($status, $validStatuses)) {
        $query = "UPDATE orders SET ShippingStatus = ?, OrderStatus = ? WHERE OrderID = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param('ssi', $status, $status, $orderId);
        if ($stmt->execute()) {
            header("Location: admin-orders-manegment.php");
            exit();
        } else {
            echo "<script>alert('فشل في تحديث حالة الطلب.');</script>";
        }
        $stmt->close();
    }
}
if (isset($_GET['reviewPayment']) && isset($_GET['orderId'])) {
    $orderId = (int)$_GET['orderId'];
    $status = $mysqli->real_escape_string($_GET['reviewPayment']);
    $validStatuses = ['success', 'pending', 'failed'];
    if (in_array($status, $validStatuses)) {
        $query = "UPDATE invoice SET PaymentStatus = ? WHERE OrderID = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param('si', $status, $orderId);
        if ($stmt->execute()) {
            header("Location: admin-orders-manegment.php");
            exit();
        } else {
            echo "<script>alert('فشل في تحديث حالة الدفع.');</script>";
        }
        $stmt->close();
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'updateStatus') {
    include("config.php");
    $orderId = (int)$_POST['orderId'];
    $newStatus = $mysqli->real_escape_string($_POST['newStatus']);
    $validStatuses = ['shipped', 'pending', 'accepted', 'cancelled'];
    if (in_array($newStatus, $validStatuses)) {
        $query = "UPDATE orders SET ShippingStatus = ? WHERE OrderID = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param('si', $newStatus, $orderId);
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to update']);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid status']);
    }
    exit;
}
if (isset($_GET['updateEscrow']) && isset($_GET['orderId'])) {
    $orderId = (int)$_GET['orderId'];
    $newEscrowStatus = $mysqli->real_escape_string($_GET['updateEscrow']);
    $validEscrowStatuses = ['held', 'released', 'cancelled'];
    if (in_array($newEscrowStatus, $validEscrowStatuses)) {
        $query = "UPDATE orders SET escrowStatus = ? WHERE OrderID = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param('si', $newEscrowStatus, $orderId);
        if ($stmt->execute()) {
            if ($newEscrowStatus === 'cancelled') {
                $updateOrderQuery = "UPDATE orders SET OrderStatus = 'cancelled', ShippingStatus = 'cancelled' WHERE OrderID = ?";
                $stmt2 = $mysqli->prepare($updateOrderQuery);
                $stmt2->bind_param('i', $orderId);
                $stmt2->execute();
                $stmt2->close();
                $updateItemsQuery = "UPDATE order_items SET PaymentStatus = 'failed' WHERE OrderID = ?";
                $stmt3 = $mysqli->prepare($updateItemsQuery);
                $stmt3->bind_param('i', $orderId);
                $stmt3->execute();
                $stmt3->close();
                $updateInvoiceQuery = "UPDATE invoice SET PaymentStatus = 'failed' WHERE OrderID = ?";
                $stmt4 = $mysqli->prepare($updateInvoiceQuery);
                $stmt4->bind_param('i', $orderId);
                $stmt4->execute();
                $stmt4->close();
            } elseif ($newEscrowStatus === 'released') {
                $updateOrderQuery = "UPDATE orders SET OrderStatus = 'accepted', ShippingStatus = 'shipped' WHERE OrderID = ?";
                $stmt2 = $mysqli->prepare($updateOrderQuery);
                $stmt2->bind_param('i', $orderId);
                $stmt2->execute();
                $stmt2->close();
                $updateItemsQuery = "UPDATE order_items SET PaymentStatus = 'released' WHERE OrderID = ?";
                $stmt3 = $mysqli->prepare($updateItemsQuery);
                $stmt3->bind_param('i', $orderId);
                $stmt3->execute();
                $stmt3->close();
                $updateInvoiceQuery = "UPDATE invoice SET PaymentStatus = 'success' WHERE OrderID = ?";
                $stmt4 = $mysqli->prepare($updateInvoiceQuery);
                $stmt4->bind_param('i', $orderId);
                $stmt4->execute();
                $stmt4->close();
            } elseif ($newEscrowStatus === 'held') {
                $updateOrderQuery = "UPDATE orders SET OrderStatus = 'pending', ShippingStatus = 'pending' WHERE OrderID = ?";
                $stmt2 = $mysqli->prepare($updateOrderQuery);
                $stmt2->bind_param('i', $orderId);
                $stmt2->execute();
                $stmt2->close();
                $updateItemsQuery = "UPDATE order_items SET PaymentStatus = 'pending' WHERE OrderID = ?";
                $stmt3 = $mysqli->prepare($updateItemsQuery);
                $stmt3->bind_param('i', $orderId);
                $stmt3->execute();
                $stmt3->close();
                $updateInvoiceQuery = "UPDATE invoice SET PaymentStatus = 'pending' WHERE OrderID = ?";
                $stmt4 = $mysqli->prepare($updateInvoiceQuery);
                $stmt4->bind_param('i', $orderId);
                $stmt4->execute();
                $stmt4->close();
            }
            header("Location: admin-orders-manegment.php");
            exit();
        } else {
            echo "<script>alert('فشل في تحديث حالة الـ escrow.');</script>";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة الطلبات</title>
    <?php include("admin-header.php"); ?>
    <?php include("admin-sidebar.php"); ?>
    <style>
        .content {
            text-align: center;
            flex: left;
            padding: 20px;
        }
        .dropdown {
            position: relative;
            display: inline-block;
        }
        .dropdown-btn {
            padding: 10px;
            font-size: 16px;
            background-color: #fff;
            border: 1px solid #ccc;
            border-radius: 5px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 130px;
        }
        .dropdown-btn::after {
            content: "∨";
            margin-left: 10px;
            font-size: 14px;
        }
        .dropdown-menu {
            display: none;
            position: absolute;
            top: 2rem;
            left: 50%;
            transform: translateX(-50%);
            background-color: #fff;
            border: 1px solid #ccc;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
            z-index: 1000;
            width: 152px;
            padding: 5px 0;
            direction: rtl;
            text-align: center;
        }
        .dropdown.open .dropdown-menu {
            display: block;
        }
        .dropdown-menu a {
            display: flex;
            align-items: center;
            padding: 10px 15px;
            text-decoration: none;
            color: #224F34;
            font-size: 16px;
            justify-content: flex-start;
            transition: background-color 0.2s;
        }
        .dropdown-menu a:hover {
            background-color: #f5f5f5;
        }
        @font-face {
            font-family: 'TheYearOfTheCamel';
            src: url('fonts/TheYearofTheCamel-Light.otf') format('opentype');
            font-weight: normal;
            font-style: normal;
        }
        * {
            font-family: 'TheYearOfTheCamel', sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            outline: none;
            border: none;
            text-decoration: none;
            text-transform: capitalize;
            transition: .2s linear;
        }
        .navbar2 {
            display: flex;
            flex-direction: row-reverse;
            gap: 16px;
            justify-content: center;
            margin-bottom: 40px;
            margin-top: 30px;
        }
        .navbar2 button {
            background-color: #EEE9DF;
            color: rgb(31, 26, 24);
            border: none;
            border-radius: 55px;
            padding: 10px 70px;
            cursor: pointer;
            font-size: 20px;
            transition: all 0.3s ease;
        }
        .navbar2 button:hover {
            background-color: #725C3A;
        }
        .navbar2 button.active {
            background-color: #725C3A;
            color: #fff;
            font-weight: bold;
        }
        .status-table {
            direction: rtl;
            margin-top: 20px;
            width: 80%;
            border-collapse: collapse;
            text-align: center;
            background-color: #FFFDF9;
            border-radius: 10px;
            box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
            margin: auto;
        }
        .status-table th, .status-table td {
            padding: 15px;
            border: 1px solid #eaeaea;
            color: #6D5633;
        }
        .status-table th {
            background-color: #f3f0e9;
            color: #466952;
        }
        .status-table button {
            padding: 10px 15px;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            background-color: #f3f0e9;
            color: #6D5633;
            font-weight: bold;
        }
        .status-table button.active {
            background-color: #6D5633;
            color: white;
        }
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }
        .container {
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        .content {
            flex: 1;
            padding: 20px;
            margin-right: 20px;
        }
        .select-dropdown {
            width: 100%;
            padding: 5px;
            font-size: 14px;
            border-radius: 5px;
            border: 1px solid #ccc;
            background-color: #f3f0e9;
            color: #6D5633;
        }
        select {
            padding: 5px;
            width: 100%;
            font-size: 14px;
            border-radius: 5px;
            border: 1px solid #ccc;
            background-color: #f3f0e9;
            color: #6D5633;
        }
        h1 {
            font-size: 30px;
            color: #224F34;
            margin-bottom: 20px;
            text-align: right;
            margin-right: 210px;
            margin-top: -13.5px;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="content">
        <h1>إدارة الطلبات <?php echo "(" . $orders_count . ")<br>"; ?></h1>
        <div class="navbar2">
            <a href="?status=all">
                <button class="<?php echo $filter === 'all' ? 'active' : ''; ?>">الكل</button>
            </a>
            <a href="?status=new">
                <button class="<?php echo $filter == 'new' ? 'active' : ''; ?>">جديد</button>
            </a>
            <a href="?status=pending">
                <button class="<?php echo $filter == 'pending' ? 'active' : ''; ?>">معلق</button>
            </a>
            <a href="?status=cancelled">
                <button class="<?php echo $filter == 'cancelled' ? 'active' : ''; ?>">ملغيّ</button>
            </a>
            <a href="?status=completed">
                <button class="<?php echo $filter == 'completed' ? 'active' : ''; ?>">مكتمل</button>
            </a>
        </div>
        <?php if ($orders_count == 0): ?>
            <p>لا يوجد محتوى في الوقت الحالي.</p>
        <?php else: ?>
            <table class="status-table">
                <thead>
                    <tr>
                        <th>رقم الطلب</th>
                        <th>اسم العميل</th>
                        <th>البريد الإلكتروني</th>
                        <th>المبلغ الإجمالي</th>
                        <th>حالة الطلب</th>
                        <th>حالة الـ Escrow</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($order = $orders_result->fetch_assoc()): 
                        $order_status_display = $order['OrderStatus'] === 'pending' ? 'قيد التجهيز' :
                        ($order['OrderStatus'] === 'shipped' ? 'مشحون' : 
                        ($order['OrderStatus'] === 'accepted' ? 'مستلم' : 
                        ($order['OrderStatus'] === 'cancelled' ? 'ملغيّ' : 
                        ($order['OrderStatus'] === 'held' ? 'محجوز' : 'غير محدد'))));
                        $status_display = $order['ShippingStatus'] === 'pending' ? 'معلق' :
                        ($order['ShippingStatus'] === 'shipped' ? 'مشحون' : 
                        ($order['ShippingStatus'] === 'accepted' ? 'مستلم' : 
                        ($order['ShippingStatus'] === 'cancelled' ? 'ملغيّ' : 
                        ($order['ShippingStatus'] === 'held' ? 'محجوز' : 'غير محدد'))));
                        $escrow_status = isset($order['escrowStatus']) ? $order['escrowStatus'] : 'pending';
                        $escrow_status_display = $escrow_status == 'held' ? 'تم الحجز' :
                        ($escrow_status == 'released' ? 'تم الإصدار' : 
                        ($escrow_status == 'cancelled' ? 'تم الإلغاء' : 'معلق '));
                        $order_status_display = $escrow_status === 'pending' ? 'معلق' : $order_status_display;
                    ?>
                        <tr>
                            <td>
                                <button onclick="showOrderDetails(<?php echo htmlspecialchars($order['OrderID']); ?>)">
                                    <?php echo htmlspecialchars($order['OrderID']); ?>
                                </button>
                            </td>
                            <td><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($order['email']); ?></td>
                            <td><?php echo htmlspecialchars($order['TotalAmount']); ?> ريال</td>
                            <td>
                                <span><?php echo $order_status_display; ?></span>
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button class="dropdown-btn"><?php echo $escrow_status_display; ?></button>
                                    <div class="dropdown-menu">
                                        <a href="?updateEscrow=held&orderId=<?php echo $order['OrderID']; ?>">تم الحجز</a>
                                        <a href="?updateEscrow=released&orderId=<?php echo $order['OrderID']; ?>">تم الإصدار</a>
                                        <a href="?updateEscrow=cancelled&orderId=<?php echo $order['OrderID']; ?>">تم الإلغاء</a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
<script>
    const dropdownBtns = document.querySelectorAll('.dropdown-btn');
    dropdownBtns.forEach((btn) => {
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            document.querySelectorAll('.dropdown.open').forEach((openDropdown) => {
                if (openDropdown !== btn.closest('.dropdown')) {
                    openDropdown.classList.remove('open');
                }
            });
            const dropdown = btn.closest('.dropdown');
            dropdown.classList.toggle('open');
        });
    });
    document.addEventListener('click', () => {
        document.querySelectorAll('.dropdown.open').forEach((dropdown) => {
            dropdown.classList.remove('open');
        });
    });
    function showOrderDetails(orderId) {
        window.location.href = 'admin_order_details.php?order_id=' + orderId;
    }
</script>
</body>
</html>
