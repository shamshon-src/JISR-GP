<?php
include("config.php");
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'craftsman') {
    header("Location: login.php");
    exit();
}
$first_name = $_SESSION['first_name'];
$craftsman_id = $_SESSION['user_id'];
$user_id = $_SESSION['user_id'];
$query = "SELECT profile_picture FROM users WHERE id = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($profile_picture);
$stmt->fetch();
$stmt->close();
if (empty($profile_picture)) {
    $profile_picture = 'uploads/default-profile.png';
}
$query_customers_count = "
  SELECT COUNT(DISTINCT orders.CustomerID) AS total_customers
  FROM orders
  JOIN order_items ON orders.OrderID = order_items.OrderID
  WHERE order_items.CraftsmanID = $craftsman_id;
";
$total_craftsman_customers = $mysqli->query($query_customers_count)->fetch_assoc()['total_customers'];
$query_products_count = "
  SELECT COUNT(*) AS total_products 
  FROM product 
  WHERE CraftsmanID = $craftsman_id";
$total_craftsman_products = $mysqli->query($query_products_count)->fetch_assoc()['total_products'];
$query_orders_count = "
  SELECT COUNT(DISTINCT OrderID) AS total_orders
  FROM order_items
  WHERE CraftsmanID = $craftsman_id;
";
$total_craftsman_orders = $mysqli->query($query_orders_count)->fetch_assoc()['total_orders'];
$query_earnings = "
  SELECT SUM(order_items.Quantity * order_items.Price) AS total_earnings 
  FROM order_items 
  JOIN orders ON order_items.OrderID = orders.OrderID
  WHERE order_items.CraftsmanID = $craftsman_id 
  AND orders.escrowStatus = 'released';
";
$result = $mysqli->query($query_earnings);
$total_craftsman_earnings = $result->fetch_assoc()['total_earnings'];
$query = "
  SELECT MONTH(orders.OrderDate) AS month, SUM(order_items.Quantity * order_items.Price) AS total_sales
  FROM order_items
  JOIN orders ON order_items.OrderID = orders.OrderID
  WHERE order_items.CraftsmanID = $craftsman_id
  GROUP BY MONTH(orders.OrderDate)
  ORDER BY month;
";
$monthly_sales = $mysqli->query($query)->fetch_all(MYSQLI_ASSOC);
$months = [];
$sales_data = [];
foreach ($monthly_sales as $sale) {
    $months[] = date('F', mktime(0, 0, 0, $sale['month'], 10));
    $sales_data[] = $sale['total_sales'];
}
$query_top_products = "
    SELECT product.ProductName, SUM(order_items.Quantity) AS total_orders
    FROM orders
    JOIN order_items ON orders.OrderID = order_items.OrderID
    JOIN product ON order_items.ProductID = product.ProductID
    WHERE product.CraftsmanID = $craftsman_id
    GROUP BY product.ProductID
    ORDER BY total_orders DESC
    LIMIT 5;
";
$top_products_result = $mysqli->query($query_top_products);
$product_names = [];
$product_sales = [];
if ($top_products_result->num_rows > 0) {
    while ($product = $top_products_result->fetch_assoc()) {
        $product_names[] = $product['ProductName'];
        $product_sales[] = $product['total_orders'];
    }
}
$craftsman_id = $user_id;
$sql = "SELECT 
          o.OrderID,
          o.OrderDate,
          u.first_name AS customer_first_name,
          u.last_name AS customer_last_name,
          u.phone_number AS customer_phone,
          SUM(oi.Quantity * oi.Price) AS TotalPrice,
          u.profile_picture AS customer_profile_picture
        FROM orders o
        JOIN order_items oi ON o.OrderID = oi.OrderID
        JOIN users u ON o.CustomerID = u.id
        WHERE oi.CraftsmanID = ? AND TRIM(o.OrderStatus) != 'Cancelled'
        GROUP BY o.OrderID
        ORDER BY o.OrderDate DESC";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $craftsman_id);
