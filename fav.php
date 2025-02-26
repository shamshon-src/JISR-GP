<?php
include("config.php");
session_start();
$user_id = $_SESSION['user_id'] ?? null;
$isLoggedIn = isset($user_id) && !empty($user_id);
$customer_id = $_SESSION['user_id'];
$first_name = isset($_SESSION['first_name']) ? $_SESSION['first_name'] : '';
if (isset($_GET['remove_from_favorites'])) {
    $product_id = intval($_GET['remove_from_favorites']);
    if ($product_id > 0) {
        $delete_sql = "DELETE FROM favorites WHERE ProductID = ? AND CustomerID = ?";
        $stmt = $mysqli->prepare($delete_sql);
        $stmt->bind_param("ii", $product_id, $customer_id);
        $stmt->execute();
        $_SESSION['message'] = $stmt->affected_rows > 0 ? "تمت إزالة المنتج من المفضلة " : "حدث خطأ أثناء إزالة المنتج.";
    } else {
        $_SESSION['message'] = "بيانات غير صحيحة.";
    }
    header("Location: fav.php");
    exit;
}
$current_page = basename($_SERVER['PHP_SELF']);

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
if (isset($_GET['add_to_cart'])) {
    $product_id = intval($_GET['add_to_cart']);
    if ($product_id > 0) {
        $check_cart_sql = "SELECT * FROM cart WHERE ProductID = ? AND CustomerID = ?";
        $stmt = $mysqli->prepare($check_cart_sql);
        $stmt->bind_param("ii", $product_id, $customer_id);
        $stmt->execute();
        $cart_result = $stmt->get_result();
        if ($cart_result->num_rows > 0) {
            $update_cart_sql = "UPDATE cart SET Quantity = Quantity + 1 WHERE ProductID = ? AND CustomerID = ?";
            $stmt = $mysqli->prepare($update_cart_sql);
            $stmt->bind_param("ii", $product_id, $customer_id);
            $stmt->execute();
        } else {
            $insert_cart_sql = "INSERT INTO cart (ProductID, Quantity, CustomerID) VALUES (?, 1, ?)";
            $stmt = $mysqli->prepare($insert_cart_sql);
            $stmt->bind_param("ii", $product_id, $customer_id);
            $stmt->execute();
        }
        $delete_from_favorites_sql = "DELETE FROM favorites WHERE ProductID = ? AND CustomerID = ?";
        $stmt = $mysqli->prepare($delete_from_favorites_sql);
        $stmt->bind_param("ii", $product_id, $customer_id);
        $stmt->execute();
        $_SESSION['message'] = "تم نقل المنتج إلى السلة.";
    } else {
        $_SESSION['message'] = "بيانات غير صحيحة.";
    }
    header("Location: fav.php");
    exit;
}
$filter_sort = isset($_GET['sort']) ? $_GET['sort'] : ''; 
$filter_status = isset($_GET['status']) ? $_GET['status'] : ''; 
$filter_classification = isset($_GET['classification']) ? $_GET['classification'] : ''; 
$category_filter = isset($_GET['category']) ? $_GET['category'] : ''; 
$sql = "SELECT favorites.ProductID, product.ProductName, product.Price, product.ProductImage, product.Stock, product.Category
        FROM favorites 
        INNER JOIN product ON favorites.ProductID = product.ProductID 
        WHERE favorites.CustomerID = ?";
