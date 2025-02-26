<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}
$first_name = $_SESSION['first_name'];
require_once 'config.php';
$query = "SELECT Category, SUM(sales_percentage) AS total_sales FROM product GROUP BY Category";
$product = $mysqli->query($query)->fetch_all(MYSQLI_ASSOC);
$labels = [];
$data = [];
foreach ($product as $category) {
    $arabicCategory = '';
    switch ($category['Category']) {
        case 'cups':
            $arabicCategory = 'أكواب';
            break;
        case 'bags':
            $arabicCategory = 'حقائب';
            break;
        case 'dolls':
            $arabicCategory = 'دمى';
            break;
        case 'other':
            $arabicCategory = 'أخرى';
            break;
        default:
            $arabicCategory = $category['Category'];
            break;
    }
    $labels[] = $arabicCategory;
    $data[] = $category['total_sales'];
}
$sql = "SELECT CONCAT(first_name, ' ', last_name) AS name, email, address, craft_description, phone_number, created_at, profile_picture FROM users WHERE role = 'craftsman'";
$result = $mysqli->query($sql);
$craftsmen = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $craftsmen[] = $row;
    }
}
$sql = "SELECT CONCAT(first_name, ' ', last_name) AS name, email, address, phone_number, created_at, profile_picture FROM users WHERE role = 'customer'";
$result = $mysqli->query($sql);
$customers = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $customers[] = $row;
    }
}
if (empty($customers)) {
    echo "لا توجد بيانات عملاء متاحة.";
}
$query_craftsmen = "SELECT COUNT(*) AS total_craftsmen FROM users WHERE role = 'craftsman'";
$total_craftsmen = $mysqli->query($query_craftsmen)->fetch_assoc()['total_craftsmen'];
$query_customers = "SELECT COUNT(*) AS total_customers FROM users WHERE role = 'customer'";
$total_customers = $mysqli->query($query_customers)->fetch_assoc()['total_customers'];
$query_products = "SELECT COUNT(*) AS total_products FROM product";
$total_products = $mysqli->query($query_products)->fetch_assoc()['total_products'];
$query_orders = "SELECT COUNT(*) AS total_orders FROM orders";
$total_orders = $mysqli->query($query_orders)->fetch_assoc()['total_orders'];
echo "<script>
    const totalCraftsmen = $total_craftsmen;
    const totalCustomers = $total_customers;
    const totalProducts = $total_products;
    const totalOrders = $total_orders;
    const productLabels = " . json_encode($labels) . ";
    const productData = " . json_encode($data) . ";
    const customers = " . json_encode($customers) . ";
    const craftsmen = " . json_encode($craftsmen) . ";
</script>";
$query_monthly_sales = "SELECT MONTH(OrderDate) AS month, SUM(TotalAmount) AS total_sales FROM orders GROUP BY MONTH(OrderDate) ORDER BY month";
$result_monthly_sales = $mysqli->query($query_monthly_sales);
$monthly_sales = [];
$total_year_sales = 0;
while ($row = $result_monthly_sales->fetch_assoc()) {
    $monthly_sales[] = $row;
    $total_year_sales += $row['total_sales'];
}
$months = [];
$sales_data = [];
$percentage_data = [];
foreach ($monthly_sales as $sale) {
    $month_name = date('F', mktime(0, 0, 0, $sale['month'], 10));
    $months[] = $month_name;
    $sales_data[] = $sale['total_sales'];
    $percentage_data[] = ($sale['total_sales'] / $total_year_sales) * 100;
}
echo "<script>
    const monthlyLabels = " . json_encode($months) . ";
    const monthlyData = " . json_encode($percentage_data) . ";
