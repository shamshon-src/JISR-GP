<?php
session_start(); // بدء الجلسة
include("config.php");

// التحقق مما إذا كان المستخدم مسجلاً الدخول
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // إعادة توجيه غير المسجلين إلى صفحة تسجيل الدخول
    exit;
}

$customer_id = $_SESSION['user_id'];
$checkCartQuery = "SELECT COUNT(*) as cart_count FROM cart WHERE CustomerID = $customer_id";
$cartResult = $mysqli->query($checkCartQuery);
$cartCount = $cartResult->fetch_assoc()['cart_count'];

$isLoggedIn = isset($_SESSION['user_name']);
$userName = $isLoggedIn ? $_SESSION['user_name'] : '';
$userImage = $isLoggedIn ? $_SESSION['user_image'] : '';

// الاتصال بقاعدة البيانات
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'jisrgp';
$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// الحصول على معرف العميل من الجلسة
$customer_id = $_SESSION['user_id']; 

$cleanCartQuery = "DELETE FROM cart WHERE ProductID IN (SELECT ProductID FROM product WHERE Stock < Quantity)";
$mysqli->query($cleanCartQuery);
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $productID => $item) {
        $stockQuery = "SELECT Stock FROM product WHERE ProductID = ?";
        $stmt = $mysqli->prepare($stockQuery);
        $stmt->bind_param("i", $productID);
        $stmt->execute();
        $stockResult = $stmt->get_result();

        if ($stockResult->num_rows > 0) {
            $stockQuantity = $stockResult->fetch_assoc()['Stock'];
            if ($stockQuantity == 0) {
                unset($_SESSION['cart'][$productID]); 
            }
        }
    }
}
// استعلام لجلب المنتجات من جدول السلة مع تفاصيل المنتجات
$sql = "SELECT cart.CartID, cart.ProductID, cart.Quantity, product.ProductName, product.Price, product.ProductImage, product.CraftsmanID 
        FROM cart 
        INNER JOIN product ON cart.ProductID = product.ProductID 
        WHERE cart.CustomerID = $customer_id";

$result = $conn->query($sql);

// التحقق من وجود نتائج
if ($result->num_rows > 0) {
    $cartItems = [];
    while ($row = $result->fetch_assoc()) {
        // حفظ بيانات المنتج في الجلسة
        $_SESSION['cart'][$row['ProductID']] = [
            'CartID' => $row['CartID'],
            'ProductID' => $row['ProductID'],
            'Quantity' => $row['Quantity'],
            'ProductName' => $row['ProductName'],
            'Price' => $row['Price'],
            'ProductImage' => $row['ProductImage'],
            'CraftsmanID' => $row['CraftsmanID'],
        ];
        $cartItems[] = $row;
    }
} else {
    $cartItems = [];
}

// معالجة حذف منتج من السلة
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_product_id'])) {
    $removeProductId = intval($_POST['remove_product_id']);

    // حذف المنتج من السلة بناءً على CartID و CustomerID
    $delete_sql = "DELETE FROM cart WHERE CartID = $removeProductId AND CustomerID = $customer_id";
    if ($mysqli->query($delete_sql) === TRUE) {
        header("Location: cart.php");
        exit;
    } else {
        echo "Error: " . $conn->error;
    }
}

// معالجة إفراغ السلة
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['empty_cart'])) {
    $empty_sql = "DELETE FROM cart WHERE CustomerID = $customer_id";
    if ($conn->query($empty_sql) === TRUE) {
        header("Location: cart.php");
        exit;
    } else {
        echo "Error: " . $conn->error;
    }
}




// معالجة تحديث الكمية في السلة
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart'])) {
    $cartID = intval($_POST['cart_id']);
    $quantity = intval($_POST['quantity']);

    // جلب المخزون المتاح للمنتج
    $stockQuery = "SELECT Stock FROM product WHERE ProductID = (SELECT ProductID FROM cart WHERE CartID = ?)";
    $stmt = $conn->prepare($stockQuery);
    $stmt->bind_param("i", $cartID);
    $stmt->execute();
    $stockResult = $stmt->get_result();
    $stockQuantity = $stockResult->fetch_assoc()['Stock'];

    // التحقق من توفر الكمية المطلوبة
    if ($quantity > $stockQuantity) {
        $quantity = $stockQuantity;
        $message = "الكمية المطلوبة غير متاحة. المتبقي في المخزون: $stockQuantity.";
    } 

    // تحديث الكمية في قاعدة البيانات
    $updateQuery = "UPDATE cart SET Quantity = ? WHERE CartID = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("ii", $quantity, $cartID);
    $stmt->execute();

    // تحديث الكمية في الجلسة
    if (isset($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['CartID'] === $cartID) {
                $item['Quantity'] = $quantity;
                break;
            }
        }
    }

    // إرسال الاستجابة إلى الواجهة الأمامية
    echo json_encode([
        'success' => true,
        'message' => $message,
        'updated_quantity' => $quantity,
        'remaining_stock' => $stockQuantity
    ]);
    exit;
}

