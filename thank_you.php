<?php
$conn = new mysqli("localhost", "root", '', "jisrgp");
if ($conn->connect_error) {
    die("خطأ في الاتصال: " . $conn->connect_error);
}
$orderID = $_GET['orderID'] ?? null;
$InvoiceDate = date('Y-m-d H:i:s');
if ($orderID) {
    $orderQuery = "SELECT OrderID, CustomerID, TotalAmount, PaymentMethod, OrderDate, Address FROM orders WHERE OrderID = ?";
    $stmt = $conn->prepare($orderQuery);
    if (!$stmt) {
        die("خطأ في تحضير استعلام الطلب: " . $conn->error);
    }
    $stmt->bind_param("i", $orderID);
    $stmt->execute();
    $orderResult = $stmt->get_result();
    $order = $orderResult->fetch_assoc();

    if ($order) {
        $totalAmount = $order['TotalAmount'] ?? 0;
        $customerID = $order['CustomerID'] ?? null;
        $paymentMethod = $order['PaymentMethod'] ?? 'غير محدد';
        $Address = $order['Address'] ?? 'غير متوفر'; 
        if (!$totalAmount || !$customerID || !$paymentMethod) {
            die("بيانات الطلب غير مكتملة. الرجاء التحقق.");
        }
        $invoiceQuery = "INSERT INTO invoice (OrderID, CustomerID, Amount, PaymentMethod) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($invoiceQuery);

        if (!$stmt) {
            die("خطأ في تحضير استعلام إدخال الفاتورة: " . $conn->error);
        }
        $stmt->bind_param("iids", $orderID, $customerID, $totalAmount, $paymentMethod);
        $executeSuccess = $stmt->execute();

        if (!$executeSuccess) {
            die("خطأ في تنفيذ استعلام إدخال الفاتورة: " . $stmt->error);
        }

    }}

$conn->close();
if (isset($_POST['payment_method'])) {
    $payment_method = $_POST['payment_method']; 
    $payment_status = 'paid'; 
    $sql_update_payment = "UPDATE orders SET payment_status = '$payment_status' WHERE id = '$order_id'";
    if ($conn->query($sql_update_payment) === TRUE) {
        echo "تم الدفع بنجاح!";
    } else {
        echo "خطأ في تحديث حالة الدفع: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>شكرًا لتسوقك معنا</title>
    <style>
        @font-face {
            font-family: 'TheYearOfTheCamel';
            src: url('fonts/TheYearofTheCamel-Light.otf') format('opentype');
            font-weight: normal;
            font-style: normal;
        }

        body {
            font-family: 'TheYearOfTheCamel', Arial, sans-serif;
            background-color: #fdf9f0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background-color: #fff;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 800px;
            color: #725C3A;
            direction: rtl; 
        }
        .container h1 {
            font-size: 32px;
            margin-bottom: 20px;
            text-align: center; 

        }
        .container h2 {
            font-size: 32px;
            margin-bottom: 20px;
            text-align: center; 

        }
        .container .icon {
            font-size: 32px;
            margin-bottom: 20px;
            text-align: center; 
        }
        .container p {
            font-size: 18px;
            margin-bottom: 30px;
            line-height: 1.6;
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
}   .invoice-details {
            margin-top: 20px;
            text-align: right;
        }
        .total {
            font-size: 18px;
            font-weight: bold;
            text-align: center;
            border-top: 1px solid #725C3A; 
            margin-top:-10px;
            margin-bottom: 1px; 
        }
        
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">🎉</div>
        <h1>شكرًا لتسوقك معنا!</h1>
        <p>تم إتمام طلبك بنجاح. نحن نقدر اختيارك لمنتجاتنا ونتطلع لخدمتك مرة أخرى قريبًا.</p>
        <div class="invoice-details">
            <h2> رقم الطلب : <?php echo $orderID; ?></h2>
            <p class="total">الإجمالي: <?php echo $totalAmount; ?> ريال</p>
            <div>
    <a href="product.php" class="btn">العودة للتسوق</a>
    <a href="customerinvoice.php?order_id=<?php echo urlencode($orderID); ?>" class="btn">تفاصيل الفاتورة</a>
</div>

</body>
</html>
