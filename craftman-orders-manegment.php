<?php
include("config.php");
session_start();
$user_id = $_SESSION['user_id'];
$first_name = $_SESSION['first_name'];
$query = "SELECT profile_picture, last_name, phone_number, address FROM users WHERE id = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($profile_picture, $last_name, $phone_number, $address);
$stmt->fetch();
$stmt->close();
if (empty($profile_picture)) {
    $profile_picture = 'uploads/default-profile.png';
}
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'craftsman') {
    header("Location: login.php");
    exit();
}
$first_name = $_SESSION['first_name'];
$craftsman_id = $_SESSION['user_id'];
$filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$status_condition = '';
if ($filter == 'pending') {
    $status_condition = " AND o.OrderStatus = 'Pending' ";
} elseif ($filter == 'shipped') {
    $status_condition = " AND o.OrderStatus = 'Shipped' ";
} elseif ($filter == 'accepted') {
    $status_condition = " AND o.OrderStatus = 'Accepted' ";
}
$sql = "SELECT 
        o.OrderID, 
        o.CustomerID, 
        o.OrderDate, 
        o.ShippingStatus, 
        o.OrderStatus, 
        o.TotalAmount, 
        o.Address, 
        o.is_new, 
        oi.ProductID, 
        p.ProductName,
        oi.quantity, 
        oi.Price, 
        oi.PaymentStatus, 
        oi.CraftsmanID, 
        oi.order_item_id,
        o.escrowStatus,
        u.first_name AS customer_first_name,
        u.last_name AS customer_last_name
    FROM orders o
    JOIN order_items oi ON o.OrderID = oi.OrderID
    JOIN users u ON o.CustomerID = u.id
    JOIN product p ON oi.ProductID = p.ProductID 
    WHERE oi.CraftsmanID = ? AND TRIM(o.OrderStatus) != 'Cancelled' $status_condition
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
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['OrderID']) && isset($_POST['OrderStatus'])) {
        $orderID = $_POST['OrderID'];
        $query = "SELECT escrowStatus FROM orders WHERE OrderID = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("i", $orderID);
        $stmt->execute();
        $stmt->bind_result($escrowStatus);
        $stmt->fetch();
        $stmt->close();
        if ($escrowStatus == 'released') {
            $orderStatus = 'accepted';
        } else {
            $orderStatus = $_POST['OrderStatus'];
        }
        $sql = "UPDATE orders SET OrderStatus = ? WHERE OrderID = ?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("si", $orderStatus, $orderID);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: craftman-orders-manegment.php?status=" . $filter);
    exit();
}
?>
<!DOCTYPE html>
<html lang="ar">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>إدارة الطلبات</title>
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
       /* تنسيق الهيدر */
       .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #fdf9f0;
            padding: 10px 5%;
        }

        .header .icons {
            display: flex;
            gap: 1.5rem;
            align-items: center;
        }

        .header .icons img {
            width: 1.5rem;  /* حجم الأيقونات العادية */
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

         /* تنسيق القائمة المنسدلة */
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
    filter: brightness(0) saturate(100%)
          invert(33%) sepia(54%) saturate(209%) hue-rotate(2deg) brightness(93%) contrast(88%); 
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
            margin-top:-2px;       
        }
        .sidebar a img:not([style*="width: 8rem"]) {
  filter: brightness(0) saturate(100%)
          invert(33%) sepia(54%) saturate(209%) hue-rotate(2deg) brightness(93%) contrast(88%);
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


        .sidebar a:hover {
            background-color: #ddd;
        }

        .sidebar a.active {
         color: #224F34;
        }
        
        .sidebar .acc h3 {
        font-size:2.5erm;
         text-align: center; 
          margin-right: 0px;
          margin-top: -9.5px;   
        }


       .sidebar a.active img {
         filter: brightness(0) saturate(100%) invert(14%) sepia(59%) saturate(468%) hue-rotate(99deg) brightness(93%) contrast(93%); /* تغيير لون الأيقونة */
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
            margin-left:12px;
        }
        .acc :hover{
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
      padding: 20px;
    }
    h1 {
      font-size: 30px;
      color: #224F34;
      margin-bottom: 20px;
      text-align: right;
      margin-right: 311px;
      margin-top: 10px;
    }
    .profile-container {
      text-align: center;
      margin-top: 20px;
    }
    .profile-picture {
      width: 120px;
      height: 120px;
      border-radius: 50%;
      object-fit: cover;
      box-shadow: 0 4px 6px rgba(0,0,0,0.1);
      border: 3px solid #725C3A;
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
      margin: 20px auto;
      width: 100%;
      max-width: 1400px;
      border-collapse: collapse;
      text-align: center;
      background-color: #FFFDF9;
      border-radius: 10px;
      box-shadow: 0px 2px 5px rgba(0,0,0,0.1);
      overflow: hidden;
      font-size: 16px;
    }
    .status-table thead th,
    .status-table tbody td {
      padding: 15px;
    }
    .status-table thead th {
      background-color: #f3f0e9;
      color: #466952;
      border: 1px solid #eaeaea;
    }
    .status-table tbody td {
      border: 1px solid #eaeaea;
      color: #6D5633;
    }
    .order-number {
      background-color: #f3f0e9;
      border: 1px solid #eaeaea;
      border-radius: 50%;
      width: 38px;
      height: 38px;
      line-height: 40px;
      text-align: center;
      display: inline-block;
      margin: auto;
      font-size: 18px;
    }
    .select-dropdown {
      max-width: 72%;
      padding: 8px 2px 9px 10px;
      text-align:center;
      font-size: 16px;
      border: 1px solid #ccc;
      border-radius: 30px;
      background-color: #f3f0e9;
      background-image: url('./images/arrow-down.png');
      background-repeat: no-repeat;
      background-position: left 5px center;
      background-size: 12px;
      color: #6D5633;
      appearance: none;
      -webkit-appearance: none;
      -moz-appearance: none;
      transition: none;
    }
    .select-dropdown:focus,
    .select-dropdown:hover {
      outline: none;
      border: 1px solid #ccc;
    }
    select.select-dropdown {
      width: 100%;
    }
    p {
      text-align: center;
      font-size: 15px;
      color: #7f8c8d;
      margin-top: 20px;
    }
    .status-label {
      background-color: #f3f0e9;
      border: 1px solid #ccc;
      border-radius: 30px;
      padding: 7px 19px;
      color: #6D5633;
      font-size: 16px;
      display: inline-block;
      margin: auto;
      text-align: center;
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
    <a href="craftman-orders-manegment.php" class="active">
      <img src="./images/manage_orders.png" alt="ادارة الطلبات"> إدارة الطلبات
    </a>
    <a href="logout.php" class="logout-btn">
      <img src="./images/logout.png" alt="تسجيل الخروج"> <span>تسجيل الخروج</span>
    </a>
  </div>
  <div class="container">
    <h1>إدارة الطلبات (<?php echo count($orders); ?>)</h1>
    <div class="navbar2">
      <button onclick="window.location.href='craftman-orders-manegment.php?status=all'" <?php if($filter=='all') echo 'class="active"'; ?>>جميع الطلبات</button>
      <button onclick="window.location.href='craftman-orders-manegment.php?status=pending'" <?php if($filter=='pending') echo 'class="active"'; ?>>قيد التجهيز</button>
      <button onclick="window.location.href='craftman-orders-manegment.php?status=shipped'" <?php if($filter=='shipped') echo 'class="active"'; ?>>تم الشحن</button>
    </div>
    <?php if (!empty($orders)): ?>
    <table class="status-table">
      <thead>
        <tr>
          <th>رقم الطلب</th>
          <th>اسم العميل</th>
          <th>التاريخ</th>
          <th>حالة الطلب</th>
          <th>العنوان</th>
          <th>المنتج</th>
          <th>الكمية</th>
          <th>السعر</th>
          <th>حالة الدفع</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($orders as $order): ?>
        <tr>
          <td><span class="order-number"><?= $order['OrderID']; ?></span></td>
          <td><?= $order['customer_first_name'] . "  " . $order['customer_last_name']; ?></td>
          <td><?= date("Y-m-d", strtotime($order['OrderDate'])); ?></td>
          <td>
            <form method="POST" action="craftman-orders-manegment.php">
              <input type="hidden" name="OrderID" value="<?= $order['OrderID']; ?>">
              <?php if ($order['escrowStatus'] == 'released'): ?>
                <span class="status-label">تم التسليم</span>
              <?php else: ?>
                <select name="OrderStatus" onchange="this.form.submit()" class="select-dropdown">
                  <option value="Pending" <?= $order['OrderStatus'] == 'Pending' ? 'selected' : ''; ?>>قيد التجهيز</option>
                  <option value="Shipped" <?= $order['OrderStatus'] == 'Shipped' ? 'selected' : ''; ?>>تم الشحن</option>
                </select>
              <?php endif; ?>
            </form>
          </td>
          <td><?= $order['Address']; ?></td>
          <td><?= $order['ProductName']; ?></td>
          <td><?= $order['quantity']; ?></td>
          <td><?= number_format($order['Price'] * $order['quantity'], 2); ?> ريال</td>
          <td><?php if ($order['PaymentStatus'] == 'released') { echo 'تم الاستلام'; } elseif ($order['PaymentStatus'] == 'failed') { echo 'ملغي'; } else { echo 'محجوز'; } ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php else: ?>
      <p>لا توجد بيانات الآن</p>
    <?php endif; ?>
  </div>
  <script>
    function toggleDropdown() {
      const dropdownMenu = document.getElementById("dropdownMenu");
      dropdownMenu.style.display = dropdownMenu.style.display === "block" ? "none" : "block";
    }
    function toggleSidebar() {
      const sidebar = document.getElementById('sidebar');
      sidebar.style.right = (sidebar.style.right === '0px') ? '-250px' : '0px';
    }
    function showContent(page) {
      switch (page) {
        case 'account': window.location.href = 'craftman-edit-profile.php'; break;
        case 'addproduct': window.location.href = 'addproduct.php'; break;
        case 'manage-orders': window.location.href = 'craftman_manage_products.php'; break;
        case 'craftman-orders-manegment.php': window.location.href = 'craftman-orders-manegment.php'; break;
        default: window.location.href = 'logout.php';
      }
    }
    document.addEventListener("click", function(event) {
      const dropdownMenu = document.getElementById("dropdownMenu");
      const accountBtn = document.querySelector(".account-btn");
      if (!accountBtn.contains(event.target) && !dropdownMenu.contains(event.target)) {
          dropdownMenu.style.display = "none";
      }
    });
  </script>
</body>
</html>
