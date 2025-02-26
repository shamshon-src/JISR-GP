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
if (isset($_SESSION['success_message'])) {
    echo "<div style='background-color: #725C3A; color: white; text-align: center; padding: 10px; font-size: 18px; position: fixed; top: 0px; left: 0; width: 100%; z-index: 1000;'>" . $_SESSION['success_message'] . "</div>";
    unset($_SESSION['success_message']);
}

$first_name = $_SESSION['first_name'] ?? '';
$last_name = $_SESSION['last_name'] ?? '';

$full_name = $first_name . ' ' . $last_name;
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $productName = $_POST['productName'] ?? '';
    $description = $_POST['productDescription'] ?? '';
    $price = $_POST['productPrice'] ?? '';
    $quantity = $_POST['productQuantity'] ?? '';
    $category = $_POST['productCategory'] ?? '';
    $productImage = $_FILES['productImage'] ?? null;

    $errors = [];

    if (empty($productName)) {
        $errors[] = "اسم المنتج مطلوب.";
    }
    if (empty($description)) {
        $errors[] = "الوصف مطلوب.";
    }
    if (empty($price) || !is_numeric($price)) {
        $errors[] = "السعر يجب أن يكون رقماً.";
    }
    if (empty($quantity) || !is_numeric($quantity)) {
        $errors[] = "الكمية يجب أن تكون رقماً.";
    }
    if (empty($category)) {
        $errors[] = "الفئة مطلوبة.";
    }
    if (isset($productImage) && $productImage['error'] === UPLOAD_ERR_OK) {
        $imageTmp = $productImage['tmp_name'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];

        if (!in_array($productImage['type'], $allowedTypes)) {
            $errors[] = "صيغة الملف غير مدعومة. يُسمح فقط بـ PNG, JPG, JPEG.";
        } else {
            $uploadDir = 'uploads/';

            $imageName = uniqid() . '_' . basename($productImage['name']);
            $imagePath = $uploadDir . $imageName;

            if (!move_uploaded_file($imageTmp, $imagePath)) {
                $errors[] = "حدث خطأ أثناء رفع الصورة.";
            }
        }
    } else {
        $errors[] = "الصورة غير مرفوعة أو هناك مشكلة في رفعها.";
    }

    if (!empty($errors)) {
        foreach ($errors as $error) {
            echo "<p style='color: red;'>$error</p>";
        }
    } else {
        $mysqli = new mysqli($host, $username, $password, $dbname);

        $sql = "INSERT INTO `product`(`ProductName`, `Description`, `Price`, `Category`, `CraftsmanID`, `IsApproved`, `RejectionReason`, `CreatedAt`, `Stock`, `ProductImage`, `CraftsmanFullName`) 
        VALUES ('$productName', '$description', '$price', '$category', '{$_SESSION['user_id']}', null, NULL, NOW(), '$quantity', '$imagePath', '$full_name')";

        if ($mysqli->query($sql) === TRUE) {
            $_SESSION['success_message'] = "سيتم مراجعة المنتج والموافقة عليه قبل رفعه";
            header("Location: addproduct.php");
            exit();
        } else {
            echo "خطأ: " . $mysqli->error;
        }
    }
}

$craftsman_id = $_SESSION['user_id'];
$sql = "SELECT 
    p.ProductName,
    p.Description,
    p.Price,
    p.Stock,
    p.ProductImage,
    p.IsApproved,
    o.OrderID,
    o.OrderStatus
FROM product p
LEFT JOIN order_items oi ON p.ProductID = oi.ProductID
LEFT JOIN orders o ON oi.OrderID = o.OrderID
WHERE p.CraftsmanID = ?";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $craftsman_id);
$stmt->execute();
$result = $stmt->get_result();

$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}

