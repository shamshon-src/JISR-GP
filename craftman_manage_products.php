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
if (isset($_POST['update_product'])) {

    $product_id           = $_POST['product_id'];
    $product_name         = $_POST['product_name'];
    $product_description  = $_POST['product_description'];
    $product_price        = $_POST['product_price'];
    $product_stock        = $_POST['product_stock'];
    $product_category     = $_POST['product_category'];

    $stmt = $mysqli->prepare("SELECT IsApproved FROM product WHERE ProductID = ? AND CraftsmanID = ?");
    $stmt->bind_param("ii", $product_id, $craftsman_id);
    $stmt->execute();
    $stmt->bind_result($current_status);
    $stmt->fetch();
    $stmt->close();

    if ($current_status == 1) {
        $sql = "UPDATE product 
                SET ProductName = ?, Description = ?, Price = ?, Stock = ?, Category = ?, IsApproved = 1
                WHERE ProductID = ? AND CraftsmanID = ?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("ssdisii", $product_name, $product_description, $product_price, $product_stock, $product_category, $product_id, $craftsman_id);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "تم تحديث المنتج بنجاح";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            echo "حدث خطأ أثناء التحديث: " . $stmt->error;
        }
    } else {
      
        $sql = "UPDATE product 
                SET ProductName = ?, Description = ?, Price = ?, Stock = ?, Category = ?, IsApproved = NULL
                WHERE ProductID = ? AND CraftsmanID = ?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("ssdisii", $product_name, $product_description, $product_price, $product_stock, $product_category, $product_id, $craftsman_id);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "تم تحديث المنتج بنجاح";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            echo "حدث خطأ أثناء التحديث: " . $stmt->error;
        }
    }
    $stmt->close();
}

if (isset($_POST['delete_product'])) {
    $delete_id = intval($_POST['product_id']);
    $stmt_del = $mysqli->prepare("DELETE FROM product WHERE ProductID = ? AND CraftsmanID = ?");
    $stmt_del->bind_param("ii", $delete_id, $craftsman_id);
    if ($stmt_del->execute()) {
        $_SESSION['success_message'] = "تم حذف المنتج بنجاح";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        echo "حدث خطأ أثناء حذف المنتج: " . $stmt_del->error;
    }
    $stmt_del->close();
}

$sql = "SELECT 
            ProductID,
            ProductName,
            Description,
            Price,
            Stock,
            ProductImage,
            IsApproved,
            Category,
            RejectionReason
        FROM product
        WHERE CraftsmanID = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $craftsman_id);
$stmt->execute();
$result = $stmt->get_result();

$all_products      = [];
$review_products   = [];
$approved_products = [];
$rejected_products = [];

while ($row = $result->fetch_assoc()) {
    $all_products[] = $row;
    if (is_null($row['IsApproved'])) {
        $review_products[] = $row;
    } elseif ($row['IsApproved'] == 1) {
        $approved_products[] = $row;
    } elseif ($row['IsApproved'] == 0) {
        $rejected_products[] = $row;
    }
}
$stmt->close();
$mysqli->close();
?>