// معالجة كود الخصم
$discount = 0; // قيمة الخصم تبدأ كـ 0
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['discount_code'])) {
    $discountCode = trim($_POST['discount_code']);
    $currentDate = date('Y-m-d');
    $discountQuery = "SELECT discount_percentage FROM discount_codes WHERE code = ? AND expiry_date >= ? AND is_active = 1";
    $stmt = $conn->prepare($discountQuery);

    if ($stmt === false) {
        die("Error preparing the discount query: " . $conn->error);
    }

    $stmt->bind_param("ss", $discountCode, $currentDate);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $discount = $result->fetch_assoc()['discount_percentage'];
        $_SESSION['discount_percentage'] = $discount;
        $_SESSION['discount_code'] = $discountCode;
        $successMessage = "تم تطبيق الخصم بنسبة $discount% بنجاح!";
    } else {
        $errorMessage = "الكود غير صالح أو منتهي الصلاحية.";
        $_SESSION['discount_percentage'] = 0;
    }
}

if (isset($_SESSION['discount_percentage'])) {
    $discount = $_SESSION['discount_percentage'];
} else {
    $discount = 0;
}

// حساب المجموع الفرعي وقيمة الفاتورة
$subtotal = 0;
foreach ($cartItems as $cartItem) {
    $subtotal += $cartItem['Price'] * $cartItem['Quantity'];
}
$discount_amount = ($subtotal * $discount) / 100;
$shippingCost = 15;
$total = $subtotal + $shippingCost - $discount_amount;
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>السلة</title>
        <!-- تضمين الهيدر الخاص بالمستخدم المسجل -->
        <?php include("customer_header.php"); ?>

        <!-- تضمين الشريط الجانبي الخاص بالمستخدم المسجل -->
        <?php include("homepage-sidebar.php"); ?>


    <style>
      
      @font-face {
       font-family: 'TheYearOfTheCamel';
       src: url('fonts/TheYearofTheCamel-Light.otf') format('opentype');
       font-weight: normal;
       font-style: normal;
   }
   
   
   *{
           font-family: 'TheYearOfTheCamel', sans-serif;
           margin: 0; padding: 0;
           box-sizing: border-box;
           outline: none;
           border: none;
           text-decoration: none;
           text-transform: capitalize;
           transition: .2s linear;
       }
       html{
           font-size: 62.5%;   
           overflow-x: hidden;
           scroll-padding-top: 7rem;
           scroll-behavior: smooth;
       }
       html::-webkit-scrollbar{
           width: 0.3rem;
       }
       html::-webkit-scrollbar-track{
           background: transparent;
       }
       html::-webkit-scrollbar-thumb{
           background-color: var(--white);
           border-radius: 5rem;
       }
   body {
       direction: rtl; /* جعل النص يتجه من اليمين لليسار */
   
       font-family: 'TheYearOfTheCamel';
       background-color: #fdf9f0;
   }
   
   .container {
       max-width: 1000px;
       margin: 130px auto;
       background-color: #fff;
       padding: 20px;
       border-radius: 8px;
       box-shadow: 0 0 12px rgba(0, 0, 0, 0.1);
       direction: rtl;
   }
   
   .proheader {
       display: grid;
       grid-template-columns: 1.5fr 1fr 1fr 1fr  0.5fr;
       text-align: center;
       font-weight: bold;
       font-size: 18px;
       color: #725C3A;
       padding: 10px;
   }
   
   .row {
       display: grid;
       grid-template-columns: 1.2fr 1fr 1fr 1fr 0.5fr;
       align-items: center;
       border-bottom: 1px solid #ddd;
       padding: 10px;
   }
   
   .row:last-child {
       border-bottom: none;
   }
   
   .product-info {
       display: flex;
       align-items: center;
       gap: 10px;
       text-align: center;
           font-size: 16px;
           color: #725C3A;
           border-right: 1px solid #ddd; /* خط بين الأعمدة */
   }
   .product-info product-name {
       text-align: center;
           font-size: 18px;
           color: #725C3A;
           border-right: 1px solid #ddd; /* خط بين الأعمدة */
   }
   
   .product-info img {
       width: 60px;
       height: 60px;
       object-fit: cover;
       border-radius: 5px;
       align-items: right;

   }
   
   .price, .quantity, .subtotal,.remove,.Custom {
           text-align: center;
           font-size: 16px;
           color: #725C3A;
           border-right: 1px solid #ddd; /* خط بين الأعمدة */
           display: flex;
           align-items: center;
         justify-content: center;
}
   
   .quantity input {
       width: 50px;
       border-right: 1px solid #ddd; /* خط بين الأعمدة */
       text-align: center;
       font-size: 16px;
       border: 0.4px solid #725C3A;
       border-radius: 4px;
       padding: 5px;
   }
   
   .remove button {
       border: none;
       padding: 5px 10px;
       border-radius: 4px;
       cursor: pointer;
       background-color: #725C3A;
       color: white;
   }
   /*-------------------------------------------سنعي مكانه xox-------------زر افراغ السله----------------------------------------------------------*/
           .empty-cart-container{
               display: flex;
               justify-content: flex-start;
               align-items: center;
               margin-right: -100px;
   
           }
   /*--------------------------------------------------------جزء الخصم  --------------------------------------------------------------*/
   .discount-code-container {
    margin: 15px 0;
    width: 100%; /* العرض الكامل */

}

