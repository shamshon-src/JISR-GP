<?php
include("config.php");
session_start();
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$isLoggedIn = isset($_SESSION['user_name']);
$userName = $isLoggedIn ? $_SESSION['user_name'] : '';
$userImage = $isLoggedIn ? $_SESSION['user_image'] : '';
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'jisrgp';
$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
if (!isset($_SESSION['user_id'])) {
    $customer_id = null;
} else {
    $customer_id = $_SESSION['user_id'];
}
$isLoggedIn = isset($_SESSION['user_id']);
if ($isLoggedIn) {
    $customer_id = $_SESSION['user_id'];
    $userName = $_SESSION['first_name'];
    $userImage = $_SESSION['user_image'] ?? '';
} else {
    $userName = null;
    $userImage = null;
}
if ($isLoggedIn) {
    $userName = $_SESSION['first_name'];
    $userImage = $_SESSION['user_image'] ?? ''; 
} else {
    $userName = null;
    $userImage = null;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }
    $product_id = $_POST['product_id'];
    $sql = "SELECT * FROM product WHERE ProductID = $product_id";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
        $customer_id = $_SESSION['user_id']; 
        $quantity = 1; 
        $product_name = $product['ProductName']; 
        $product_price = $product['Price']; 
        $craftsman_id = $product['CraftsmanID'];
        $stock_quantity = $product['Stock']; 
        $check_cart_sql = "SELECT * FROM cart WHERE CustomerID = $customer_id AND ProductID = $product_id";
        $check_result = $conn->query($check_cart_sql);
        if ($check_result->num_rows > 0) {
            $update_sql = "UPDATE cart SET Quantity = Quantity + 1 WHERE CustomerID = $customer_id AND ProductID = $product_id";
            $conn->query($update_sql);
            $_SESSION['success_message'] = "تم تحديث الكمية في السلة بنجاح!";
        } else {
            $insert_sql = "INSERT INTO cart (CustomerID, ProductID, ProductName, Price, Quantity, CraftsmanID) 
                           VALUES ($customer_id, $product_id, '$product_name', $product_price, $quantity, $craftsman_id)";
            $conn->query($insert_sql);
            $_SESSION['success_message'] = "تمت إضافة المنتج إلى السلة بنجاح!";
        }
    }
    header("Location: product.php"); 
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['favorite_product_id'])) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php"); 
        exit;
    }
    $favorite_product_id = $_POST['favorite_product_id'];

    $sql = "SELECT * FROM product WHERE ProductID = $favorite_product_id";
    $result55 = $mysqli->query($sql);

    if ($result55->num_rows > 0) {
        $product = $result55->fetch_assoc();

        $favorite_item = [
            'id' => $product['ProductID'],
            'name' => $product['ProductName'],
            'price' => $product['Price'],
            'image' => $product['ProductImage']
        ];

        if (!isset($_SESSION['favorites'])) {
            $_SESSION['favorites'] = [];
        }

        $is_already_favorite = false;
        foreach ($_SESSION['favorites'] as $item) {
            if ($item['id'] === $favorite_item['id']) {
                $is_already_favorite = true;
                break;
            }
        }

        if (!$is_already_favorite) {
            $_SESSION['favorites'][] = $favorite_item;
        }
        $customer_id = $_SESSION['user_id'];
        $sql_fav = "INSERT INTO favorites (CustomerID, ProductID) VALUES (?, ?)";
        $stmt = $mysqli->prepare($sql_fav);
        $stmt->bind_param("ii", $customer_id, $favorite_product_id);
        $stmt->execute();
        $_SESSION['success_message'] = "تمت إضافة المنتج إلى المفضلة بنجاح!";
    }
    header("Location: product.php");
    exit;
}
$category = isset($_GET['category']) ? $_GET['category'] : 'all';
$perPage = 8;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $perPage;
$sql = "SELECT * FROM product WHERE IsApproved = 1";
if ($category !== 'all') {
    $sql .= " AND Category = ?";
}
$sql .= " LIMIT $perPage OFFSET $offset";
$result = $conn->query($sql);
$product_count_sql = "SELECT COUNT(*) AS total FROM product";
if ($category !== 'all') {
    $product_count_sql .= " WHERE Category = '$category'";
}
$product_count = $conn->query($product_count_sql)->fetch_assoc()['total'];
$totalPages = ceil($product_count / $perPage);
if (isset($_SESSION['success_message'])) {
    echo "<div style='background-color: #725C3A; color: white; text-align: center; padding: 10px; font-size: 18px; position: fixed; top: 0px; left: 0; width: 100%; z-index: 1000;'>" . $_SESSION['success_message'] . "</div>";
    unset($_SESSION['success_message']);
}
?>
<?php
if (isset($_SESSION['success_message'])) {
    echo "<div style='background-color: #725C3A; color: white; text-align: center; padding: 10px; font-size: 18px; position: fixed; top: 0px; left: 0; width: 100%; z-index: 1000;'>" . $_SESSION['success_message'] . "</div>";
    unset($_SESSION['success_message']); 
}
?>
<!DOCTYPE html>
<html lang="ar">
<head>
<meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php if ($isLoggedIn): ?>
        <?php include("customer_header.php"); ?>
        <?php include("homepage-sidebar.php"); ?>
    <?php else: ?>
        <?php include("header.php"); ?>
    <?php endif; ?>
    <style> 
    .low-stock-alert {
        color:rgb(203, 111, 80);
    font-size: 8px;
    margin-top: 5px;
}
    .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #fdf9f0;
            padding: 10px 5%;
            margin-top: 30px; 
        }
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

    .search-box {
    display: none;
    position: absolute;
    bottom: 26px;
    right: 1144px;
    border: 0.5px solid rgb(146, 130, 106);
    background-color: #FEFCF9;
    padding: 2px 8px;
    border-radius: 25px;
    width: 190px;
    transition: all 0.3s ease;
    z-index: 1;
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
    body{
        direction: rtl; 
        background-color: #fdf9f0;
        padding-top: 110px;      
    }
#product-list {
    display: grid;
    grid-template-columns: repeat(4, 1fr); 
    gap: 20px; 
    margin: 40px auto; 
    padding: 10px;
    max-width: 1200px; 
}
.product {
    background-color: #fdfcfb; 
    border: 1px solid #e0e0e0; 
    border-radius: 5px; 
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); 
    overflow: hidden; 
    text-align: right;
    transition: transform 0.3s, box-shadow 0.3s;
}
.product:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
}
.product .product-image img {
    width: 100%; 
    height: 200px; 
    object-fit: cover;
}
.product .product-details {
    padding: 10px;
}
.product .product-details h3 {
    font-size: 1.9rem;
    color: #725C3A;
    margin: 10px 0;
}
.product .product-details .price {
    font-size: 2rem;
    color: #437457;
    margin-bottom:10px;
}
.product button {
    border: none;
    padding: 5px;
    border-radius: 3px;
    margin-right: 80%;
    margin-top: -35px;
    cursor: pointer;
    transition: background-color 0.3s;
}
.header2 {
    background-color: #f9f3e7;
    display: flex;
    flex-direction: row;
    align-items: center;
    position: fixed;
    top: 60px;
    width: 100%;
    z-index: 999;
    padding: 5px 0;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}