<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة المنتجات</title>
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
            margin-right: 0;
            padding: 20px;
        }
        h2 {
            font-size: 30px;
            color: #725C3A;
            margin-bottom: 20px;
            text-align: right;
            margin-right: 50px;
            margin-top: -13.5px;
        }
        h4 {
            font-size: 30px;
            color: #224F34;
            margin-bottom: 20px;
            text-align: right;
            margin-right: 50px;
            margin-top: -13.5px;
        }
        .acc {
            color: #725C3A;
            text-align: center;
            font-size: 16px;
        }
        .acc :hover {
            color: #224F34;
        }
        .container {
            flex-grow: 1;
            margin-right: 320px;
            padding: 10px;
            max-width: 70%;
            margin: 0 auto;
            text-align: center;
        }
        .navbar2 {
            flex-direction: row-reverse;
            display: flex;
            gap: 16px;
            justify-content: center;
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
        .product-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            direction: rtl;
            justify-content: flex-start;
            padding: 20px;
            margin-right: 58px;
        }
        /* لكل بطاقة نضيف data-id لسهولة التعرف عليها */
        .product-card {
            background: linear-gradient(to bottom, #F7F3EB 51.5%, #FEFCF9 40%);
            border: 1px solid rgb(201, 193, 182);
            border-radius: 30px;
            text-align: center;
            padding: 15px;
            box-shadow: 0 4px 9px rgba(0, 0, 0, 0.1);
            position: relative;
            width: 270px;
            height: 369px;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            cursor: pointer;
        }
        #reviewContent .product-card,
        #approvedContent .product-card {
            height: 310px;
            background: linear-gradient(to bottom, #F7F3EB 61.02%, #FEFCF9 40%);
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        .product-image {
            width: 66%;
            height: 188px;
            object-fit: cover;
            border-top-left-radius: 2px;
            border-top-right-radius: 2px;
            position: absolute;
            top: 0.5px;
            left: 50%;
            transform: translateX(-50%);
        }
        .product-details {
            padding: 150px 15px 15px;
            text-align: center;
        }
        .product-name {
            font-size: 24px;
            color: rgb(69, 55, 35);
            margin: 33px 0px;
            text-align: center;
            font-weight: bold;
        }
        .product-Category {
            font-size: 20px;
            color: #725C3A;
            margin: -27px;
            margin-bottom: 26px;
        }
        .product-price {
            font-size: 20px;
            color: #224F34;
            font-weight: bold;
            margin-bottom: 19px;
            margin-top: -14px;
        }
        .product-status {
            font-size: 16px;
            color: white;
            background-color: #8C7B65;
            padding: 5px 20px;
            border-radius: 50px;
            display: inline-block;
            font-weight: bold;
            text-align: center;
            margin-top: -30px;
        }
        .product-rejection {
            font-size: 16px;
            color: white;
            background-color: #8C7B65;
            padding: 4px 8px;
            border-radius: 50px;
            display: inline-block;
            font-weight: bold;
            text-align: center;
            margin-top:-39px;
        }
        .delete-btn {
            position: absolute;
            top: 10px;
            right: 6px;
            background: none;
            border: none;
            cursor: pointer;
            padding: 5px;
            z-index: 2;
        }
        .delete-btn img {
            width: 25px;
            height: 25px;
            transition: transform 0.2s ease-in-out;
        }
        .delete-btn:hover img {
            transform: scale(1.1);
        }
        .edit-btn {
            position: absolute;
            top: 50px;
            right: 10px;
            background: none;
            border: none;
            cursor: pointer;
            padding: 0;
            z-index: 2;
        }
        .edit-btn img {
            width: 25px;
            height: 25px;
            transition: transform 0.3s ease;
        }
        .edit-btn:hover img {
            transform: scale(1.1);
        }
        /* تنسيق النافذة المنبثقة (Modal) لتأكيد الحذف */
        .confirm-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.2);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        .confirm-modal-content {
            width: 320px;
            background-color: #fbf9f5;
            padding: 33px;
            border-radius: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
            align-items: center;
            direction: rtl;
            text-align: center;
            position: relative;
        }
        .confirm-modal-content h2 {
            font-size: 18px;
            margin-bottom: 20px;
            color: #224F34;
            margin-left: 49px;
            white-space: nowrap;
        }
        .btn-container {
            display: flex;
            gap: 20px;
            justify-content: center;
            width: 100%;
        }
        .confirm-modal-content button {
            padding: 10px 20px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            cursor: pointer;
        }
        .btn-yes {
            background-color: #725C3A;
            color: white;
        }
        .btn-yes:hover {
            transform: scale(1.1);
        }
        .btn-no {
            background-color: #ddd;
            color: #224F34;
        }
        .btn-no:hover {
            transform: scale(1.1);
        }
        p {
            text-align: center;
            font-size: 15px;
            color: #7f8c8d;
            margin-top: 20px;
        }
.modal {
  display: none;
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.2);
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal-content {
  width: 440px; 
  height: 588px;     
  margin: 13px auto;
  background-color: #fbf9f5;
  padding: 20px;
  border-radius: 8px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  display: flex;
  flex-direction: column;
  align-items: center;
  direction: rtl;
  text-align: center;
  position: relative; 
}

.modal-content .close-btn {
  position: absolute;
  top: 10px;    
  left: 14px;   
  width: 41px;   
  height: 41px;  
  cursor: pointer;
  transition: transform 0.3s ease;
}

.modal-content .close-btn:hover {
  transform: scale(1.1);
}

    .modal-content form {
      width: 100%;
    }
    .modal-content label {
      margin-top: 20px;
      text-align: right;
      margin-right: 35px;
      display: block;
      margin-bottom: 8px;
      font-weight: bold;
      color: #224F34;
      font-size: 18px;
      cursor: pointer;
    }
    .modal-content .input {
      max-width: 334px;   
      background-color: #F7F5F0;
      color:rgb(33, 28, 20);
      padding: 0.1rem 0.5rem; 
      min-height: 44px;   
      border-radius: 40px; 
      font-size: 14px;     
      border: none;
      line-height: 1.15;
      font-size: 16px;
      width: 100%;
      margin-bottom: -4px;
      padding-right: 18px;
      outline: 1px solid rgb(187, 185, 180);
    }
   

    .modal-content .input:hover {
      outline: 1px solid rgb(109, 103, 95);
    }

    .modal-content .update-btn {
      transition: all 0.3s ease-in-out;
      width: 150px;
      height: 49px;
      background-color: #725C3A;
      border-radius: 40px;
      box-shadow: 0 15px 25px -6px rgba(114, 92, 58, 0.5);
      outline: none;
      cursor: pointer;
      border: none;
      font-size: 24px;
      color: white;
      display: block;
      margin: 30px auto 0;
    }
    .modal-content .update-btn:hover {
      transform: translateY(3px);
      box-shadow: none;
      background-color: #5E4C2A;
    }
    .modal-content .update-btn:active {
      opacity: 0.5;
    }

    .category-options {
    display: flex;
    justify-content: flex-start;
    margin-top: 1px;
    margin-left: 30px;
    flex-direction: row-reverse;
}

.category-option {
    display: flex;
    align-items: left;
    cursor: pointer;
}

.category-input {
    display: none;
}

.category-label {
    display: inline-block;
    align-self: flex-start;
    align-items: center;
    background-color: #F7F5F0;
    padding: 9px 16px;
    border-radius: 20px;
    outline: 1px solid rgb(187, 185, 180);
    color: #4A4A4A;
    font-size: 18px;
    text-align: center;
    cursor: pointer;
    transition: background-color 0.3s ease, color 0.3s ease;
    margin: -10px;
    width: 75px;
}



.category-input:checked + .category-label {
    background-color: #725C3A;
    color: #fff;
}

.category-label:hover {
    outline: 1px solid rgb(109, 103, 95);
}


    </style>
</head>
<body>

<?php
    if (isset($_SESSION['success_message'])) {
        echo "<div style='background-color: #725C3A; color: white; text-align: center; padding: 10px; font-size: 18px; position: fixed; top: 0; left: 0; width: 100%; z-index: 1000;'>" 
             . $_SESSION['success_message'] . "</div>";
        unset($_SESSION['success_message']);
    }
  ?>
  
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
       <img src="<?php echo $profile_picture; ?>" alt="الحساب"style="border-radius: 50%; width: 8rem; height:8rem; object-fit: cover; ">
    </a>
 <div class="acc">
    <h2>أهلًا بك، <?php echo htmlspecialchars($first_name); ?><br></h2>
    <h3>حرفـيّ</h3>
    </div>

    <a href="addproduct.php">
    <img src="./images/add-product.png" alt="إضافة منتج"> إضافة منتج
    </a>

    <a href="craftman_manage_products.php" class="active">
        <img src="./images/pro_man.png" alt="إدارة المنتجات"> إدارة المنتجات
    </a>
    <a href="craftman-orders-manegment.php">
        <img src="./images/manage_orders.png" alt="ادارة الطلبات"> إدارة الطلبات
    </a>


    <a href="logout.php" class="logout-btn">
    <img src="./images/logout.png" alt="تسجيل الخروج"> <span>تسجيل الخروج</span>
</a>

</div>

    <div class="container">
        <h4>إدارة المنتجات</h4>
        <div class="navbar2">
            <button onclick="filterProducts('all')" class="active">جميع المنتجات</button>
            <button onclick="filterProducts('review')">قيد المراجعة</button>
            <button onclick="filterProducts('approved')">تمت الموافقة</button>
            <button onclick="filterProducts('rejected')">تم الرفض</button>
        </div>
   <div id="allContent" class="content">
      <?php if (count($all_products) > 0): ?>
        <div class="product-container">
          <?php foreach ($all_products as $product): ?>
            <div class="product-card" 
                 data-id="<?php echo $product['ProductID']; ?>" 
                 data-name="<?php echo htmlspecialchars($product['ProductName']); ?>" 
                 data-description="<?php echo htmlspecialchars($product['Description']); ?>" 
                 data-price="<?php echo htmlspecialchars($product['Price']); ?>" 
                 data-stock="<?php echo htmlspecialchars($product['Stock']); ?>" 
                 data-category="<?php echo htmlspecialchars($product['Category']); ?>"
                 onclick="location.href='craftman_product_details.php?id=<?php echo $product['ProductID']; ?>'">
              <button class="delete-btn" onclick="event.stopPropagation(); openProductDeleteModal(<?php echo $product['ProductID']; ?>)">
                <img src="./images/del.png" alt="حذف">
              </button>
              <button class="edit-btn" onclick="event.stopPropagation(); openEditModal(<?php echo $product['ProductID']; ?>)">
                <img src="./images/edit111.png" alt="تعديل">
              </button>
              <img src="<?php echo htmlspecialchars($product['ProductImage']); ?>" alt="صورة المنتج" class="product-image">
              <div class="product-details">
                <h2 class="product-name"><?php echo htmlspecialchars($product['ProductName']); ?></h2>
                <p class="product-Category">
                  <?php 
                    $cat = htmlspecialchars($product['Category']);
                    $categories = [
                      'cups'   => 'اكواب',
                      'dolls'  => 'دمى',
                      'bags'   => 'حقائب',
                      'other'  => 'اخرى'
                    ];
                    echo isset($categories[$cat]) ? $categories[$cat] : $cat;
                  ?>
                </p>
                <p class="product-price"><?php echo htmlspecialchars($product['Price']); ?> ريال</p>
                <p class="product-status">
                  <?php 
                    if (is_null($product['IsApproved'])) {
                      echo 'قيد المراجعة';
                    } elseif ($product['IsApproved'] == 1) {
                      echo 'مقبول';
                    } elseif ($product['IsApproved'] == 0) {
                      echo 'مرفوض';
                    }
                  ?>
                </p>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <p>لا توجد منتجات حتى الآن</p>
      <?php endif; ?>
    </div>
    <div id="reviewContent" class="content" style="display: none;">
      <?php if (count($review_products) > 0): ?>
        <div class="product-container">
          <?php foreach ($review_products as $product): ?>
            <div class="product-card" 
                 data-id="<?php echo $product['ProductID']; ?>" 
                 data-name="<?php echo htmlspecialchars($product['ProductName']); ?>" 
                 data-description="<?php echo htmlspecialchars($product['Description']); ?>" 
                 data-price="<?php echo htmlspecialchars($product['Price']); ?>" 
                 data-stock="<?php echo htmlspecialchars($product['Stock']); ?>" 
                 data-category="<?php echo htmlspecialchars($product['Category']); ?>"
                 onclick="location.href='craftman_product_details.php?id=<?php echo $product['ProductID']; ?>'">
              <button class="delete-btn" onclick="event.stopPropagation(); openProductDeleteModal(<?php echo $product['ProductID']; ?>)">
                <img src="./images/del.png" alt="حذف">
              </button>
              <button class="edit-btn" onclick="event.stopPropagation(); openEditModal(<?php echo $product['ProductID']; ?>)">
                <img src="./images/edit111.png" alt="تعديل">
              </button>
              <img src="<?php echo htmlspecialchars($product['ProductImage']); ?>" alt="صورة المنتج" class="product-image">
              <div class="product-details">
                <h2 class="product-name"><?php echo htmlspecialchars($product['ProductName']); ?></h2>
                <p class="product-Category">
                  <?php 
                    $cat = htmlspecialchars($product['Category']);
                    $categories = [
                      'cups'   => 'اكواب',
                      'dolls'  => 'دمى',
                      'bags'   => 'حقائب',
                      'other'  => 'اخرى'
                    ];
                    echo isset($categories[$cat]) ? $categories[$cat] : $cat;
                  ?>
                </p>
                <p class="product-price"><?php echo htmlspecialchars($product['Price']); ?> ريال</p>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <p>لا توجد منتجات قيد المراجعة</p>
      <?php endif; ?>
    </div>
    <div id="approvedContent" class="content" style="display: none;">
      <?php if (count($approved_products) > 0): ?>
        <div class="product-container">
          <?php foreach ($approved_products as $product): ?>
            <div class="product-card" 
                 data-id="<?php echo $product['ProductID']; ?>" 
                 data-name="<?php echo htmlspecialchars($product['ProductName']); ?>" 
                 data-description="<?php echo htmlspecialchars($product['Description']); ?>" 
                 data-price="<?php echo htmlspecialchars($product['Price']); ?>" 
                 data-stock="<?php echo htmlspecialchars($product['Stock']); ?>" 
                 data-category="<?php echo htmlspecialchars($product['Category']); ?>"
                 onclick="location.href='craftman_product_details.php?id=<?php echo $product['ProductID']; ?>'">
              <button class="delete-btn" onclick="event.stopPropagation(); openProductDeleteModal(<?php echo $product['ProductID']; ?>)">
                <img src="./images/del.png" alt="حذف">
              </button>
              <button class="edit-btn" onclick="event.stopPropagation(); openEditModal(<?php echo $product['ProductID']; ?>)">
                <img src="./images/edit111.png" alt="تعديل">
              </button>
              <img src="<?php echo htmlspecialchars($product['ProductImage']); ?>" alt="صورة المنتج" class="product-image">
              <div class="product-details">
                <h2 class="product-name"><?php echo htmlspecialchars($product['ProductName']); ?></h2>
                <p class="product-Category">
                  <?php 
                    $cat = htmlspecialchars($product['Category']);
                    $categories = [
                      'cups'   => 'اكواب',
                      'dolls'  => 'دمى',
                      'bags'   => 'حقائب',
                      'other'  => 'اخرى'
                    ];
                    echo isset($categories[$cat]) ? $categories[$cat] : $cat;
                  ?>
                </p>
                <p class="product-price"><?php echo htmlspecialchars($product['Price']); ?> ريال</p>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <p>لا توجد منتجات مقبولة</p>
      <?php endif; ?>
    </div>
    <div id="rejectedContent" class="content" style="display: none;">
      <?php if (count($rejected_products) > 0): ?>
        <div class="product-container">
          <?php foreach ($rejected_products as $product): ?>
            <div class="product-card" 
                 data-id="<?php echo $product['ProductID']; ?>" 
                 data-name="<?php echo htmlspecialchars($product['ProductName']); ?>" 
                 data-description="<?php echo htmlspecialchars($product['Description']); ?>" 
                 data-price="<?php echo htmlspecialchars($product['Price']); ?>" 
                 data-stock="<?php echo htmlspecialchars($product['Stock']); ?>" 
                 data-category="<?php echo htmlspecialchars($product['Category']); ?>"
                 onclick="location.href='craftman_product_details.php?id=<?php echo $product['ProductID']; ?>'">
              <button class="delete-btn" onclick="event.stopPropagation(); openProductDeleteModal(<?php echo $product['ProductID']; ?>)">
                <img src="./images/del.png" alt="حذف">
              </button>
              <button class="edit-btn" onclick="event.stopPropagation(); openEditModal(<?php echo $product['ProductID']; ?>)">
                <img src="./images/edit111.png" alt="تعديل">
              </button>
              <img src="<?php echo htmlspecialchars($product['ProductImage']); ?>" alt="صورة المنتج" class="product-image">
              <div class="product-details">
                <h2 class="product-name"><?php echo htmlspecialchars($product['ProductName']); ?></h2>
                <p class="product-Category">
                  <?php 
                    $cat = htmlspecialchars($product['Category']);
                    $categories = [
                      'cups'   => 'اكواب',
                      'dolls'  => 'دمى',
                      'bags'   => 'حقائب',
                      'other'  => 'اخرى'
                    ];
                    echo isset($categories[$cat]) ? $categories[$cat] : $cat;
                  ?>
                </p>
                <p class="product-price"><?php echo htmlspecialchars($product['Price']); ?> ريال</p>
                <p class="product-rejection">سبب الرفض: <?php echo htmlspecialchars($product['RejectionReason'] ?? 'غير محدد'); ?></p>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <p>لا توجد منتجات مرفوضة</p>
      <?php endif; ?>
    </div>

    <form id="deleteProductForm" method="POST" style="display: none;">
      <input type="hidden" name="product_id" id="deleteProductId">
      <input type="hidden" name="delete_product" value="1">
    </form>

    <div id="deleteProductModal" class="confirm-modal">
      <div class="confirm-modal-content">
        <h2>هل أنت متأكد من حذف هذا المنتج؟</h2>
        <div class="btn-container">
          <button class="btn-yes" onclick="confirmDeleteProduct()">نعم</button>
          <button class="btn-no" onclick="closeProductDeleteModal()">لا</button>
        </div>
      </div>
    </div>

    <div class="modal" id="editModal">
      <div class="modal-content">
        <img src="images/close.png" alt="إغلاق" class="close-btn" onclick="closeEditModal()">
        <form method="POST">
          <input type="hidden" name="update_product" value="1">
          <input type="hidden" name="product_id" id="edit_product_id" class="input">
          
          <label>اسم المنتج :</label>
          <input type="text" name="product_name" id="edit_product_name" class="input" required>
          
          <label>الوصف :</label>
          <input type="text" name="product_description" id="edit_product_description" class="input" required>
          
          <label>السعر :</label>
          <input type="number" name="product_price" id="edit_product_price" class="input" required min="0.5" step="0.01">
          
          <label>الكمية :</label>
          <input type="number" name="product_stock" id="edit_product_stock" class="input" required min="0">
          
          <label>التصنيف :</label>
          <div class="category-options">
            <label class="category-option">
              <input type="radio" name="product_category" value="other" class="category-input">
              <span class="category-label">أخرى</span>
            </label>
            <label class="category-option">
              <input type="radio" name="product_category" value="bags" class="category-input">
              <span class="category-label">حقائب</span>
            </label>
            <label class="category-option">
              <input type="radio" name="product_category" value="dolls" class="category-input">
              <span class="category-label">دمى</span>
            </label>
            <label class="category-option">
              <input type="radio" name="product_category" value="cups" class="category-input" required>
              <span class="category-label">أكواب</span>
            </label>
          </div>
          
          <button type="submit" class="update-btn">تحديث</button>
        </form>
      </div>
    </div>
  </div>

  
  <script>
    function toggleDropdown() {
      const dropdownMenu = document.getElementById("dropdownMenu");
      dropdownMenu.style.display = dropdownMenu.style.display === "block" ? "none" : "block";
    }
    function toggleSidebar() {
      const sidebar = document.getElementById('sidebar');
      sidebar.style.right = sidebar.style.right === '0px' ? '-250px' : '0px';
    }
    function filterProducts(page) {
      const contents = document.querySelectorAll('.content');
      contents.forEach(content => content.style.display = 'none');
      const selectedContent = document.getElementById(page + 'Content');
      if (selectedContent) selectedContent.style.display = 'block';
      const buttons = document.querySelectorAll('.navbar2 button');
      buttons.forEach(button => button.classList.remove('active'));
      const activeButton = document.querySelector(`.navbar2 button[onclick="filterProducts('${page}')"]`);
      if (activeButton) activeButton.classList.add('active');
    }
    window.onload = function() {
      filterProducts('all');
    }
        let deleteProductIdGlobal = null;
    function openProductDeleteModal(productId) {
        deleteProductIdGlobal = productId;
        document.getElementById("deleteProductModal").style.display = "flex";
    }
    function closeProductDeleteModal() {
        document.getElementById("deleteProductModal").style.display = "none";
    }
    function confirmDeleteProduct() {
        document.getElementById("deleteProductId").value = deleteProductIdGlobal;
        document.getElementById("deleteProductForm").submit();
    }

    function openEditModal(productId) {
        const card = document.querySelector('.product-card[data-id="' + productId + '"]');
        if (!card) return;
        const name = card.getAttribute('data-name');
        const description = card.getAttribute('data-description');
        const price = card.getAttribute('data-price');
        const stock = card.getAttribute('data-stock');
        const category = card.getAttribute('data-category');
                document.getElementById('edit_product_id').value = productId;
        document.getElementById('edit_product_name').value = name;
        document.getElementById('edit_product_description').value = description;
        document.getElementById('edit_product_price').value = price;
        document.getElementById('edit_product_stock').value = stock;
        
        const radios = document.getElementsByName('product_category');
        radios.forEach(radio => radio.checked = false);
        const categoryRadio = document.querySelector('input[name="product_category"][value="'+category+'"]');
        if (categoryRadio) {
            categoryRadio.checked = true;
        }
        
        document.getElementById('editModal').style.display = 'flex';
    }
    function closeEditModal() {
        document.getElementById('editModal').style.display = 'none';
    }
    
    window.onclick = function(event) {
        const editModal = document.getElementById("editModal");
        if (event.target === editModal) {
            editModal.style.display = "none";
        }
        const deleteModal = document.getElementById("deleteProductModal");
        if (event.target === deleteModal) {
            deleteModal.style.display = "none";
        }
    }
  </script>
</body>
</html>