.input-with-button {
    display: flex;
    align-items: center;
    border: 1px solid #ccc;
    border-radius: 5px;
    overflow: hidden;
    width: 100%; /* تأكيد أن المربع بأكمله سيكون بعرض السلة */
    max-width: 100%; /* عدم تحديد حد أقصى للعروض */
}

.input-with-button input {
    border: none;
    padding: 10px;
    flex: 1;
    outline: none;
    font-size: 16px;
    width: 100%; /* تأكد من أن الحقل يأخذ عرض كامل المساحة */
}

.input-with-button button {
    border: none;
    color: #725C3A;
    background-color: white;
    padding: 5px 10px;
    cursor: pointer;
    font-size: 18px;
    border-left: 1px solid #ccc;
}


   /*--------------------------------------------------------جزء الفاتوره ----------------------------------------------------------- */
   
           .summary-box {
               background-color: white;
               border-radius: 10px;
               padding: 20px;
               width: 300px;
               color: #725C3A;
               margin-top: 5px;
               border-collapse: collapse;
               
           }
   
           .summary-box .title {
               font-weight: bold;
               font-size: 18px;
               margin-bottom: 10px;
           }
   
           .summary-box .item {
               margin-bottom: 8px;
               display: flex;
               justify-content: space-between;
               font-size: 16px;
               border-bottom: 0.5px solid #ddd;
               padding-bottom: 5px;
           }
   
           .summary-box .total {
               font-weight: bold;
               font-size: 16px;
               margin-top: 10px;
               text-align: center;
           }
   
           .summary-box button {
       background-color: #725C3A;
       color: white;
       border: none;
       padding: 10px 20px;
       font-size: 16px;
       border-radius: 10px;
       cursor: pointer;
       display: inline-block;
       margin: 15px 20px 0 0; /* مسافة بين الزرين */
       transition: all 0.3s ease;
       align-items: center;

           }
           .footer {
               display: flex;
               justify-content: space-between;
               width: 100%;
               margin-top: 20px;
           }
   
           .footer .summary-box {
               margin-right: 20px;
               flex: 1;
           }
           .btn {
    display: flex; /* لجعل العناصر داخل الحاوية مرنة */
    flex-direction: row; /* ترتيب العناصر بشكل أفقي */
    justify-content: flex-start; /* محاذاة العناصر إلى اليسار */
    align-items: center; /* محاذاة العناصر عموديًا */
    gap: 10px; /* مسافة بين الأزرار */
}