.navbar2 {
    display: flex;
    gap: 10px;
    justify-content: center;
    margin: 20px;
    margin-right:-47px;
}
.navbar2 button {
    background-color: #EEE9DF;
    color: white;
    border: none;
    border-radius: 50px;
    padding: 10px 20px;
    cursor: pointer;
    font-size: 16px;
    transition: all 0.3s ease;
    color: #8A8A8A;
}
.navbar2 button:hover {
    background-color: #725C3A;
}
.navbar2 button.active {
    background-color: #725C3A;
    color: #fff;
    font-weight: bold;
}
.container {
    padding: 20px;
}
.pagination {
    text-align: center;
    margin-top: 20px;
    position: fixed; /* لجعل التنقل ثابتًا */
    bottom: 0; /* تثبيت التنقل في أسفل الصفحة */
    left: 0; /* محاذاة التنقل لليسار */
    width: 100%; /* لجعل التنقل يغطي عرض الصفحة بالكامل */
    z-index: 1000; /* التأكد من أن التنقل يكون فوق أي عناصر أخرى */
}
.pagination a {
    text-decoration: none;
    color: #7b612b;
    margin: 0 5px;
    padding: 5px 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
    transition: background-color 0.3s;
}
.pagination a:hover {
    background-color: #d4c9b6;
}
.pagination a.active {
    background-color: #725C3A; 
    color: white; 
    font-weight: bold;
    border: none;
}
.add-to-cart-btn, .add-to-favorites-btn {
    background-color: #fdfcfb;
    border: none;
    padding: 5px;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s;
    display: inline-block;
    width: 50px; 
    height: 50px; 
    display: flex;
    justify-content: center;
    align-items: center;
}

.add-to-cart-btn img {
    width: 20px !important;
    height: 20px!important;
    margin-left:70px;
    margin-top: 29px;

}
.add-to-favorites-btn img {
    width: 28px!important;
    height: 28px!important;
}|

