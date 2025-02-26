<?php
include("config.php");

$orderId = $_GET['order_id']; 

$query = "SELECT * FROM orders WHERE OrderID = '$orderId'";
$result8 = mysqli_query($mysqli, $query);

if (!$result8) {
    die("استعلام تفاصيل الطلب غير صحيح: " . mysqli_error($mysqli));
}

$order = mysqli_fetch_assoc($result8);

$customerId = $order['CustomerID']; 
$customerQuery = "SELECT first_name FROM users WHERE id = '$customerId'";
$customerResult = mysqli_query($mysqli, $customerQuery);
if (!$customerResult) {
    die("استعلام اسم العميل غير صحيح: " . mysqli_error($mysqli));
}

$customer = mysqli_fetch_assoc($customerResult);
$customerName = $customer['first_name']; 

$productQuery = "SELECT p.ProductName, oi.ProductID, oi.Quantity, p.Price ,p.ProductImage
                 FROM order_items oi
                 INNER JOIN product p ON oi.ProductID = p.ProductID
                 WHERE oi.OrderID = '$orderId'"; 
$productResult = mysqli_query($mysqli, $productQuery);
if (!$productResult) {
    die("استعلام المنتجات غير صحيح: " . mysqli_error($mysqli));
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تفاصيل الطلب</title>
    <?php include("admin-header.php");?>
    <?php include("admin-sidebar.php");?>
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

        html {
            font-size: 62.5%;
            overflow-x: hidden;
            scroll-padding-top: 7rem;
            scroll-behavior: smooth;
        }

        body {
            direction: rtl; 
            background-color: #fdf9f0;
            font-family: 'TheYearOfTheCamel';
        }

        .container {
            max-width: 900px;
            margin: 80px auto;
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            direction: rtl;
            border-radius: 12px;
        }

        h2 {
            color: #224F34;
            margin-bottom: 25px;
            font-size: 32px;
            text-align: center;
        }

        .order-details h3 {
            color: #2D3B2F;
            font-size: 22px;
            margin-top: 20px;
            margin-bottom: 10px;
            border-bottom: 2px solid #E1E1E1;
            padding-bottom: 10px;
        }

        .order-details h4 {
            color: #2D3B2F;
            font-size: 22px;
            margin-top: 20px;
            margin-bottom: 10px;
            border-bottom: 2px solid #E1E1E1;
            padding-bottom: 10px;
            text-align:left;
        }

        p {
            font-size: 18px;
            line-height: 1.6;
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            border-radius: 12px;
        }

        th, td {
            padding: 15px;
            text-align: center;
            border: 1px solid #ddd;
        }

        th {
            background-color:  #725C3A;
            color:  #fff;
        }

        td {
            background-color: #fff;
            color: #555;
            font-size: 1.5rem
                }

        .button {
            background: #725C3A;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 12px;
            display: block;
            margin: 30px auto 0;
            font-size: 20px;
            width: fit-content;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .button:hover {
            background-color: #3E4A39;
        }

        .button:focus {
            outline: none;
        }
        .product-img {
    width: 60px; 
    height: 60px;
    object-fit: cover; 
    border-radius: 12px;

}

    </style>

</head>
<body>

    <div class="container">
        <header>
            <h2>تفاصيل الطلب رقم <?php echo $order['OrderID']; ?></h2>
        </header>

        <div class="order-details">
            <h3>بيانات العميل</h3>
            <p><strong>اسم العميل:</strong> <?php echo $customerName; ?></p> 
            <p><strong>العنوان:</strong> <?php echo $order['Address']; ?></p>
            <p><strong>تاريخ الطلب:</strong> <?php echo $order['OrderDate']; ?></p>

            <h3>تفاصيل الشحن</h3>
            <p><strong>حالة الطلب:</strong> <?php echo $order['OrderStatus']; ?></p>

            <h3>المنتجات المطلوبة</h3>
            <table>
                <thead>
                    <tr>
                    <th> المنتج</th>
                        <th>اسم المنتج</th>
                        <th>الكمية</th>
                        <th>السعر</th>
                        <th>المجموع الفرعي</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($productResult) > 0): ?>
                        <?php while ($product = mysqli_fetch_assoc($productResult)): ?>
                            <tr>
                             
                            <td><img src="<?php echo $product['ProductImage']; ?>" alt="صورة المنتج" class="product-img"> </td>
                            <td><?php echo $product['ProductName']; ?></td>
                                <td><?php echo $product['Quantity']; ?></td>
                                <td><?php echo $product['Price']; ?> ريال</td>
                                <td><?php echo $product['Quantity'] * $product['Price']; ?> ريال</td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="4">لا توجد منتجات لهذا الطلب.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <h4>المبلغ الإجمالي: <?php echo $order['TotalAmount']; ?> ريال</h4>

            <button class="button" onclick="window.location.href='admin-orders-manegment.php'">عودة إلى إدارة الطلبات</button>
        </div>
    </div>

</body>
</html>
