<?php
session_start();
$user_id = $_SESSION['user_id'] ?? null; 
$subtotal = $_GET['subtotal'] ?? 0;
$discount = $_GET['discount'] ?? 0;
$total = $_GET['total'] ?? 0;
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $address = $_POST['address'] ?? '';
    $TotalAmount = $_POST['total'] ?? 0;
    $paymentMethod = $_POST['paymentMethod'] ?? '';
    $shippingStatus = 'Pending';
    $orderStatus = 'New';
    $is_new = 1;
    $host = 'localhost';
    $db = 'jisrgp';
    $user = 'root';
    $pass = '';
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("INSERT INTO orders (CustomerID, TotalAmount, Address, ShippingStatus, OrderStatus, is_new, PaymentMethod) 
                               VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $TotalAmount, $address, $shippingStatus, $orderStatus, $is_new, $paymentMethod]);

        $orderID = $pdo->lastInsertId();
        $cart = $_SESSION['cart'];
        foreach ($cart as $item) {
            $productID = $item['ProductID'];
            $quantity = $item['Quantity'];
            $price = $item['Price'];
            $craftsmanID = $item['CraftsmanID'];
            
            $stmt = $pdo->prepare("SELECT Stock FROM product WHERE ProductID = ? FOR UPDATE");
            $stmt->execute([$productID]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($product && $product['Stock'] >= $quantity) {
                $stmt = $pdo->prepare("INSERT INTO order_items (OrderID, ProductID, Quantity, Price, CraftsmanID) 
                                       VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$orderID, $productID, $quantity, $price, $craftsmanID]);
                
                $stmt = $pdo->prepare("UPDATE product SET Stock = Stock - ?, sales_count = COALESCE(sales_count, 0) + ? WHERE ProductID = ?");
                $stmt->execute([$quantity, $quantity, $productID]);
            } else {
                echo "عذرًا، المنتج $productID غير متوفر بالكمية المطلوبة.";
                $pdo->rollBack(); 
                exit;
            }
        }
        $totalSalesStmt = $pdo->query("SELECT SUM(sales_count) AS total_sales FROM product");
        $totalSales = $totalSalesStmt->fetch(PDO::FETCH_ASSOC)['total_sales'];
        if ($totalSales > 0) {
            $percentageStmt = $pdo->prepare("UPDATE product 
                SET sales_percentage = (COALESCE(sales_count, 0) / (COALESCE(sales_count, 0) + COALESCE(Stock, 0))) * 100 
                WHERE (COALESCE(sales_count, 0) + COALESCE(Stock, 0)) > 0");
            $percentageStmt->execute();
        }
        $pdo->commit();
        header("Location: thank_you.php?orderID=" . $orderID);
        exit;
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack(); 
        }
        echo "حدث خطأ أثناء حفظ الطلب: " . $e->getMessage();
    }
}