$stmt->execute();
$result = $stmt->get_result();
$orders = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
}
$stmt->close();
echo "<script>
  const totalCraftsmanCustomers = $total_craftsman_customers;
  const totalCraftsmanProducts = $total_craftsman_products;
  const totalCraftsmanOrders = $total_craftsman_orders;
  const totalCraftsmanEarnings = " . ($total_craftsman_earnings ?: 0) . ";
  const months = " . json_encode($months) . ";
  const sales = " . json_encode($sales_data) . ";
  const topProductNames = " . json_encode($product_names) . ";
  const topProductSales = " . json_encode($product_sales) . ";
</script>";
?>
<!DOCTYPE html>
<html lang="ar">
<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge, chrome=1">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<style>
@font-face {
    font-family: 'TheYearOfTheCamel';
    src: url('fonts/TheYearofTheCamel-Light.otf') format('opentype');
    font-weight: normal;
    font-style: normal;
}
* {
    font-family: 'TheYearOfTheCamel', sans-serif;
    box-sizing: border-box;
}
body {
    font-family: 'TheYearOfTheCamel';
    background-color: #fdf9f0;
}
.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background-color: #fdf9f0;
    padding: 10px 5%;
    margin-top: 30px;
}
.header .icons {
    display: flex;
    gap: 1.5rem;
    align-items: center;
}
.header .icons img {
    width: 1.5rem;
    opacity: 0.8;
    cursor: pointer;
}
.header .icons img:hover {
    transform: scale(1.2);
    opacity: 1;
}
.header .navbar {
    display: flex;
    gap: 2rem;
}
.header .navbar a {
    font-size: 1.5rem;
    color: #7b612b;
    font-weight: bold;
    text-decoration: none;
    transition: color 0.3s;
}
.header .navbar a:hover {
    color: #A4AC86;
}
.dropdown {
    position: relative;
    display: inline-block;
}
.dropdown-menu {
    display: none;
    position: absolute;
    top: 3.9rem;
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
.dropdown-icon {
    width: 25px;
    height: 25px;
    margin-left: 10.5px;
    filter: brightness(0) saturate(100%) invert(33%) sepia(54%) saturate(209%) hue-rotate(2deg) brightness(93%) contrast(88%);
}
.sidebar {
    width: 250px;
    height: 100%;
    background-color: #EEE9DF;
    box-shadow: -2px 0 5px rgba(0, 0, 0, 0.1);
    position: fixed;
    top: 0;
    right: -250px;
    transition: right 0.3s ease;
    padding-top: 20px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    border-radius: 15px;
    z-index: 10;
}
.sidebar .account-icon {
    display: flex;
    align-items: center;
    padding: 15px;
    text-decoration: none;
    color: #7b612b;
    margin-bottom: 15px;
    margin-left: auto;
    margin-right: 43px;
}
.sidebar a.logout-btn {
    margin: auto 30px 80px auto;
    text-align: center;
}
.sidebar a {
    text-align: center;
    font-size: 20px;
    display: flex;
    align-items: center;
    padding: 10px 15px;
    text-decoration: none;
    color: #7b612b;
    margin: 5px 30px 8px;
    flex-direction: row-reverse;
}
.sidebar a img {
    width: 1.2rem;
    margin-left: 16px;
    margin-top: -2px;
}
.sidebar a img:not([style*="width: 8rem"]) {
    filter: brightness(0) saturate(100%) invert(33%) sepia(54%) saturate(209%) hue-rotate(2deg) brightness(93%) contrast(88%);
}
.sidebar a:hover img:not([style*="width: 8rem"]) {
    filter: brightness(0) saturate(100%) invert(14%) sepia(59%) saturate(468%) hue-rotate(99deg) brightness(93%) contrast(93%);
    transform: scale(1.2);
    transition: all 0.3s ease-in-out;
}
.sidebar a:hover {
    color: #224F34;
    background-color: #ddd;
    transition: all 0.3s ease-in-out;
}
.sidebar a.active {
    color: #224F34;
}
.sidebar .acc h3 {
    font-size: 2.5erm;
    text-align: center;
    margin-right: 0px;
    margin-top: -9.5px;
}
.sidebar a.active img {
    filter: brightness(0) saturate(100%) invert(14%) sepia(59%) saturate(468%) hue-rotate(99deg) brightness(93%) contrast(93%);
}
.account-btn img:hover {
    transform: scale(1.2);
    opacity: 0.9;
}
h2 {
    font-size: 30px;
    color: #725C3A;
    margin-bottom: 20px;
    text-align: right;
    margin-right: 50px;
    margin-top: -13.5px;
}
.acc {
    color: #725C3A;
    text-align: center;
    margin-left: 12px;
}
.acc :hover {
    color: #224F34;
}
nav ul {
    list-style: none;
    padding: 0;
}
nav ul li {
    margin: 15px 0;
    cursor: pointer;
}
nav ul li:hover {
    text-decoration: underline;
}
.content {
    text-align: center;
    flex: left;
    padding: 20px;
}
.content {
    margin-right: 30px;
    padding: 0px;
    text-align: center;
}
.stats-container {
    display: flex;
    gap: 15px;
    padding: 20px;
    justify-content: center;
    align-items: center;
}
.stat-box {
    background-color: #ffffff;
    border-radius: 30px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    text-align: center;
    width: 200px;
    height: 95px;
    display: inline-block;
    justify-content: center;
    flex-direction: column;
    align-items: flex-start;
    padding: 5px;
    position: relative;
    transition: transform 0.2s ease;
    z-index: 0;
}
.stat-box:hover {
    transform: scale(1.05);
}
.stat-box .icon-container {
    position: absolute;
    right: 18px;
    bottom: 33px;
    display: flex;
    justify-content: center;
    align-items: center;
}
.stat-box .icon-image {
    width: 45px;
    height: 45px;
    object-fit: contain;
}
.stat-number {
    font-size: 32px;
    font-weight: bold;
    color: rgb(74, 93, 82);
    font-family: "Palatino Linotype", "Book Antique", "Palatino", serif;
    top: 14px;
    right: 18px;
    position: relative;
}
.stat-box h3 {
    font-size: 15px;
    top: 17x;
    right: 19px;
    color: rgb(113, 111, 106);
    position: relative;
}
.charts-container {
    display: flex;
    gap: 13px;
    margin-top: 1px;
    justify-content: center;
    align-items: stretch;
}
.chart {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    background-color: #FFFFFF;
    padding: 30px;
    border-radius: 16px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    width: 490px;
    height: 315px;
}
.chart h4 {
    margin: 0;
    padding-bottom: 15px;
    font-size: 30px;
    color: #725C3A;
    margin-top: -10px;
}
canvas {
    max-width: 100%;
    height: 200px;
    object-fit: contain;
}
.table-container {
    width: 70%;
    margin: 20px auto;
    direction: rtl;
    text-align: center;
}
.table-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 5px 10px;
    background-color: rgb(255, 255, 255);
    border-bottom: none;
    border-radius: 8px 8px 0 0;
    font-size: 14px;
    line-height: 1.2;
    border: 1px solid rgb(238, 235, 235);
    flex-wrap: wrap;
    box-sizing: border-box;
}
@media (max-width: 768px) {
    .table-header {
        font-size: 12px;
        padding: 8px;
    }
}
@media (max-width: 480px) {
    .table-header {
        flex-direction: column;
        align-items: flex-start;
        font-size: 11px;
    }
}
.table-controls {
    display: flex;
    align-items: center;
    gap: 15px;
}
.table {
    text-align: center;
    width: 100%;
    border-collapse: collapse;
}
th, td {
    padding: 10px;
    border: 1px solid rgb(238, 235, 235);
    background-color: rgb(255, 255, 255);
}
th {
    color: #725C3A;
    background-color: rgb(255, 255, 255);
    align-items: center;
}
.user-row {
    display: flex;
    align-items: center;
    gap: 10px;
}
.user-row img {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
}
.user-info {
    display: flex;
    flex-direction: column;
    padding: 2px 5px;
}
.user-info .name {
    text-align: right;
    font-weight: bold;
    color: #333;
    padding: 1.5px;
}
.user-info .email {
    color: #666;
    font-size: 14px;
}
.navbar {
    display: flex;
    flex-wrap: nowrap;
    gap: 20px;
    justify-content: center;
    margin: 20px 0;
    align-items: center;
}
.navbar button {
    background-color: #EEE9DF;
    color: #1F1815;
    border: none;
    border-radius: 55px;
    padding: 10px 20px;
    cursor: pointer;
    font-size: 16px;
    transition: all 0.3s ease;
    width: 200px;
    text-align: center;
    flex-shrink: 0;
    box-sizing: border-box;
}
.navbar button:hover {
    background-color: #725C3A;
    color: white;
}
.navbar button.active {
    background-color: #725C3A;
    color: white;
    font-weight: bold;
}
@media (max-width: 768px) {
    .navbar {
        gap: 10px;
    }
    .navbar button {
        width: auto;
        font-size: 14px;
    }
}
.search-bar {
    border-radius: 20px;
    padding: 10px 18px;
    font-size: 16px;
    width: 350px;
    outline: none;
    text-align: right;
    border: 0.5px solid rgb(146, 130, 106);
    background-image: url('images/search.png');
    background-position: right 10px center;
    background-repeat: no-repeat;
    background-size: 18px 18px;
    padding-right: 38px;
}
.btn-management img {
    transition: transform 0.3s ease;
}
.btn-management:hover img {
    transform: scale(1.1);
}
</style>
</head>
<body>
<header class="header">
    <div class="icons">
        <a class="menu-btn" onclick="toggleSidebar()">
            <img src="./images/line.png" alt="شريط">
        </a>
        <div class="dropdown">
            <a class="account-btn" onclick="toggleDropdown()">
                <img src="<?php echo $profile_picture; ?>" alt="الحساب" style="border-radius: 50%; width: 4rem; height: 4rem; object-fit: cover;">
            </a>
            <div id="dropdownMenu" class="dropdown-menu">
                <a href="craftsman-edit-profile.php">
                    <img src="./images/usercust.png" alt="Profile" class="dropdown-icon">
                    الملف الشخصي
                </a>
                <a href="logout.php">
                    <img src="./images/logout.png" alt="Logout" class="dropdown-icon">
                    تسجيل الخروج
                </a>
            </div>
        </div>
    </div>
    <nav class="navbar">
        <a href="craftsman_dashboard.php">الرئيسية</a>
    </nav>
    <div class="logoContent">
        <a href="#"><img src="./images/logo.png" alt="Logo" style="height: 4rem;"></a>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</header>