$stmt->close();
$mysqli->close();
?>
<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إضافة المنتج</title>
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

        .container {
            flex-grow: 1;
            margin-right: 320px;
            padding: 10px;
            max-width: 70%;
            margin: 0 auto;
            text-align: center;
        }

        .navbar2 {
    font-family: 'TheYearOfTheCamel';
    flex-direction: row-reverse;
    display: flex;
    gap: 15px;
    justify-content: center;
    margin-top:-10px;
}

.navbar2 button {
    background-color: #EEE9DF;
    color: white;
    border: none;
    border-radius: 55px;
    padding: 10px 20px;
    cursor: pointer;
    font-size: 20px;
    transition: all 0.3s ease;
    color:rgb(31, 24, 21);
}

.navbar2 button:hover {
    background-color: #725C3A;
}

.navbar2 button.active {
    background-color: #725C3A;
    color: #fff;
    font-weight: bold;
}
        .error-message {
            color: #d76a66;
            font-size: 16px;
            margin-bottom: 10px;
            display: none;
            text-align: center;
            font-weight: bold;
        }

        .upload-section {
            display: flex;
            flex-direction: row;
            gap: 15px;
            justify-content: space-between;
        }

        .upload-image {
            border: 2px dashed #897C68;
            padding: 20px;
            border-radius: 22px;
            text-align: center;
            position: relative;
            z-index: 500;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            order: 1;
            width: 100.5%;
            height: 200px;
            margin-top: -5px;
        }

        .upload-image img {
            max-width: 130%;
            max-height: 130px;
            border-radius: 8px;
            margin-bottom: 10px;
            order: 2;
        }

        .upload-image input {
            opacity: 0;
            position: absolute;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }

        .upload-image label {
            font-size: 24px;
            color: #897C68;
            cursor: pointer;
        }

        .upload-image .uploaded-message {
            display: none;
            color: #4A4A4A;
            font-size: 18px;
            margin-top: 10px;
            order:3;
        }

        .product-details {
            background-color:rgb(247, 242, 232);
            border: 1px solid #ddd;
             border-radius: 20px;
             box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); 
             display: flex;
            flex-direction: column;
            padding: 20px;
            gap: 15px;
            order: 2;
            width: 50%;
            text-align: right;
            margin-right: 50px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1); 

        }
        .upload-container {
    background-color:rgb(247, 242, 232);
    border: 1px solid #ddd;
    border-radius: 20px;
    padding: 20px;
    text-align: center;
    width: 55%; 
    height: 340px;
    margin-top: 0px; 
    order: 2;
    margin-right: auto;
    margin-left: auto;
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1); 
}

        .product-details .input-group {
            display: flex;
            flex-direction: column;
            gap: 7px;
        }

        .product-details label {
            font-size: 18px;
            color: #4A4A4A;
            text-align: right;
            margin-right: 2%;
        }

        .product-details input,
        .product-details select {
            padding: 15px;
            font-size: 16px;
            border: 0.1px solid #897C68;
            border-radius: 17px;
            background-color: #F7F5F0;
            color: #4A4A4A;
            text-align: right;
            direction: rtl;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .product-details input:focus,
        .product-details select:focus {
            border-color: #897C68;
            box-shadow: 0 0 8px rgba(137, 124, 104, 0.5);
            outline: none;
            
        }

        button {
            font-family: 'TheYearOfTheCamel';
            width: 400px;
            padding: 15px;
            background-color: #725C3A;
            color: #fff;
            border: none;
            border-radius: 140px;
            cursor: pointer;
            font-size: 25px;
            margin-top: 30px;
            margin-left: 0px;
        }

        button:hover {
            background-color: #5E4C2A;
        }
        
.category-options {
    display: flex;
    gap: 14px;
    justify-content: flex-start; 
    margin-top: 10px;
    flex-direction: row-reverse; 
}

.category-option {
    display: flex;
    align-items:center ;
    cursor: pointer;
}

.category-input {
    display: none; 
}

.category-label {
    background-color: #F7F5F0;
    padding: 8px 25px;
    border-radius: 25px;
    border: 1px solid #897C68;
    color: #4A4A4A;
    font-size: 20px;
    margin-right:-8px;
    text-align: center;
    cursor: pointer;
    transition: background-color 0.001s ease, color 0.3s ease;
}

.category-input:checked + .category-label {
    background-color: #725C3A; 
    color: #fff;
}

.category-label:hover {
    background-color: #ddd;
}

#allProductsContent {
    margin: 20px;
    padding: 20px;
    background-color: #fdf9f0;
    border-radius: 8px;
}
table {
    text-align: center;
    width: 90%; 
    max-width: 1000px; 
    border-collapse: collapse;
    direction: rtl;
    margin: 30px auto; 
    font-size: 19px;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1); 
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