.btn button {
    background-color: #725C3A; /* لون خلفية الزر */
    color: white; /* لون النص */
    border: none; /* إزالة الحدود */
    padding: 10px 20px; /* مسافة داخلية للزر */
    font-size: 16px; /* حجم النص */
    border-radius: 10px; /* حواف مستديرة */
    cursor: pointer; /* المؤشر يظهر على شكل يد عند التمرير */
    transition: all 0.3s ease; /* تأثير عند التحويم */
}

.btn button:hover {
    background-color: #5e4d30; /* تغيير اللون عند التمرير */
}

.btn a {
    color: white; /* لون النص داخل الرابط */
    text-decoration: none; /* إزالة الخط السفلي للرابط */
}
.proheader, .row {
    display: grid;
    grid-template-columns: 1.5fr 1fr 1fr 1fr 1fr 0.5fr; /* توزيع الأعمدة */
    grid-gap: 10px; /* مسافة بين الأعمدة */
    align-items: center;
    text-align: center;
    padding: 10px;
}


.product-info img {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 8px;
}

.remove button {
    padding: 5px;
    cursor: pointer;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 5px;
    background: none;
    border: none;
    margin-top: -8px;
}

.remove img {
    width: 32px; 
    height: 32px;
    object-fit: contain; 
    transition: transform 0.2s;
}

.remove img:hover {
    transform: scale(1.08);
}