<div class="sidebar" id="sidebar">
    <a href="#" class="account-icon" onclick="showContent('account')">
       <img src="<?php echo $profile_picture; ?>" alt="الحساب" style="border-radius: 50%; width: 8rem; height:8rem; object-fit: cover;">
    </a>
    <div class="acc">
        <h2>أهلًا بك، <?php echo htmlspecialchars($first_name); ?><br></h2>
        <h3>حرفـيّ</h3>
    </div>
    <a href="addproduct.php">
        <img src="./images/add-product.png" alt="إضافة منتج"> إضافة منتج
    </a>
    <a href="craftman_manage_products.php">
        <img src="./images/pro_man.png" alt="إدارة المنتجات"> إدارة المنتجات
    </a>
    <a href="craftman-orders-manegment.php">
        <img src="./images/manage_orders.png" alt="ادارة الطلبات"> إدارة الطلبات
    </a>
    <a href="craftsman_terms-and-conditions.php" >
    <img src="./images/شروط.png" alt="الشروط والاحكام "> الشروط والاحكام 
</a>
    <a href="logout.php" class="logout-btn">
        <img src="./images/logout.png" alt="تسجيل الخروج"> <span>تسجيل الخروج</span>
    </a>
</div>
<div class="stats-container">
    <div class="stat-box">
        <div class="icon-container">
            <img src="images/money.png" alt="Icon" class="icon-image">
        </div>
        <span class="stat-number" id="totalEarnings"></span>
        <h3>الأرباح</h3>
    </div>
    <div class="stat-box">
        <div class="icon-container">
            <img src="images/order1.png" alt="Icon" class="icon-image">
        </div>
        <span class="stat-number" id="totalOrdersBox"></span>
        <h3>الطلبات</h3>
    </div>
    <div class="stat-box">
        <div class="icon-container">
            <img src="images/product.png" alt="Icon" class="icon-image">
        </div>
        <span class="stat-number" id="totalProductsBox"></span>
        <h3>المنتجات</h3>
    </div>
    <div class="stat-box">
        <div class="icon-container">
            <img src="images/icon_user.png" alt="Icon" class="icon-image">
        </div>
        <span class="stat-number" id="totalCustomersBox"></span>
        <h3>العملاء</h3>
    </div>