tr:nth-child(even) {
    background-color: #fdf9f0;
}

td img {
    width: 80px;
    height: 80px;
    object-fit: cover;
}


.error-message {
    color: red;
    font-weight: bold;
    margin-top: 20px;
}

.no-products-message {
    font-size: 18px;
    color: #777;
    padding: 20px;
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
       <img src="<?php echo $profile_picture; ?>" alt="الحساب"style="border-radius: 50%; width: 8rem; height:8rem; object-fit: cover; ">
    </a>
 <div class="acc">
    <h2>أهلًا بك، <?php echo htmlspecialchars($first_name); ?><br></h2>
    <h3>حرفـيّ</h3>
    </div>

    <a href="addproduct.php" class="active">
    <img src="./images/add-product.png" alt="إضافة منتج"> إضافة منتج
    </a>

    <a href="craftman_manage_products.php">
        <img src="./images/pro_man.png" alt="إدارة المنتجات"> إدارة المنتجات
    </a>
    <a href="craftman-orders-manegment.php">
        <img src="./images/manage_orders.png" alt="ادارة الطلبات"> إدارة الطلبات
    </a>


    <a href="logout.php" class="logout-btn">
    <img src="./images/logout.png" alt="تسجيل الخروج"> <span>تسجيل الخروج</span>
</a>

</div>

 
<main>
<div class="navbar2">
    <button onclick="filterProducts('addproduct')">رفع منتج جديد</button>
    <button onclick="filterProducts('all')">المنتجات السابقة</button>
    </div>


<div id="addproductContent" class="content" style="display:block;">
    <div class="container">
        <div class="error-message" id="errorMessage" style="display:none;">
            ! يرجى تعبئة جميع الحقول
        </div>
        <form class="upload-section" method="POST" action="addproduct.php" enctype="multipart/form-data">
            <div class="upload-container">
                <div class="upload-image">
                    <input type="file" id="productImage" name="productImage" required >
                    <label for="productImage" style="display: block; text-align: center; cursor: pointer;">
                        <img src="./images/upload_image.png" alt="صورة المنتج" style="max-width: 40px; height: auto; display: block; margin: 0 auto 10px;">
                        <span>صورة المنتج</span>
                    </label>
                    <img id="imagePreview" alt="معاينة الصورة" style="display: none;">
                    <div class="uploaded-message" id="uploadedMessage" style="display:none;">! تم رفع الصورة بنجاح </div>
                    <button type="button" id="removeImageBtn" style="display: none; position: absolute; bottom: 118px; left: 190px; background: transparent; border: none; font-size: 50px; color: #725C3A; cursor: pointer;">×</button>
                    </div>
                
                <button type="submit" id="uploadBtn">رفع المنتج</button>
            </div>
            
            <div class="product-details">
                <div class="input-group">
                    <label for="productName">اسم المنتج</label>
                    <input type="text" id="productName" name="productName"
                    required>
                </div>
                <div class="input-group">
                    <label for="productDescription">وصف المنتج</label>
                    <input type="text" id="productDescription" name="productDescription"
                    required>
                </div>
                <div class="input-group">
    <label for="productPrice">السعر</label>
    <input type="number" id="productPrice" name="productPrice" min="1" step="0.01" required>
</div>

<div class="input-group">
    <label for="productQuantity">الكمية</label> 
    <input type="number" id="productQuantity" name="productQuantity" min="1" step="1" required>
</div>
                <div class="input-group">
                    <label for="productCategory">التصنيف</label>
                    <div class="category-options">
                        <label class="category-option">
                            <input type="radio" name="productCategory" value="cups" class="category-input" required>
                            <span class="category-label">أكواب</span>
                        </label>
                        <label class="category-option">
                            <input type="radio" name="productCategory" value="dolls" class="category-input">
                            <span class="category-label">دمى</span>
                        </label>
                        <label class="category-option">
                            <input type="radio" name="productCategory" value="bags" class="category-input">
                            <span class="category-label">حقائب</span>
                        </label>
                        <label class="category-option">
                            <input type="radio" name="productCategory" value="other" class="category-input">
                            <span class="category-label">أخرى</span>
                        </label>
                    </div>
                </div>


            </div>
        </form>
    </div>
</div>

<div id="allProductsContent" class="content" style="display:none;">
    <table style="width: 100%; text-align: center; border: 1px solid black;">
        <thead>
            <tr>
            <th>الصورة</th>
                <th>اسم المنتج</th>
                <th>الوصف</th>
                <th>السعر</th>
                <th>الكمية</th>
                <th>حالة الطلب</th>
                <th>الحالة في النظام</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($products)): ?>
                <?php foreach ($products as $product): ?>
                    <tr>
                    <td>
                            <img src="<?= htmlspecialchars($product['ProductImage']); ?>" alt="صورة المنتج" style="max-width: 100px;">
                        </td>
                        <td><?= htmlspecialchars($product['ProductName']); ?></td>
                        <td><?= htmlspecialchars($product['Description']); ?></td>
                        <td><?= htmlspecialchars($product['Price']); ?> ريال</td>
                        <td><?= htmlspecialchars($product['Stock']); ?></td>
                        <td>
                            <?php
                            echo htmlspecialchars($product['OrderStatus'] ?? 'لا يوجد طلب');
                            ?>
                        </td>
                        <td>
                            <?php
                            if ($product['IsApproved'] === null) {
                                echo "تحت المراجعة";
                            } elseif ($product['IsApproved'] == 1) {
                                echo "مقبول";
                            } else {
                                echo "مرفوض";
                            }
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7">لا توجد منتجات سابقة</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
</main>