</script>";
?>
<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة تحكم الإدمن</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <?php include("admin-header.php"); ?>
    <?php include("admin-sidebar.php"); ?>
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
body, html {
    margin: 0;
    padding: 0;
    height: 100%;
    background-color: #fdf9f0;
}
.background {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100vh;
    overflow: hidden;
}
.content {
    text-align: center;
    flex: left;
    padding: 20px;
}
.charts-container {
    display: flex;
    gap: 15px;
    justify-content: space-between;
    margin-top: 30px;
}
.chart {
    flex: 1;
    background: #fff;
    padding: 50px;
    border-radius: 8px;
    max-width: 500px;
    height: 300px;
}
.chart {
    text-align: center;
    width: 45%;
    min-width: 300px;
}
.chart h2 {
    font-size: 30px;
    color: #725C3A;
    margin-bottom: 10px;
    text-align: center;
}
canvas {
    max-width: 100%;
    height: auto;
}
.requests-container {
    background: #fff;
    padding: 15px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}
h1 {
    font-size: 30px;
    color: #224F34;
    margin-bottom: 20px;
    text-align: center;
    margin-right: 0;
    margin-top: 10px;
}
.content {
    position: relative;
    z-index: 2;
    padding: 20px;
    color: #333;
    margin-right: 30px;
    padding: 0px;
    text-align: center;
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
    width: 480px;
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
.icon-container {
    width: 52px;
    height: 52px;
}
.icon-image {
    width: 100%;
    height: 100%;
    object-fit: contain;
    margin-top: 23px;
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
<main class="content">
<div class="stats-container">
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
    <div class="stat-box">
        <div class="icon-container">
            <img src="images/icon_user.png" alt="Icon" class="icon-image">
        </div>
        <span class="stat-number" id="totalCraftsmenBox"></span>
        <h3>الحرفيين</h3>
    </div>
</div>
<div class="charts-container">
    <div class="chart">
        <h4>نسبة المبيعات بالأشهر</h4>
        <canvas id="monthlySalesChart"></canvas>
    </div>
    <div class="chart">
        <h4>نسبة المبيعات لكل فئة</h4>
        <canvas id="productSalesChart"></canvas>
    </div>
</div>
<div class="table-container">
    <div class="table-header">
        <div class="navbar">
            <input type="text" class="search-bar" id="search" placeholder="بحث .." onkeyup="filterTables()">
            <button onclick="showTable('craftsmenTable', this)" class="active" id="craftsmenBtn">الحرفيين</button>
            <button onclick="showTable('customersTable', this)" id="customersBtn">العملاء</button>
        </div>
    </div>
    <table id="craftsmenTable" class="table">
        <thead>
            <tr>
                <th>الاسم</th>
                <th>وصف الحرفة</th>
                <th>رقم الهاتف</th>
                <th>تاريخ الإنضمام</th>
                <th>إدارة</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($craftsmen as $craftsman): ?>
            <tr>
                <td>
                    <div class="user-row">
                        <img src="<?= !empty($craftsman['profile_picture']) ? $craftsman['profile_picture'] : 'images/usercust.png'; ?>" alt="صورة الحرفي" style="width:40px; height:40px; border-radius:50%; object-fit:cover; margin-right:8px;">
                        <div class="user-info">
                            <span class="name"><?= $craftsman['name']; ?></span>
                            <span class="email"><?= $craftsman['email']; ?></span>
                        </div>
                    </div>
                </td>
                <td><?= $craftsman['craft_description']; ?></td>
                <td><?= $craftsman['phone_number']; ?></td>
                <td><?= date("d-m-Y", strtotime($craftsman['created_at'])); ?></td>
                <td>
                    <a href="manage_craftsmen.php" class="btn-management">
                        <img src="images/managment.png" alt="إدارة" />
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <table id="customersTable" class="table" style="display: none;">
        <thead>
            <tr>
                <th>الاسم</th>
                <th>العنوان</th>
                <th>رقم الهاتف</th>
                <th>تاريخ الإنضمام</th>
                <th>إدارة</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($customers as $customer): ?>
            <tr>
                <td>
                    <div class="user-row">
                        <img src="<?= !empty($customer['profile_picture']) ? $customer['profile_picture'] : 'images/usercust.png'; ?>" alt="صورة العميل" style="width:40px; height:40px; border-radius:50%; object-fit:cover; margin-right:8px;">
                        <div class="user-info">
                            <span class="name"><?= $customer['name']; ?></span>
                            <span class="email"><?= $customer['email']; ?></span>
                        </div>
                    </div>
                </td>
                <td><?= $customer['address']; ?></td>
                <td><?= $customer['phone_number']; ?></td>
                <td><?= date("d-m-Y", strtotime($customer['created_at'])); ?></td>
                <td>
                    <a href="manage_customer.php" class="btn-management">
                        <img src="images/managment.png" alt="إدارة" />
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</main>
<script>
const productSalesCtx = document.getElementById('productSalesChart').getContext('2d');
const productSalesChart = new Chart(productSalesCtx, {
    type: 'doughnut',
    data: {
        labels: productLabels,
        datasets: [{
            label: 'نسبة المبيعات حسب التصنيف',
            data: productData,
            backgroundColor: ['#8c7851', '#d1c2a0', '#f4e4cd', '#6b4f34']
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'right',
                labels: {
                    font: {
                        size: 17,
                        family: "'TheYearOfTheCamel'"
                    },
                    color: '#725C3A'
                }
            },
            tooltip: {
                bodyFont: {
                    size: 14
                }
            },
            datalabels: {
                display: true,
                color: '#fff',
                font: {
                    size: 16,
                    weight: 'bold'
                },
                formatter: (value, context) => {
                    return value + '%';
                },
                anchor: 'center',
                align: 'center'
            }
        },
        layout: {
            padding: {
                top: 20,
                bottom: 20
            }
        }
    }
});
const monthlySalesCtx = document.getElementById('monthlySalesChart').getContext('2d');
const monthlySalesChart = new Chart(monthlySalesCtx, {
    type: 'line',
    data: {
        labels: monthlyLabels,
        datasets: [{
            label: 'نسبة المبيعات حسب الأشهر',
            data: monthlyData,
            backgroundColor: 'rgb(153, 182, 138)',
            borderColor: 'rgb(6, 70, 40)',
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return value + '%';
                    }
                }
            }
        }
    }
});
document.getElementById("totalProductsBox").textContent = totalProducts;
document.getElementById("totalCustomersBox").textContent = totalCustomers;
document.getElementById("totalCraftsmenBox").textContent = totalCraftsmen;
document.getElementById("totalOrdersBox").textContent = totalOrders;
function showTable(tableId, btn) {
    const tables = document.querySelectorAll('.table');
    tables.forEach(table => {
        table.style.display = 'none';
        table.classList.remove('active-table');
    });
    const selectedTable = document.getElementById(tableId);
    if (selectedTable) {
        selectedTable.style.display = 'table';
        selectedTable.classList.add('active-table');
    }
    const buttons = document.querySelectorAll('.navbar button');
    buttons.forEach(button => {
        button.classList.remove('active');
    });
    btn.classList.add('active');
}
function filterTables() {
    const filter = document.getElementById('search').value.toLowerCase();
    const activeTable = document.querySelector('.table.active-table');
    if (activeTable) {
        const rows = activeTable.getElementsByTagName('tr');
        for (let i = 1; i < rows.length; i++) {
            const cells = rows[i].getElementsByTagName('td');
            let match = false;
            for (let j = 0; j < cells.length; j++) {
                if (cells[j].innerText.toLowerCase().includes(filter)) {
                    match = true;
                    break;
                }
            }
            rows[i].style.display = match ? '' : 'none';
        }
    }
}
document.addEventListener("DOMContentLoaded", function() {
    showTable('craftsmenTable', document.getElementById('craftsmenBtn'));
});
</script>
</body>
</html>
