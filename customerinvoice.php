<?php
session_start();
include("config.php");
$orderId = $_GET['order_id']; 
$query = "SELECT * FROM orders WHERE OrderID = '$orderId'";
$result8 = mysqli_query($mysqli, $query);
if (!$result8) {
    die("استعلام تفاصيل الطلب غير صحيح: " . mysqli_error($mysqli));
}
$order = mysqli_fetch_assoc($result8);
$customerId = $order['CustomerID']; 
$userQuery = "SELECT first_name FROM users WHERE id = '$customerId'";
$userResult = mysqli_query($mysqli, $userQuery);
if (!$userResult) {
    die("استعلام اسم المستخدم غير صحيح: " . mysqli_error($mysqli));
}
$user = mysqli_fetch_assoc($userResult);
$productQuery = "SELECT p.ProductName, p.ProductImage, oi.Quantity, p.Price
                 FROM order_items oi
                 INNER JOIN product p ON oi.ProductID = p.ProductID
                 WHERE oi.OrderID = '$orderId'";
$productResult = mysqli_query($mysqli, $productQuery);
if (!$productResult) {
    die("استعلام المنتجات غير صحيح: " . mysqli_error($mysqli));
}
if (!isset($_GET['order_id']) || empty($_GET['order_id'])) {
    die("رقم الطلب غير موجود.");
}
$orderId = intval($_GET['order_id']); 
?>
<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>فاتورة الطلب</title>
    <style>
        @font-face {
            font-family: 'TheYearOfTheCamel';
            src: url('fonts/TheYearofTheCamel-Light.otf') format('opentype');
            font-weight: normal;
            font-style: normal;
        }
        * {
            font-family: 'TheYearOfTheCamel', sans-serif;
            margin: 0; padding: 0;
            box-sizing: border-box;
            outline: none;
            border: none;
            text-decoration: none;
            text-transform: capitalize;
            transition: .2s linear;
        }
        body {
            direction: rtl; 
            background-color: #f5f5f5;
        }
        .container {
            max-width: 900px;
            margin: 80px auto;
            background-color: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        h2 {
            color: #224F34;
            margin-bottom: 25px;
            font-size: 32px;
            text-align: center;
        }
        p {
            font-size: 18px;
            line-height: 1.6;
            color: #333;
        }
        .order-details {
            display: flex;
            justify-content: flex-start; 
            align-items: flex-start; 
            margin-top: 20px;
        }
        .order-details .left-column, .order-details .right-column {
            width: 50%; 
            padding-right: 20px; 
        }
        .left-column {
            text-align: left; 
        }
        .product-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px; 
            justify-content: center;
            margin-top: 30px;
        }
        .product-box {
            width: 250px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #fff;
            text-align: center;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .product-box img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 10px;
        }
        .product-name {
            font-size: 16px;
            margin-bottom: 10px;
            color: #333;
        }
        .product-price {
            font-size: 18px;
            color: #224F34;
        }
        .logo {
            display: block;
            margin: 0 auto;
            width: 150px; 
            height: auto;
        }
        .btn {
    transition: all 0.3s ease-in-out;
    width: 190px;
    height: 50px;
    background-color: #725C3A;
    border-radius: 50px;
    box-shadow: 0 20px 30px -6px rgba(188, 183, 176, 0.5);
    outline: none;
    cursor: pointer;
    border: none;
    font-size: 26px;
    color: white;
    margin: 17px auto 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none; /* لإزالة التسطير من الروابط */
}

.btn:hover {
    transform: translateY(3px);
    box-shadow: none;
    background-color: #5E4C2A;
}

.btn:active {
    opacity: 0.5;
}  
        .products-heading {
            text-align: center; 
            color: #224F34; 
            font-size: 24px; 
            margin-top: 30px;
            font-weight: bold; 
        }
        .order-details p {
            color: #725C3A; 
        }
        .order-details h3 {
            color: #725C3A;
            font-weight: bold; 
            font-size: 24px; 
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <img src="images/logo.png" alt="اللوجو" class="logo">
            <h2>تفاصيل الطلب رقم# <?php echo $order['OrderID']; ?></h2>
        </header>
        <div class="order-details">
            <div class="right-column">
                <h3>بيانات العميل</h3>
                <p><strong>اسم العميل:</strong> <?php echo $user['first_name']; ?></p>
                <p><strong>العنوان:</strong> <?php echo $order['Address']; ?></p>
                <p><strong>تاريخ الطلب:</strong> <?php echo $order['OrderDate']; ?></p>
            </div>
            <div class="left-column">
                <h3>تفاصيل الشحن</h3>
                <p><strong>حالة الطلب:</strong> <?php echo $order['OrderStatus']; ?></p>
                <p><strong>طريقة الدفع:</strong> <?php echo $order['PaymentMethod']; ?></p>
                <p><strong>المبلغ الإجمالي:</strong> <?php echo $order['TotalAmount']; ?> ريال</p>
            </div>
        </div>
        <div class="products-heading">المنتجات المطلوبة</div>
        <div class="product-container">
            <?php if (mysqli_num_rows($productResult) > 0): ?>
                <?php while ($product = mysqli_fetch_assoc($productResult)): ?>
                    <div class="product-box">
                        <img src="<?php echo htmlspecialchars($product['ProductImage']); ?>" alt="صورة المنتج">
                        <div class="product-name"><?php echo $product['ProductName']; ?></div>
                        <div class="product-price"><?php echo $product['Price']; ?> ريال</div>
                        <div><strong>الكمية:</strong> <?php echo $product['Quantity']; ?></div>
                        <div><strong>المجموع الفرعي:</strong> <?php echo $product['Quantity'] * $product['Price']; ?> ريال</div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>لا توجد منتجات لهذا الطلب.</p>
            <?php endif; ?>
        </div>
        <button class="btn" onclick="window.location.href='my_order.php'">العودة إلى طلباتي</button>
    </div>
</body>
</html>