?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>اتمام الدفع</title>
    <style>
        @font-face {
            font-family: 'TheYearOfTheCamel';
            src: url('fonts/TheYearofTheCamel-Light.otf') format('opentype');
            font-weight: normal;
            font-style: normal;
        }
        * {
            font-family: 'TheYearOfTheCamel', sans-serif;
        }
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f3e7;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            text-align: right;
            width: 600px;
        }
        h1 {
            color: #224F34;
            margin-bottom: 20px;
            font-size: 30px;
        }
        .card {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 30px;
            padding: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .summary {
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .option {
            font-weight: bold;
            color: #826B48;
            cursor: pointer;
            border-bottom: 2px solid #ccc;
            margin-bottom: 20px;
            padding-bottom: 10px; /* مسافة بين النص وخط الفصل */
            font-size: 22px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .circle {
            width: 15px;
            height: 15px;
            border: 2px solid #826B48;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .circle.selected {
            background-color: rgba(114, 92, 58, 0.2);
        }
        .circle.selected::after {
            content: '✔';
            font-size: 10px;
            color: #826B48;
        }
        .hidden {
            display: none;
        }
        .field-row {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        /* إضافة مسافة أسفل حقول التوصيل لفصلها عن قسم الشحن */
        #address {
            margin-bottom: 20px;
        }
        .field-row input {
            font-size: 18px;
            padding: 10px;
            flex: 1;
            background: #EEE9DF;
            border: 2px solid #ccc;
            border-radius: 15px;
            color: #333;
        }
        .icon-row {
            display: flex;
            gap: 20px;
            margin: 15px 0;
            align-items: center;
        }
        .icon {
            width: 70%;
            height: 70px;
            border: 3px solid #ccc;
            border-radius: 15px;
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding: 10px;
            position: relative;
            cursor: pointer;
            transition: 0.3s;
        }
        .icon:hover {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        .icon.selected {
            border: 3px solid #826B48;
            background-color: rgba(114, 92, 58, 0.2);
        }
        .icon.selected::before {
            content: '✔';
            color: #826B48;
            font-size: 18px;
            position: absolute;
            right: 10px;
            top: 10px;
        }
        .button {
            background: #725C3A;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 10px;
            display: block;
            margin: 30px auto 0;
            font-size: 22px;
            width: fit-content;
        }
        .button.enabled {
            cursor: pointer;
        }
        #stripe-form {
            display: none;
        }
        .payment-form {
    border: 1px solid #ccc;
    padding: 15px;
    margin-top: 10px;
    border-radius: 15px;
}

.payment-form .field-row {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.payment-form input {
    font-size: 18px;
    padding: 10px;
    flex: 1;
    background: #EEE9DF;
    border: 2px solid #ccc;
    border-radius: 15px;
    color: #333;
}

        .icon {
            cursor: pointer;
            padding: 10px;
            border: 1px solid #ddd;
            display: inline-block;
            margin: 5px;
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
.icon span {
    margin-right: 22px;  
   color: #725C3A;
    display: inline-block;
}

    </style>
</head>
<body>
<div class="container">
    <h1>إجمالي الطلب <span id="totalAmount">(<?php echo $total; ?> ريال)</span></h1>
    <form method="POST" action="pay.php" name="address" onsubmit="return validateForm()">
        <input type="hidden" name="total" value="<?php echo $total; ?>">
        <input type="hidden" name="paymentMethod" id="paymentMethodInput">
        <input type="hidden" name="shippingMethod" id="shippingMethodInput"> 

        <div class="card">
            <div class="option" onclick="toggle('address')">
                <div class="circle" id="circle-address"></div> عنوان التوصيل
            </div>
            <div id="address" class="hidden">
                <div class="field-row">
                    <input type="text" id="city" name="address" placeholder="المدينة" required>
                    <input type="text" id="district" name="address" placeholder="الحي" required>
                </div>
            </div>
            <div class="option" onclick="toggle('shipping')">
                <div class="circle" id="circle-shipping"></div> شركة الشحن
            </div>
            <div id="shipping" class="hidden">
                <div class="icon-row">
                    <div class="icon" style="background-image: url('images/aramex.png');" onclick="selectShipping('Aramex', 15, this)">
                        <span>15 ريال</span>
                    </div>
                    <div class="icon" style="background-image: url('images/smsa.png');" onclick="selectShipping('SMSA', 30, this)">
                        <span>30 ريال</span>
                    </div>
                </div>
            </div>
            <div class="option" onclick="toggle('payment')">
                <div class="circle" id="circle-payment"></div> الدفع
            </div>
            <div id="payment" class="hidden">
                <div class="icon-row">
                    <div class="icon" onclick="selectPaymentMethod('كاش', this)">
                        <span>دفع نقدي</span>
                    </div>
                    <div class="icon" onclick="selectPaymentMethod('فيزا', this)">
                        <span>بطاقة ائتمان</span>
                    </div>
                </div>
                <div id="creditCardForm" class="payment-form hidden">
    <div class="field-row">
        <input type="text" id="cardNumber" placeholder="رقم البطاقة" maxlength="19">
        <input type="text" id="expiryDate" placeholder="تاريخ الانتهاء MM/YY" maxlength="5">
        <input type="text" id="cvv" placeholder="رمز الأمان CVV" maxlength="3">
    </div>
</div>

            </div>
            <div class="button-container">
    <button id="confirm" class="btn" type="submit">تأكيد الدفع</button>
    <a href="cart.php" class="btn">العودة الى السلة</a>
</div>

        </div>
    </form>
</div>
<script>
    const state = {
        address: false,
        shipping: '',
        paymentMethod: ''
    };
    let total = <?php echo $subtotal; ?>;
    let shippingPrice = 0;

    document.addEventListener("DOMContentLoaded", function() {
        document.getElementById("confirm").addEventListener("click", function(event) {
            if (!validateForm()) {
                event.preventDefault();
            }
        });
    });
    function toggle(id) {
        const section = document.getElementById(id);
        section.classList.toggle('hidden');
    }
    function selectShipping(method, price, element) {
        state.shipping = method;
        shippingPrice = price;
        document.querySelectorAll('#shipping .icon').forEach(icon => icon.classList.remove('selected'));
        element.classList.add('selected');
        document.getElementById('shippingMethodInput').value = method;
        updateTotal();
    }
    function selectPaymentMethod(method, element) {
        state.paymentMethod = method;
        document.querySelectorAll('#payment .icon').forEach(icon => icon.classList.remove('selected'));
        element.classList.add('selected');
        document.getElementById('paymentMethodInput').value = method;
        const creditCardForm = document.getElementById('creditCardForm');
        if (method === 'فيزا') {
            creditCardForm.classList.remove('hidden');
            document.getElementById('cardNumber').value = '4111111111111111';
            document.getElementById('cvv').value = '123';
            document.getElementById('expiryDate').value = '12/25';
        } else if (method === 'ماستر كارد') {
            creditCardForm.classList.remove('hidden');
            document.getElementById('cardNumber').value = '5555555555554444';
            document.getElementById('cvv').value = '123';
            document.getElementById('expiryDate').value = '12/25';
        } else {
            creditCardForm.classList.add('hidden');
        }
    }
    function updateTotal() {
        let finalTotal = total + shippingPrice;
        document.getElementById('totalAmount').innerText = `(${finalTotal} ريال)`;
    }
    function validateForm() {
        if (!state.shipping) {
            alert('يرجى اختيار شركة الشحن.');
            return false;
        }
        if (!state.paymentMethod) {
            alert('يرجى اختيار طريقة الدفع.');
            return false;
        }
        const cardNumber = document.getElementById('cardNumber').value;
        const cvv = document.getElementById('cvv').value;
        const expiryDate = document.getElementById('expiryDate').value;
        if (state.paymentMethod === 'فيزا' && !validateCardNumber(cardNumber) ) {
            alert('رقم البطاقة غير صحيح. يرجى التحقق من الرقم.');
            return false;
        }
      
        return true;
    }
    function validateCardNumber(cardNumber) {
        const visaPattern = /^4[0-9]{12}(?:[0-9]{3})?$/;
        const masterCardPattern = /^5[1-5][0-9]{14}$/;
        return visaPattern.test(cardNumber) || masterCardPattern.test(cardNumber);
    }
</script>
</body>
</html>