<script>
   document.getElementById('productImage').addEventListener('change', function (event) {
        const file = event.target.files[0];
        const preview = document.getElementById('imagePreview');
        const message = document.getElementById('uploadedMessage');
        const label = document.querySelector('.upload-image label');
        const removeBtn = document.getElementById('removeImageBtn');

        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                preview.src = e.target.result;
                preview.style.display = 'block'; 
                message.style.display = 'block'; 
                label.style.display = 'none';   
                removeBtn.style.display = 'block'; 
            };
            reader.readAsDataURL(file);
        } else {
            preview.style.display = 'none'; 
            preview.src = '';
            message.style.display = 'none'; 
            label.style.display = 'block';  
            removeBtn.style.display = 'none'; 
        }
    });

    document.getElementById('removeImageBtn').addEventListener('click', function() {
        document.getElementById('productImage').value = '';  
        document.getElementById('imagePreview').style.display = 'none';  
        document.getElementById('uploadedMessage').style.display = 'none';  
        document.querySelector('.upload-image label').style.display = 'block';  
        this.style.display = 'none';  
    });
    function filterProducts(page) {
    const contents = document.querySelectorAll('.content');
    contents.forEach(content => {
        content.style.display = 'none';
    });

    const selectedContent = document.getElementById(page + 'Content');
    if (selectedContent) {
        selectedContent.style.display = 'block';
    }

    const buttons = document.querySelectorAll('.navbar2 button');
    buttons.forEach(button => {
        button.classList.remove('active');
    });

    const activeButton = document.querySelector(`.navbar2 button[onclick="filterProducts('${page}')"]`);
    if (activeButton) {
        activeButton.classList.add('active');
    }

    if (page === 'all') {
        loadProducts(); 
    }
}