.product-actions {
    display: inline-flex;
    align-items: center;
    margin-top: 10px;
    
}
@media (max-width: 1024px) {
    html {
        font-size: 56.25%; 
    }
    #product-list {
        grid-template-columns: repeat(3, 1fr);
    }
    .product .product-details h3 {
        font-size: 1.4rem;
    }
    .product .product-details .price {
        font-size: 1.2rem;
    }
}
@media (max-width: 768px) {
    html {
        font-size: 50%;
    }
    #product-list {
        grid-template-columns: repeat(2, 1fr);
    }
    .product .product-details h3 {
        font-size: 1.2rem;
    }
    .product .product-details .price {
        font-size: 1rem;
    }
    .navbar2 {
        flex-direction: column;
    }
    .navbar2 button {
        width: 100%;
        margin: 5px 0;
    }
    .header2 {
        flex-direction: column;
        align-items: flex-start;
        padding: 10px;
    }
}

@media (max-width: 480px) {
    html {
        font-size: 45%; 
    }

    #product-list {
        grid-template-columns: 1fr; 
    }

    .product .product-details h3 {
        font-size: 1rem;
    }

    .product .product-details .price {
        font-size: 0.9rem;
    }

    .navbar2 button {
        font-size: 14px;
        padding: 8px 15px;
    }
}

 </style>
</head>
<body>
  
    <header2>
        <div class="navbar2">
            <button onclick="filterProducts('all')">الكل</button>
            <button onclick="filterProducts('cups')">أكواب</button>
            <button onclick="filterProducts('dolls')">الدمى</button>
            <button onclick="filterProducts('bags')">الحقائب</button>
            <button onclick="filterProducts('other')">أخرى</button>
        </div>
        <div id="message" style="color: green; text-align: center;">
            <?php 
                if (isset($_SESSION['message'])) {
                    echo $_SESSION['message'];
                    unset($_SESSION['message']);
                }
            ?>
        </div>
    </header2>

    <div id="product-list">
    <?php while($row = $result->fetch_assoc()): ?>
        <div class="product <?php echo $row['Category']; ?>">
        <a href="<?php echo isset($_SESSION['user_id']) ? 'product-details.php?id=' . $row['ProductID'] : 'login.php'; ?>">
                <?php if ($row['Stock'] <= 5 && $row['Stock'] != 0): ?>
                    <p class="low-stock-alert">⚠️ الكمية المتبقية قليلة (<?php echo $row['Stock']; ?> منتجات فقط)!</p>
            <?php endif; ?>
                <div class="product-image">
                    <img src="<?php echo $row['ProductImage']; ?>" alt="صورة المنتج">
                </div>
                <div class="product-details">
                    <h3><?php echo $row['ProductName']; ?></h3>
                    <p class="price"><?php echo $row['Price']; ?> ريال</p>
                    <?php if ($row['Stock'] == 0): ?>
                        <p style="color:rgb(203, 111, 80); font-weight: bold; font-size:13px;">نفذ من المخزون</p>
                    <?php endif; ?>
                </div>
            </a>
            <div class="product-actions">
                <?php if ($row['Stock'] > 0): ?>
                    
                    <form action="product.php" method="POST">
                        <input type="hidden" name="product_id" value="<?php echo $row['ProductID']; ?>">
                        <button type="submit" class="add-to-cart-btn">
                            <img src="images/addtocart.png" alt="أضف إلى السلة" style="width: 20px; height: 20px;">
                        </button>
                    </form>
                <?php else: ?>
                    <button class="add-to-cart-btn" disabled >
                    </button>
                <?php endif; ?>

                <form action="product.php" method="POST">
                    <input type="hidden" name="favorite_product_id" value="<?php echo $row['ProductID']; ?>">
                    <button type="submit" class="add-to-favorites-btn">
                        <img src="images/fav.png" alt="أضف إلى المفضلة" style="width: 20px; height: 20px;">
                    </button>
                </form>
            </div>
        </div>
    <?php endwhile; ?>
</div>



    <script>
        function filterProducts(category) {
            const buttons = document.querySelectorAll('.navbar2 button');
            buttons.forEach(btn => btn.classList.remove('active'));
            const activeButton = document.querySelector(`.navbar2 button[onclick="filterProducts('${category}')"]`);
            if (activeButton) activeButton.classList.add('active');

            const products = document.querySelectorAll('.product');
            products.forEach(product => {
                if (category === 'all' || product.classList.contains(category)) {
                    product.style.display = 'block';
                } else {
                    product.style.display = 'none';
                }
            });
        }

        document.addEventListener("DOMContentLoaded", () => {
            const category = '<?php echo $category; ?>';
            filterProducts(category);
        });
    </script>

    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?category=<?php echo $category; ?>&page=<?php echo $page - 1; ?>">السابقة</a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?category=<?php echo $category; ?>&page=<?php echo $i; ?>" class="<?php echo ($i == $page) ? 'active' : ''; ?>"><?php echo $i; ?></a>
        <?php endfor; ?>

        <?php if ($page < $totalPages): ?>
            <a href="?category=<?php echo $category; ?>&page=<?php echo $page + 1; ?>">التالي</a>
        <?php endif; ?>
    </div>

</body>
</html>