.confirm-modal {
    display: none; /* إخفاء النافذة بشكل افتراضي */
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
    white-space: nowrap; 
    text-align: center;
    margin-right:0px;
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
.btn-no {
    background-color: #ddd;
    color: #224F34;
}

           
 </style>
</head>
   
<body>
    <div class="container">
    <?php if ($cartCount > 0): ?>
    <form method="POST" id="emptyCartForm" style="display: inline;">
        <input type="hidden" name="empty_cart" value="1">
        <button type="button" style="font-size:16px; border: none; padding: 15px 20px; border-radius: 15px; cursor: pointer; color: #725C3A; text-align: left; display: block; margin-left: 10px;" onclick="openEmptyCartModal();">
            هل تريد إزالة كل المنتجات من السلة ؟
        </button>
    </form>
<?php endif; ?>

<div id="emptyCartModal" class="confirm-modal">
  <div class="confirm-modal-content">
    <h2>هل أنت متأكد من إفراغ السلة بالكامل؟</h2>
    <div class="btn-container">
      <button class="btn-yes" onclick="confirmEmptyCart()">نعم</button>
      <button class="btn-no" onclick="closeEmptyCartModal()">لا</button>
    </div>
  </div>
</div>

        <div class="proheader">
            <div class="product-info">المنتج</div>
            <div class="price">السعر</div>
            <div class="quantity">الكمية</div>
            <div class="subtotal">المجموع الفرعي</div>

            <div class="remove">حذف</div>
          
        </div>

        <?php foreach ($cartItems as $cartItem): ?>
            <div class="row">
                <div class="product-info">
                     <img src="<?php echo $cartItem['ProductImage']; ?>" alt="<?php echo $cartItem['ProductName']; ?>">
                    <span><?php echo $cartItem['ProductName']; ?></span>
                </div>
                <div class="price"><?php echo $cartItem['Price']; ?> ريال</div>
                <div class="quantity">
                <input type="number" name="quantity" value="<?php echo $cartItem['Quantity']; ?>" min="1" onchange="updateQuantity(<?php echo $cartItem['CartID']; ?>, this.value)">
        </div>
                <div class="subtotal"><?php echo $cartItem['Price'] * $cartItem['Quantity']; ?> ريال</div>
               

                <div class="remove">
                    <form action="cart.php" method="POST">
                        <input type="hidden" name="remove_product_id" value="<?php echo $cartItem['CartID']; ?>">
                        <button type="submit" onclick="return confirmDelete();">
    <img src="images/reject.png" alt="Delete">
</button>
                    </form>
                </div>
                
            </div>

        <?php endforeach; ?>
 <div class="discount-code-container">
            <form action="cart.php" method="POST">
                <div class="input-with-button">
                    <input type="text" name="discount_code" placeholder="أدخل كود الخصم" required>
                    <button type="submit">تطبيق</button>
                </div>
            </form>
            <?php
            if (isset($successMessage)) {
                echo "<p style='color: green;'>$successMessage</p>";
            }
            if (isset($errorMessage)) {
                echo "<p style='color: red;'>$errorMessage</p>";
            }
            ?>
        </div>
        <div class="footer">
    <div class="summary-box">
        <div class="title">مجموع السلة</div>
        <div class="item">المجموع الفرعي: <span id="subtotal-amount"><?php echo $subtotal; ?> ريال</span></div>
        <div class="item">الخصم: <span><?php echo $discount_amount . ' ريال'; ?></span></div>
        <div class="item">تكلفة الشحن: <span>15 ريال</span></div>
        <div class="item total">الإجمالي: <span id="total-amount"><?php echo $total; ?> ريال</span></div>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 15px;">
        <div class="btn">
    <?php if ($cartCount > 0): ?>
        <form action="pay.php" method="GET">
            <input type="hidden" name="subtotal" value="<?php echo $subtotal; ?>">
            <input type="hidden" name="discount" value="<?php echo $discount; ?>">
            <input type="hidden" name="total" value="<?php echo $total; ?>">
            <button type="submit">انتقل إلى الدفع</button>
        </form>
    <?php else: ?>
        <button type="button" disabled style="background-color: #ccc; cursor: not-allowed;">انتقل إلى الدفع</button>
    <?php endif; ?>

    <button>
        <a href="product.php">العودة لصفحة المنتجات</a>
    </button>
</div>

        </div>
       
    </div>
</div>

    </div>
    <script>
        function confirmDelete() {
            return confirm("هل أنت متأكد أنك تريد إزالة هذا المنتج؟");
        }

        function confirmEmptyCart() {
            return confirm("هل أنت متأكد أنك تريد إفراغ السلة بالكامل؟");
        }
        function updateQuantity(cartID, quantity) {
    const formData = new FormData();
    formData.append('update_cart', true);
    formData.append('cart_id', cartID);
    formData.append('quantity', quantity);

    fetch('cart.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload(); // إعادة تحميل الصفحة لتحديث الكمية
        } else {
            alert('حدث خطأ أثناء تحديث الكمية');
        }
    });
}
        function updateCart() {
    const rows = document.querySelectorAll('.row');
    let subtotal = 0;

    rows.forEach(row => {
        const price = parseFloat(row.querySelector('.price').textContent.replace(' ريال', ''));
        const quantity = parseInt(row.querySelector('.quantity input').value);
        const subtotalElement = row.querySelector('.subtotal');

        const rowTotal = price * quantity;
        subtotal += rowTotal;

        subtotalElement.textContent = rowTotal + ' ريال';
    });

    // إضافة الخصم
    const discount = <?php echo $discount; ?>; // احصل على قيمة الخصم من الـ PHP
    const shippingCost = 15;

    // حساب الإجمالي مع الخصم
    const total = subtotal + shippingCost - discount;

    document.getElementById('subtotal-amount').textContent = subtotal + ' ريال';
    document.getElementById('total-amount').textContent = total + ' ريال';
    document.getElementById('subtotal-input').value = subtotal;
}
document.querySelector('form[action="pay.php"]').addEventListener('submit', function(event) {
    const cartItems = <?php echo json_encode($cartItems); ?>;
    if (cartItems.length === 0) {
        event.preventDefault(); // منع إرسال النموذج
        alert('السلة فارغة. يرجى إضافة منتجات قبل الانتقال إلى الدفع.');
    }
});

    </script>
    <script>
function updateQuantity(cartID, quantity) {
            const formData = new FormData();
            formData.append('update_cart', true);
            formData.append('cart_id', cartID);
            formData.append('quantity', quantity);

            fetch('cart.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload(); // إعادة تحميل الصفحة لتحديث الكمية
                } else {
                    alert('حدث خطأ أثناء تحديث الكمية');
                }
            });
        }

        // وظائف النافذة الخاصة بتأكيد إفراغ السلة
        function openEmptyCartModal() {
            document.getElementById("emptyCartModal").style.display = "flex";
        }
        function closeEmptyCartModal() {
            document.getElementById("emptyCartModal").style.display = "none";
        }
        function confirmEmptyCart() {
            document.getElementById("emptyCartForm").submit();
        }

        function updateQuantity(cartID, quantity, stock) {
    if (quantity > stock) {
        alert("الكمية المطلوبة أكثر من المخزون المتاح.");
        quantity = stock;
    }
    var formData = new FormData();
    formData.append('update_cart', true);
    formData.append('cart_id', cartID);
    formData.append('quantity', quantity);

    fetch('cart.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } 
    });
}
</script>
</body>
</html>

<?php $conn->close(); ?>