</div>
<div class="charts-container">
    <div class="chart">
        <h4>نسبة المبيعات بالأشهر</h4>
        <canvas id="monthlySalesChart"></canvas>
    </div>
    <div class="chart">
        <h4>المنتجات الأكثر مبيعًا</h4>
        <canvas id="productSalesChart"></canvas>
    </div>
</div>
<div class="table-container">
    <div class="table-header">
        <div class="navbar">
            <input type="text" class="search-bar" id="search" placeholder="بحث .." onkeyup="filterTables()">
            <button onclick="showTable('OrderTable', this)" class="active" id="latestBtn">أحدث</button>
            <button onclick="showTable('OrderTable', this)" id="oldestBtn">أقدم</button>
        </div>
    </div>
    <table id="OrderTable" class="table">
        <thead>
            <tr>
                <th>رقم الطلب</th>
                <th>اسم العميل</th>
                <th>رقم الهاتف</th>
                <th>تاريخ الطلب</th>
                <th>السعر</th>
                <th>إدارة</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($orders)): ?>
                <?php foreach ($orders as $order): ?>
                    <tr data-orderdate="<?= date('Y-m-d', strtotime($order['OrderDate'])); ?>">
                        <td><?= $order['OrderID']; ?></td>
                        <td>
                            <div class="user-row">
                                <img src="<?= !empty($order['customer_profile_picture']) ? $order['customer_profile_picture'] : 'uploads/default-profile.png'; ?>" alt="صورة العميل" style="width:40px; height:40px; border-radius:50%; object-fit:cover; margin-right: 8px;">
                                <span><?= $order['customer_first_name'] . " " . $order['customer_last_name']; ?></span>
                            </div>
                        </td>
                        <td><?= $order['customer_phone']; ?></td>
                        <td><?= date('Y-m-d', strtotime($order['OrderDate'])); ?></td>
                        <td><?= number_format($order['TotalPrice'], 2); ?> ريال</td>
                        <td>
                            <a href="craftman-orders-manegment.php" class="btn-management">
                                <img src="images/managment.png" alt="إدارة">
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6">لا توجد طلبات حتى الآن</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<script>
function toggleDropdown() {
    const dropdownMenu = document.getElementById("dropdownMenu");
    dropdownMenu.style.display = dropdownMenu.style.display === "block" ? "none" : "block";
}
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    if (sidebar.style.right === '0px') {
        sidebar.style.right = '-250px';
    } else {
        sidebar.style.right = '0px';
    }
}
function showContent(page) {
    const content = document.getElementById('content');
    switch (page) {
        case 'addproduct':
            window.location.href = 'addproduct.php';
            break;
        case 'manage-orders':
            window.location.href = 'craftman_manage_products.php';
            break;
        case 'craftman-orders-manegment.php':
            window.location.href = 'craftman-orders-manegment.php';
            break;
            case 'craftsman_terms-and-conditions.php':
                    window.location.href = 'craftsman_terms-and-conditions.php'; 
                    break;
        default:
            window.location.href = 'logout.php';
    }
}
document.addEventListener('DOMContentLoaded', () => {
    if (typeof totalCraftsmanCustomers !== 'undefined') {
        document.getElementById('totalCustomersBox').textContent = totalCraftsmanCustomers;
    }
    if (typeof totalCraftsmanProducts !== 'undefined') {
        document.getElementById('totalProductsBox').textContent = totalCraftsmanProducts;
    }
    if (typeof totalCraftsmanOrders !== 'undefined') {
        document.getElementById('totalOrdersBox').textContent = totalCraftsmanOrders;
    }
});
document.getElementById('totalEarnings').innerText = totalCraftsmanEarnings.toLocaleString('en-US');
const arabicMonths = ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'];
const monthlySalesCtx = document.getElementById('monthlySalesChart').getContext('2d');
const monthlySalesChart = new Chart(monthlySalesCtx, {
    type: 'line',
    data: {
        labels: arabicMonths,
        datasets: [{
            label: 'نسبة المبيعات حسب الأشهر',
            data: sales,
            backgroundColor: 'rgba(135, 206, 250, 0.2)',
            borderColor: 'rgba(135, 206, 250, 1)',
            borderWidth: 2,
            pointBackgroundColor: 'rgba(135, 206, 250, 1)',
            pointBorderColor: 'rgba(135, 206, 250, 1)',
            pointRadius: 5
        }]
    },
    options: {
        responsive: true,
        scales: {
            x: {
                ticks: {
                    align: 'center',
                    direction: 'rtl'
                }
            },
            y: {
                beginAtZero: true
            }
        },
        plugins: {
            legend: {
                position: 'top',
                labels: {
                    color: '#727171',
                    font: {
                        size: 14
                    }
                }
            }
        }
    }
});
const productSalesCtx = document.getElementById('productSalesChart').getContext('2d');
const productSalesChart = new Chart(productSalesCtx, {
    type: 'bar',
    data: {
        labels: topProductNames,
        datasets: [{
            label: 'المنتجات الأكثر مبيعًا',
            data: topProductSales,
            backgroundColor: ['#8c7851', '#d1c2a0', '#f4e4cd', '#6b4f34', '#a69076'],
            borderColor: ['#8c7851', '#d1c2a0', '#f4e4cd', '#6b4f34', '#a69076'],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: { beginAtZero: true }
        },
        plugins: {
            legend: {
                position: 'top',
                labels: {
                    boxWidth: 0,
                    color: '#727171',
                    font: {
                        size: 14
                    }
                }
            }
        }
    }
});
function sortTableByDate(tableId, asc) {
    var table = document.getElementById(tableId);
    var tbody = table.tBodies[0];
    var rows = Array.from(tbody.getElementsByTagName("tr"));
    rows.sort(function(a, b) {
      var dateA = new Date(a.getAttribute("data-orderdate"));
      var dateB = new Date(b.getAttribute("data-orderdate"));
      return asc ? dateA - dateB : dateB - dateA;
    });
    rows.forEach(function(row) {
      tbody.appendChild(row);
    });
}
function showTable(tableId, btn) {
    if (btn.id === "latestBtn") {
      sortTableByDate(tableId, false);
      btn.classList.add("active");
      document.getElementById("oldestBtn").classList.remove("active");
    } else if (btn.id === "oldestBtn") {
      sortTableByDate(tableId, true);
      btn.classList.add("active");
      document.getElementById("latestBtn").classList.remove("active");
    }
}
document.addEventListener("DOMContentLoaded", function() {
    showTable('OrderTable', document.getElementById('latestBtn'));
    if (typeof totalCraftsmanCustomers !== 'undefined') {
      document.getElementById('totalCustomersBox').textContent = totalCraftsmanCustomers;
    }
    if (typeof totalCraftsmanProducts !== 'undefined') {
      document.getElementById('totalProductsBox').textContent = totalCraftsmanProducts;
    }
    if (typeof totalCraftsmanOrders !== 'undefined') {
      document.getElementById('totalOrdersBox').textContent = totalCraftsmanOrders;
    }
    document.getElementById('totalEarnings').innerText = totalCraftsmanEarnings.toLocaleString('en-US');
});
function filterTables() {
    const searchValue = document.getElementById('search').value.toLowerCase();
    const table = document.getElementById('OrderTable');
    const tbody = table.tBodies[0];
    const rows = tbody.getElementsByTagName("tr");
    for (let i = 0; i < rows.length; i++) {
      let rowText = rows[i].textContent.toLowerCase();
      rows[i].style.display = rowText.includes(searchValue) ? "" : "none";
    }
}
</script>
</body>
</html>