if ($filter_status === 'available') {
    $sql .= " AND product.Stock > 0";
} elseif ($filter_status === 'unavailable') {
    $sql .= " AND product.Stock = 0";
}
if ($category_filter && $category_filter != '') {
    $sql .= " AND product.Category = ?";
}
if ($filter_sort === 'latest') {
    $sql .= " ORDER BY favorites.FavoritesID DESC";
} elseif ($filter_sort === 'oldest') {
    $sql .= " ORDER BY favorites.FavoritesID ASC";
}
if ($filter_classification === 'lowest-price') {
    $sql .= " ORDER BY product.Price ASC"; 
} elseif ($filter_classification === 'highest-price') {
    $sql .= " ORDER BY product.Price DESC"; 
}
$stmt = $mysqli->prepare($sql);
if ($category_filter && $category_filter != '') {
    $stmt->bind_param("is", $customer_id, $category_filter);
} else {
    $stmt->bind_param("i", $customer_id);
}
$stmt->execute();
$result55 = $stmt->get_result();
$favorites = [];
if ($result55->num_rows > 0) {
    while ($row = $result55->fetch_assoc()) {
        $favorites[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>المفضله </title>
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
            padding-top: 110px; 
            direction: rtl;
            background-color: #fdf9f0;

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
.icons img[alt="بحث"] {
  position: relative;
  z-index: 30;
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
    .logo img {
      height: 3rem;
    }
    .navbarheader a {
      text-decoration: none;
      color: #7b612b;
      font-size: 1rem;

    }
    .icons {
      display: flex;
      align-items: center;
      gap: 20px;
    }
    .icons img {
      cursor: pointer;
      transition: transform 0.3s;
    }
    .icons img:hover {
      transform: scale(1.2);
    }
    .icon-cart {
    margin-right: -10px;
    margin-left: 14px;
    width: 1.5rem;
    height: auto;
}
    .custom-dropdown {
      position: relative;
      display: inline-block;
    }
    .custom-dropdown-menu {
      display: none;
      position: absolute;
      top: 2.5rem;
      left: 50%;
      transform: translateX(-50%);
      background-color: #fff;
      border: 1px solid #ccc;
      box-shadow: 0 4px 6px rgba(0,0,0,0.1);
      border-radius: 5px;
      width: 152px;
      padding: 5px 0;
      text-align: center;
      z-index: 1000;
    }
    .custom-dropdown-menu a {
      display: flex;
      align-items: center;
      padding: 10px 15px;
      text-decoration: none;
      color: #224F34;
      font-size: 16px;
      transition: background-color 0.2s;
      white-space: nowrap;

    }
    .custom-dropdown-menu a:hover {
      background-color: #f5f5f5;
    }
    .custom-dropdown-menu .dropdown-icon {
      width: 25px;
      height: 25px;
      margin-left: 10.5px;
    }

    .custom-dropdown-menu a:last-child img {
    filter: invert(26%) sepia(34%) saturate(3115%) hue-rotate(33deg) brightness(94%) contrast(92%);
}
    .search-box {
      display: none;
      position: absolute;
      bottom: 40px;
      left: 230px;
      border: 0.5px solid rgb(146,130,106);
      background-color: #FEFCF9;
      padding: 2px 3px;
      border-radius: 25px;
      width: 190px;
      transition: all 0.3s ease;
      z-index: 20;
    }
    .search-box input {
      padding: 8px 10px;
      width: 100%;
      font-size: 10px;
      border: none;
      border-radius: 20px;
      outline: none;
      background-color: #FEFCF9;
    }
/* تنسيق مدخل النص */
.search-box input {
    padding: 8px 10px;
    width: 100%; 
    font-size: 10px;
    border: none;
    border-radius: 20px;
    outline: none;
    background-color: #FEFCF9;
}

.search-box button {
    display: none;
}

#cartIcon {
  position: relative;
  transition: transform 0.3s ease;
  top:4px;
}

#cartIcon:hover {
  transform: scale(1.1);
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

.content {
    margin-right: 0;
    padding: 20px;
    width: 100%;
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
}
.acc:hover {
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
    font-size: 25px;
    font-weight: bold;
    color: #224F34;
    margin-bottom: 10px;
    position: sticky;
    top: 0;
    z-index: 10;
    padding: 10px 0;
    margin-top: -15px;
}
.navbarrr {
    background-color: #fff;
    flex-direction: row-reverse; 
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    border-radius: 20px;
    border: 1px solid #ddd;
    position: sticky;
    top: 60px;
    z-index: 9;
}
.navbarrr a {
    color: #725C3A;
    text-decoration: none;
    font-size: 18px;
    margin: 0 30px;
    position: relative;
    font-weight: bold;
}
.navbarrr a:hover {
    color: #224F34;
}
.navbarrr a::after {
    content: '\25B8';
    font-size: 10px;
    margin-left: 5px;
    color: #725C3A;
}

.dropdown {
    display: none;
    position: absolute;
    background-color: #fff;
    border: 1px solid #ddd;
    border-radius: 5px;
    width: 200px;
    z-index: 1000;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
    margin-top: 10px;
}
.dropdown a {
    display: block;
    padding: 12px;
    color: #725C3A;
    text-decoration: none;
    font-size: 16px;
    white-space: nowrap;
}
.dropdown a:hover {
    background-color: #eee;
}
.navbar-item {
    position: relative;
    display: inline-block;
}
.navbar-item .dropdown {
    display: none;
}
.navbar-item.active .dropdown {
    display: block;
    width:150px;
    text-align:center;
}
#favorite-list {
    margin-top: 20px;
}
.product {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    background-color: #fff;
    padding: 15px;
    border-radius: 30px;
    border: 1px solid #ddd;
    position: relative;
    overflow: hidden;

    transition: transform 0.3s ease;
    direction: rtl; 
}
.product:hover {
    transform: translateX(60px); 
}
.product-image img {
    max-width: 100px;
    height: auto;
    margin-right: 20px; 
}
.product-details {
    flex: 1; 
    text-align: center;
}
.product-details h3 {
    margin: 0;
    font-size: 18px;
    color: #333;
}
.product-details .price {
    color: #725C3A;
    font-size: 16px;
}
.action-buttons {
    display: flex;
    align-items: center;
    gap: 10px;
    position: absolute;
    top: 50%;
    left: -100px;
    transform: translateY(-50%);
    opacity: 0;
    transition: left 0.3s ease, opacity 0.3s ease;
}
.product:hover .action-buttons {
    left: 20px; 
    opacity: 1; 
}
.add-to-cart-btn {
    background-color: transparent; 
    color: #725C3A; 
    font-weight: bold;
    border: 1px solid #725C3A; 
    padding: 8px 12px;
    font-size: 14px;
    cursor: pointer;
    border-radius: 5px;
    transition: background-color 0.3s ease, color 0.3s ease;
    text-decoration: none;
}
.remove-btn {
    border: none; 
    background-color: transparent; 
    transition: transform 0.3s ease;
}
.remove-btn:hover {
    transform: translateY(-5px);
    transition: transform 0.3s ease; 
    background-color: transparent; 
    color: inherit;
}
.cart-btn:hover {
    transform: translateY(-5px); 
    transition: transform 0.3s ease; 
}
.cart-btn:hover {
    transform: translateY(-5px); 
    transition: transform 0.3s ease; 
}
.no-favorites {
    text-align: center;
    color: #224F34;
    font-weight: bold;
    font-size: 24px;
    margin-top: 50px;
}
</style>
</head>
<body>
   
<header class="header">
    <div class="logo">
      <a href="homepage.php"><img src="./images/logo.png" alt="Logo"></a>
    </div>
    <nav class="navbarheader">
      <a href="homepage.php">الرئيسيـة</a>
      <a href="product.php">المنتجـات</a>
      <a href="homepage.php#aboutUs">من نـحن</a>
      <a href="homepage.php#footer">تواصل معنا</a>
    </nav>
    <div class="icons">
      <img src="images/search.png" alt="بحث" width="24" onclick="toggleSearchBox()">
      <a href="cart.php" id="cartIcon">
  <img src="images/cart.png" alt="السلة" width="24">
</a>
      <div class="custom-dropdown">
        <a class="account-btn" onclick="toggleDropdownh()">
          <img src="<?php echo $profile_picture; ?>" alt="الحساب" style="border-radius:50%; width:2.5rem; height:2.5rem; object-fit:cover;">
        </a>
        <div id="dropdownMenu" class="custom-dropdown-menu">
          <a href="customer-edit-profile.php">
            <img src="./images/user.png" alt="Profile" class="dropdown-icon"> الملف الشخصي
          </a>
          <a href="logout.php">
            <img src="./images/logout.png" alt="Logout" class="dropdown-icon"> تسجيل الخروج
          </a>
        </div>
      </div>
      <a class="menu-btn" onclick="toggleSidebar()">
        <img src="./images/line.png" alt="شريط">
      </a>
    </div>
    <div id="searchBox" class="search-box">
      <form method="POST" action="search_results.php">
        <input type="text" name="search_term" placeholder="ابحث عن منتج..." required>
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
<div class="content">
    <script>
        document.addEventListener('click', function (event) {
            const isDropdown = event.target.closest('.navbar-item');
            document.querySelectorAll('.navbar-item').forEach(item => {
                if (item === isDropdown) {
                    item.classList.toggle('active');
                } else {
                    item.classList.remove('active');
                }
            });
        });
    </script>
</head>
<body>
<div class="container">
    <?php if (isset($_SESSION['message'])): ?>
        <div class="message" style="background-color: #D9CBA7; color: #224F34; padding: 10px; margin-bottom: 15px; border-radius: 5px; text-align: center;">
            <?php 
            echo $_SESSION['message']; 
            unset($_SESSION['message']); 
            ?>
        </div>
    <?php endif; ?>
    <div class="title-bar">المنتجات المفضلة</div>
    <div class="navbarrr">
        <div class="navbar-item">
            <a href="#">فرز</a>
            <div class="dropdown">
                <a href="?sort=latest">الأحدث أولاً</a>
                <a href="?sort=oldest">الأقدم أولاً</a>
            </div>
        </div>
        <div class="navbar-item">
            <a href="#">الفئات</a>
            <div class="dropdown">
            <a href="?category=">الكل</a>
        <a href="?category=dolls">دمى</a>
        <a href="?category=cups">أكواب</a>
        <a href="?category=bags">حقائب</a>
        <a href="?category=other">أخرى</a>
            </div>
        </div>
        <div class="navbar-item">
            <a href="#">الحالة</a>
            <div class="dropdown">
                <a href="?status=available">متوفر</a>
                <a href="?status=unavailable">غير متوفر</a>
            </div>
        </div>
        <div class="navbar-item">
            <a href="#">تصنيف</a>
            <div class="dropdown">
                <a href="?classification=lowest-price">الأقل سعرًا</a>
                <a href="?classification=highest-price">الأعلى سعرًا</a>
                <a href="?classification=highest-rating">الأعلى تقييمًا</a>
            </div>
        </div>
    </div>
    <div id="favorite-list">
<?php 
if (count($favorites) > 0): 
    foreach ($favorites as $favorite): ?>
        <div class="product">
            <div class="product-image">
                <img src="<?php echo $favorite['ProductImage']; ?>" alt="صورة المنتج">
            </div>
            <div class="product-details">
                <h3><?php echo $favorite['ProductName']; ?></h3>
                <p class="price"><?php echo $favorite['Price']; ?> ريال</p>
            </div>
            <div class="action-buttons">
                <a href="fav.php?remove_from_favorites=<?php echo $favorite['ProductID']; ?>" class="remove-btn">
                    <img src="images/del2.png" alt="إزالة" style="width: 40px; height: 40px;">
                </a>
                <a href="fav.php?add_to_cart=<?php echo $favorite['ProductID']; ?>" class="cart-btn">
                    <img src="images/cartt.png" alt="إضافة إلى السلة" style="width: 20px; height: 20px;">
                </a>
            </div>
        </div>
    <?php endforeach; 
else: ?>
    <p class="no-favorites">لا توجد منتجات في المفضلة</p>
<?php endif; ?>
</div>


<script>

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
  </script>

</body>
</html>