function loadProducts() {
    const allProductsContent = document.getElementById('allProductsContent');
    
    if (!allProductsContent) return;
    
    allProductsContent.style.display = 'block';
}


    document.addEventListener('DOMContentLoaded', function() {
        filterProducts('addproduct');  
    });

    function submitForm() {
    const productName = document.getElementById('productName').value;
    const productDescription = document.getElementById('productDescription').value;
    const productPrice = document.getElementById('productPrice').value;
    const productQuantity = document.getElementById('productQuantity').value;
    const productCategory = document.querySelector('input[name="productCategory"]:checked');
    const productImage = document.getElementById('productImage').files[0];

    console.log(productName, productDescription, productPrice, productQuantity, productCategory);

    if (!productName || !productDescription || !productPrice || !productQuantity || !productCategory || !productImage) {
        document.getElementById('errorMessage').style.display = 'block';
    } else {
        document.getElementById('errorMessage').style.display = 'none';
        
        const formData = new FormData();
        formData.append('productName', productName);
        formData.append('productDescription', productDescription);
        formData.append('productPrice', productPrice);
        formData.append('productQuantity', productQuantity);
        formData.append('productCategory', productCategory.value);
        formData.append('productImage', productImage);

        fetch('addproduct.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            alert("تم رفع المنتج بنجاح!");
        })
        .catch(error => {
            console.error('حدث خطأ:', error);
            alert("حدث خطأ أثناء رفع المنتج.");
        });
    }
}

document.getElementById('allProductsContent').style.display = 'block';


    
document.addEventListener("click", function(event) {
    const dropdownMenu = document.getElementById("dropdownMenu");
    const menuIcon = document.querySelector(".menu-icon");
    if (!menuIcon.contains(event.target) && !dropdownMenu.contains(event.target)) {
        dropdownMenu.style.display = "none";
    }
});

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
                case 'addproduct':
                    window.location.href = 'addproduct.php'; 
                    break;
                    case 'manage-orders':
                    window.location.href = 'craftman_manage_products.php';
                    break;
                    case 'manage_orders':
                    window.location.href = 'manage_orders.html'; 
                    break;
                default:
                    window.location.href = 'logout.php';
            }
        }

        function toggleDropdown(id) {
        const dropdown = document.getElementById(id);
        dropdown.style.display = dropdown.style.display === "block" ? "none" : "block";
    }

    document.addEventListener("DOMContentLoaded", function () {
    const priceInput = document.getElementById("productPrice");
    const quantityInput = document.getElementById("productQuantity");

    function showWarningMessage(message) {
        let warningMessage = document.getElementById("warningMessage");
        if (!warningMessage) {
            warningMessage = document.createElement("div");
            warningMessage.id = "warningMessage";
            warningMessage.style.cssText = `
                background-color: #725C3A;
                color: white;
                text-align: center;
                padding: 10px;
                font-size: 18px;
                position: fixed;
                top: 0px;
                left: 0;
                width: 100%;
                z-index: 1000;
            `;
            document.body.prepend(warningMessage);
        }
        warningMessage.textContent = message;

        setTimeout(() => {
            if (warningMessage) {
                warningMessage.remove();
            }
        }, 5000);
    }

    function validateInput(input) {
        if (input.value !== "" && input.value < 1) {
            input.value = ""; 
            showWarningMessage(" يجب إدخال قيمة 1 أو أكثر");
        }
    }

    priceInput.addEventListener("input", function () {
        validateInput(priceInput);
    });

    quantityInput.addEventListener("input", function () {
        validateInput(quantityInput);
    });
});

function toggleDropdown() {
      const dropdownMenu = document.getElementById("dropdownMenu");
      dropdownMenu.style.display = dropdownMenu.style.display === "block" ? "none" : "block";
    }
</script>
</body>
</html>