<?php
session_start();
include("config.php");
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: login.php");
    exit();
}
$current_page = basename($_SERVER['PHP_SELF']);

$user_id = $_SESSION['user_id'];
$query = "SELECT profile_picture FROM users WHERE id = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($profile_picture);
$stmt->fetch();
$stmt->close();
if (empty($profile_picture)) {
    $profile_picture = 'images/usercust.png';
}
$first_name = $_SESSION['first_name'];
// التأكد من أن المستخدم مسجل الدخول
$user_id = $_SESSION['user_id'] ?? null;
$isLoggedIn = isset($user_id) && !empty($user_id);

if (!$isLoggedIn) {
    echo "يرجى تسجيل الدخول لعرض الطلبات.";
    exit;
}

// إعداد الاتصال بقاعدة البيانات
$host = 'localhost';
$db = 'jisrgp';
$user = 'root';
$pass = '';
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // جلب الطلبات بناءً على معرف المستخدم
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE CustomerID = ? ORDER BY OrderDate DESC");
    $stmt->execute([$user_id]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "خطأ في الاتصال بقاعدة البيانات: " . $e->getMessage();
    exit;
}

// معالجة إلغاء الطلب
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cancel_order'])) {
    $order_id = $_POST['order_id'];

    // جلب الطلب للتحقق من صلاحيته للإلغاء
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE OrderID = ? AND CustomerID = ?");
    $stmt->execute([$order_id, $user_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($order) {
        $order_date = strtotime($order['OrderDate']);
        $days_difference = (time() - $order_date) / (60 * 60 * 24);

        if (in_array(strtolower($order['OrderStatus']), ['new', 'unpaid']) && $days_difference <= 2) {
            // تحديث حالة الطلب إلى "ملغي"
            $update_stmt = $pdo->prepare("UPDATE orders SET OrderStatus = 'Cancelled' WHERE OrderID = ?");
            $update_stmt->execute([$order_id]);

            echo "<script>alert('تم إلغاء الطلب بنجاح.'); window.location.href='my_order.php';</script>";
            exit;
        } else {
            echo "<script>alert('لا يمكنك إلغاء الطلب بعد مرور يومين من تاريخ الطلب.');</script>";
        }
    } else {
        echo "<script>alert('حدث خطأ، تأكد من صلاحية الطلب للإلغاء.');</script>";
    }
}

// معالجة تأكيد وصول الطلب
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['confirm_delivery'])) {
    $order_id = $_POST['order_id'];

    // جلب الطلب للتحقق من صلاحيته لتأكيد الوصول
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE OrderID = ? AND CustomerID = ?");
    $stmt->execute([$order_id, $user_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($order) {
        if (strtolower($order['OrderStatus']) === 'shipped') {
            // تحديث حالة الطلب إلى "تم الوصول"
            $update_stmt = $pdo->prepare("UPDATE orders SET OrderStatus = 'Delivered' WHERE OrderID = ?");
            $update_stmt->execute([$order_id]);

            echo "<script>alert('تم تأكيد وصول الطلب بنجاح.'); window.location.href='my_order.php';</script>";
            exit;
        }
}}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة الطلبات</title>
    <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css">

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
            padding-top: 110px; /* المسافة من أعلى الصفحة */
            direction: rtl;
            background-color: #fdf9f0;

        }

         .dropdown {
    position: relative;
    display: inline-block;
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
.dropdown-icon {
    width: 25px;
    height: 25px;
    margin-left: 10.5px;
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
.sidebar .account-icon img {
    width: 5rem; 
    margin: auto;
}
.sidebar a.logout-btn {
           margin: auto 10px 70px auto;
           text-align: center;
           font-size:20px;
        }
        .sidebar a.logout-btn img {
            width: 2.9rem;
            height: auto;
            margin-right: 15px;
}

       .sidebar a {
    text-align: center;
    font-size: 21px;
    display: flex;
    align-items: center;
    padding: 10px 15px;
    text-decoration: none;
    color: #7b612b;
    margin: 5px 40px 8px;
    flex-direction: row; 
    justify-content: flex-start; 
}
.sidebar a img {
    margin-top: -2px; 
    filter: none;
    transition: transform 0.3s ease-in-out, filter 0.3s ease-in-out;
}
.icon-home {
    margin-right: -10px;
    margin-left: 14px;
    width: 1.5rem;
    height: auto;
}
.icon-orders {
    margin-right: -7px;
    margin-left: 14px;
    width: 1rem;
    height: auto;
}
.icon-fav {
    margin-right: -14px;
    margin-left: 12px;
    width: 1.9rem;
    height: auto;
}
.icon-cart {
    margin-right: -10px;
    margin-left: 14px;
    width: 1.5rem;
    height: auto;
}
.cart-icon {
    position: relative;
  transition: transform 0.3s ease;
  top:4px;
}
.sidebar a.active .icon-home,
.sidebar a.active .icon-orders,
.sidebar a.active .icon-fav,
.sidebar a.active .icon-cart {
    filter: brightness(0) saturate(100%) invert(14%) sepia(59%) saturate(468%) hue-rotate(99deg) brightness(93%) contrast(93%);
    transform: scale(1.1);غغ
    transition: all 0.3s ease-in-out;
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
        .acc h3 {
    font-size: 1.2rem; 
    color: #7b612b;
    margin-top: -10px;
    margin-bottom: 24px;
    text-align: center;
}
.sidebar a.active img {
    filter: none;
}
.menu-item {
        position: relative;
    }
    .submenu {
        display: none;
        padding-left: 20px;
        background-color:#EEE9DF;
    }
    .submenu a {
        padding: 8px 10px;
        font-size: 14px;
        border: none;
        color: #7b612b;
    }
    .submenu a:hover {
        background-color:#ddd;
    }
    .arrow {
        margin-left: auto;
        font-size: 12px;
    }
    .menu-item.active .submenu {
        display: block;
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
            margin-right: 54px;
            margin-top: 5px;
        }
        .acc {
            color: #725C3A;
            text-align: center; 
            font-size:1.5rem;
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
        .container {
            max-width: 800px;
            margin: 20px auto;
            padding: 0 20px;
        }
        .title-bar {
            text-align: right;
            font-size: 33px;
            font-weight: bold;
            color: #224F34;
            margin-bottom: 29px;
            margin-top:0px;
        }
        .navbar2 {
    display: flex;
    gap: 16px;
    justify-content: center;
}

.navbar2 button {
    background-color: #EEE9DF;
    color: rgb(31, 26, 24);
    border: none;
    border-radius: 55px;
    margin-bottom:15px;
    padding: 13px 25px;
    cursor: pointer;
    font-size: 18px;
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

        .order {
            border: 1px solid #ddd;
            margin: 10px auto;
            padding: 10px;
            background-color: #f9f9f9;
            border-radius: 30px;
            text-align: right;
        }
        .order h3 {
            margin: 0 0 10px;
            margin-right:10px;
            color: white;
            background-color:rgb(128, 127, 126);
            padding: 7px 18px;
            font-size:15px;
            border-radius: 50px;
            display: inline-block;
            font-weight: bold;
            text-align: center;
        }
        .order h3 {
    margin: 0 0 10px;
    margin-right: 10px;
    color: white;
    padding: 7px 18px;
    font-size: 15px;
    border-radius: 50px;
    display: inline-block;
    font-weight: bold;
    text-align: center;
}

.order.new h3 {
    background-color:rgb(163, 184, 197); 
}

.order.pending h3 {
    background-color:rgb(171, 166, 147); 
}

.order.shipped h3 {
    background-color:rgb(204, 180, 106); 
}

.order.accepted h3 {
    background-color:rgb(98, 146, 117); 
}

.order.cancelled h3 {
    background-color:rgb(138, 133, 130);  


}
        .order #order-id {
            font-size: 14px;
            margin-right:10px;
            color: #6C746F;
            margin-top: -5px;
        }
        .swiper {
            width: 100%;
            padding: 10px 0;
            position: relative;
        }
        .swiper-slide img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            margin-right:43px;
        }
        .swiper-button-next, .swiper-button-prev {
            color: #725C3A;
            font-size: 24px;
            transform: scale(0.5);
        }
        .swiper-button-next:after {
            content: '>';
        }
        .swiper-button-prev:after {
            content: '<';
        }
        
        .buttons-container {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 10px;
            margin-top: 10px;
        }
        .order-button {
            background-color: #EEE9DF;
            border: none;
            padding: 8px 15px;
            font-size: 16px;
            color: #725C3A;
            border-radius: 8px;
            cursor: pointer;
            margin-top:-8px;
        }
        .order-button:hover, .order-button:active {
            background-color: #7D6B4F;
            color: #ffffff;
        }
        .order-button a {
            text-decoration: none;
            color: inherit;
        }
        .total-price {
            font-size: 16px;
            font-weight: bold;
            color: #696766;
            margin-left: auto;
            align-self: center;
            margin-right:29px;
        }
        /* تخصيص الألوان */
        .new .status-circle {
            background-color:rgb(44, 127, 183);
        }
        .pending .status-circle {
            background-color: #965293;
        }
        .accepted .status-circle {
            background-color: #965253;
        }
        .shipped .status-circle {
            background-color: #4D8D66;
        }
        .cancelled .status-circle {
            background-color:rgb(130, 131, 131);

        }

        .dropdown {
    position: relative;
    display: inline-block;
}

.dropdown-menu {
    display: none;
    position: absolute;
    margin-top: 8px;
    right: 9;
    background-color: #fff;
    border: 1px solid #ddd;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    border-radius: 8px;
    z-index: 1000;
    width: 150px;
    padding: 5px 0;
    direction: rtl;
    text-align: right;
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
    font-size: 14px;
    transition: background-color 0.2s ease;
}

.dropdown-menu a:hover {
    background-color: #f5f5f5;
}

.dropdown-menu .dropdown-icon {
    width: 20px;
    height: 20px;
    margin-left: 10px;
}

.dropdown-menu a:last-child img {
    filter: invert(26%) sepia(34%) saturate(3115%) hue-rotate(33deg) brightness(94%) contrast(92%);
}

   
/* تنسيقات الهيدر */
        .header {
            top: 0;
            right: 0;
            left: 0;
            background-color: #fdf9f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: fixed;
            padding: 1.5rem 7%;
            z-index: 10;
            direction:rtl;

        }
        

        .header .logo img {
            height: 3rem;
            margin-top:13px;
        }

     
        .header .navbarheader{
    display: flex;
    gap: 3.2rem;
    
}

.header .navbarheader
a {
    font-size: 1.2rem;
    color: #7b612b;
    font-weight: bold;
    text-decoration: none;
    transition: color 0.3s;
    margin-top:13px;
}
.header .account-btn img {
    width: 1.9rem !important;
    height: 1.9rem !important;
}

.header .navbarheader
a:hover {
    color:#A4AC86;
}
        .icons {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .icons img {
            cursor: pointer;
    transition: transform 0.3s ease;
        }

        .icons img:hover {
            transform: scale(1.2);
        }

       /* مربع البحث */
       .search-box {
    display: none;
    position: absolute;
    bottom: 40px;
    left: 230px;
    border: 0.5px solid rgb(146, 130, 106);
    background-color: #FEFCF9;
    padding: 2px 3px;
    border-radius: 25px;
    width: 190px;
    transition: all 0.3s ease;
    z-index: -1;

}

/* تنسيق مدخل النص */
.search-box input {
    padding: 8px 10px;
    width: 100%; /* ليأخذ العرض الكامل */
    font-size: 10px;
    border: none;
    border-radius: 20px;
    outline: none;
    background-color: #FEFCF9;
}

.search-box button {
    display: none; /* إخفاء الزر */
}



.links-container {
    display: flex; 
    justify-content: center; 
    gap: 2.5rem;
    position: absolute; 
    top: 41px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 10; 
}

p.no-content {
    text-align: center;
    font-size: 15px;
    color: #7f8c8d;
    margin-top: 20px;
}

    </style>

</head>
<body>
    
<header class="header">
<div class="logo">
        <a href="#" onclick="toggleMenu()">
            <img src="./images/logo.png" alt="Logo">
        </a>
    </div>


    <!-- القائمة -->
    <nav class="navbarheader">
        <a href="homepage.php">الرئيسيـة</a>
        <a href="product.php">المنتجـات</a>
        <a href="homepage.php#aboutUs">من نـحن</a>
        <a href="homepage.php#footer">تواصل معنا</a>
    </nav>

    <div class="icons">
    <img src="images/search.png" alt="بحث" width="24px" onclick="toggleSearchBox()">
    <a href="cart.php">
  <img src="images/cart.png" alt="السلة" width="24px" class="cart-icon">
</a>

<div class="dropdown">
            <a class="account-btn" onclick="toggleDropdownh()">
            <img src="<?php echo $profile_picture; ?>" alt="الحساب" style="border-radius: 50%; width: 2.5rem; height: 2.5rem; object-fit: cover;">
            <div id="dropdownMenu" class="dropdown-menu">
                <a href="edit-profile.php">
                    <img src="./images/usercust.png" alt="Profile" class="dropdown-icon">
                    الملف الشخصي
                </a>
                <a href="logout.php">
                    <img src="./images/logout.png" alt="Logout" class="dropdown-icon">
                    تسجيل الخروج
                </a>
            </div>
        </div>
        <a class="menu-btn" onclick="toggleSidebar()">
            <img src="./images/line.png" alt="شريط">
        </a>

    </div>
    <script>
    function toggleDropdownh() {
    const dropdownMenu = document.getElementById("dropdownMenu");
    dropdownMenu.style.display = dropdownMenu.style.display === "block" ? "none" : "block";
}

</script>
    </div>
    <div id="searchBox" class="search-box">
    <form method="POST" action="search_results.php">
        <input type="text" name="search_term" placeholder="ابحث عن منتج..." required>
        <button type="submit" name="search">
            <img src="images/search.png" width="24px">
        </button>
    </form>
</div>
</header>

 <div class="sidebar" id="sidebar">
    <a href="#" class="account-icon">
        <img src="<?php echo $profile_picture; ?>" alt="الحساب" style="border-radius: 50%; width: 8rem; height: 8rem; object-fit: cover; margin: auto;">
    </a>
    <div class="acc">
        <h2>أهلًا بك، <?php echo htmlspecialchars($first_name); ?><br></h2>
        <h3>عميل</h3>
    </div>
    <nav class="menu">
    <div class="menu-item">
        <a href="homepage.php" class="menu-item <?php echo ($current_page == 'homepage.php') ? 'active' : ''; ?>">
            <img src="./images/home.png" alt="الرئيسية" class="icon-home"> الرئيسية
        </a>
        <a href="my_order.php" class="menu-item <?php echo ($current_page == 'my_order.php') ? 'active' : ''; ?>">
            <img src="./images/orders.png" alt="طلباتي" class="icon-orders"> طلباتـي
        </a>
        <a href="fav.php" class="menu-item <?php echo ($current_page == 'fav.php') ? 'active' : ''; ?>">
            <img src="images/fav1.png" alt="المفضلة" class="icon-fav"> المفضلـة
        </a>
        <a href="cart.php" class="menu-item <?php echo ($current_page == 'cart.php') ? 'active' : ''; ?>">
            <img src="./images/cart1.png" alt="السلة" class="icon-cart"> السلـة
        </a>
    </div>
</nav>


    <a href="logout.php" class="logout-btn">
        <img src="./images/logout1.png" alt="تسجيل الخروج"> <span>تسجيل الخروج</span>
    </a>
</div>


<div class="container">
    <div class="title-bar">طلباتـي</div>
    <div class="navbar2">
    <button onclick="filterOrders('all', event)" class="active">جميع الطلبات</button>
    <button onclick="filterOrders('new', event)">جديد</button>
    <button onclick="filterOrders('pending', event)">قيد التجهيز</button>
    <button onclick="filterOrders('shipped', event)">تم الشحن</button>
    <button onclick="filterOrders('accepted', event)">تم التوصيل</button>
    <button onclick="filterOrders('cancelled', event)">تم الإلغاء</button>
</div>

    <div id="orders">
    <?php foreach ($orders as $order): ?>
            <div class="order <?php echo strtolower($order['OrderStatus']); ?>" data-status="<?php echo strtolower($order['OrderStatus']); ?>">
            <?php
// تحويل حالات الطلب إلى اللغة العربية
$status_translation = [
    'New' => 'جديد',
    'pending' => 'قيد التجهيز',
    'Shipped' => 'تم الشحن',
    'accepted' => 'تم التوصيل',
    'Cancelled' => 'تم الإلغاء'

];
$translated_status = $status_translation[$order['OrderStatus']] ?? $order['OrderStatus'];
?>
</h3>
<h3><span class="status-circle"></span><?php echo $translated_status; ?></h3>
                <div class="swiper">
                    <div class="swiper-wrapper">
                        <?php
                        $stmt = $pdo->prepare("
                            SELECT order_items.*, product.ProductImage 
                            FROM order_items
                            INNER JOIN product ON order_items.ProductID = product.ProductID
                            WHERE order_items.OrderID = ?");
                        $stmt->execute([$order['OrderID']]);
                        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

                        foreach ($items as $item): ?>
                            <div class="swiper-slide">
                                <img src="<?php echo htmlspecialchars($item['ProductImage']); ?>" alt="Product Image">
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="swiper-button-next"></div>
                    <div class="swiper-button-prev"></div>
                </div>
                <div class="buttons-container">
    <span class="total-price">السعر الإجمالي: <?php echo $order['TotalAmount']; ?> ريال</span>
    <a href="customerinvoice.php?order_id=<?php echo $order['OrderID']; ?>" class="order-button">عرض الفاتورة</a>

    <?php 
    $order_date = strtotime($order['OrderDate']);
    $days_difference = (time() - $order_date) / (60 * 60 * 24);
    if (in_array(strtolower($order['OrderStatus']), ['new', 'unpaid']) && $days_difference <= 2): ?>
        <form method="POST" style="display:inline;">
            <input type="hidden" name="order_id" value="<?php echo $order['OrderID']; ?>">
            <button type="submit" name="cancel_order" class="order-button cancel-button">إلغاء الطلب</button>
        </form>
    <?php endif; ?>
    <?php if (strtolower($order['OrderStatus']) == 'accepted'): ?>
    <a href="review_order.php?order_id=<?php echo $order['OrderID']; ?>" class="order-button">تقييم</a>
<?php endif; ?>
    <?php if (strtolower($order['OrderStatus']) === 'shipped'): ?>
        <form method="POST" style="display:inline;">
            <input type="hidden" name="order_id" value="<?php echo $order['OrderID']; ?>">
            <button type="submit" name="confirm_delivery" class="order-button">تأكيد الوصول</button>
        </form>
    <?php endif; ?>
</div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
<script>
function filterOrders(status, event) {
    // إزالة الصنف النشط من جميع الأزرار
    document.querySelectorAll('.navbar2 button').forEach(btn => btn.classList.remove('active'));
    // تفعيل الزر الذي تم النقر عليه
    event.currentTarget.classList.add('active');

    // الحصول على جميع الطلبات
    const orders = document.querySelectorAll('.order');
    let visibleCount = 0;

    orders.forEach(order => {
        if (status === 'all' || order.dataset.status === status) {
            order.style.display = 'block';
            visibleCount++;
        } else {
            order.style.display = 'none';
        }
    });

    // التحقق مما إذا كان هناك عناصر ظاهرة
    const ordersContainer = document.getElementById('orders');
    let noContentMessage = document.getElementById('noContentMessage');

    if (visibleCount === 0) {
        // إذا لم تكن الرسالة موجودة قم بإنشائها
        if (!noContentMessage) {
            noContentMessage = document.createElement('p');
            noContentMessage.id = 'noContentMessage';
            noContentMessage.className = 'no-content';
            noContentMessage.textContent = 'لا يوجد محتوى الان';
            ordersContainer.appendChild(noContentMessage);
        }
    } else {
        // إذا كان هناك عناصر ظاهرة وتوجد رسالة "لا يوجد محتوى" قم بحذفها
        if (noContentMessage) {
            noContentMessage.remove();
        }
    }
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
                case 'homepage': 
                    window.location.href = 'homepage.php'; 
                    break;
                    case 'orders':
                    window.location.href = 'my_order.php'; 
                    break;
                    case 'cart':
                    window.location.href = 'cart.php'; 
                    break;
                    case 'fav':
                    window.location.href = 'fav.php'; 
                    break;
                default:
                    window.location.href = 'logout.php'; 
            }
        }

    function toggleDropdownh() {
      const dropdownMenu = document.getElementById("dropdownMenu");
      dropdownMenu.style.display = dropdownMenu.style.display === "block" ? "none" : "block";
    }
    function toggleSearchBox() {
      var searchBox = document.getElementById("searchBox");
      // إذا كان مربع البحث مخفي أو لم يتم تحديد قيمة العرض له، نجعله ظاهرًا
      if (searchBox.style.display === "none" || searchBox.style.display === "") {
          searchBox.style.display = "block";
      } else {
          searchBox.style.display = "none";
      }
  }

  window.onload = function() {
      // تنفيذ الفلترة لعرض جميع الطلبات
      filterOrders('all', { currentTarget: document.querySelector('.navbar2 button.active') });
  };
</script>

<script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
<script>
   function filterOrders(status) {
    document.querySelectorAll('.order').forEach(order => {
        if (status === 'shipped') {
            if (order.dataset.status === 'shipped' || order.dataset.status === 'delivered') {
                order.style.display = 'block';
            } else {
                order.style.display = 'none';
            }
        } else {
            order.style.display = (status === 'all' || order.dataset.status === status) ? 'block' : 'none';
        }
    });
}
    const swiper = new Swiper('.swiper', {
        slidesPerView: 6,
        spaceBetween: 10,
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
    });
</script>
</body>
</html>
