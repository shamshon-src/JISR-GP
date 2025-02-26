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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
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
        
        $check_cart_sql = "SELECT * FROM cart WHERE CustomerID = $customer_id AND ProductID = $product_id";
        $check_result = $conn->query($check_cart_sql);

        if ($check_result->num_rows > 0) {
            $update_sql = "UPDATE cart SET Quantity = Quantity + 1 WHERE CustomerID = $customer_id AND ProductID = $product_id";
            $conn->query($update_sql);
        } else {
            $insert_sql = "INSERT INTO cart (CustomerID, ProductID, ProductName, Price, Quantity, CraftsmanID) 
                           VALUES ($customer_id, $product_id, '$product_name', $product_price, $quantity, $craftsman_id)";
            $conn->query($insert_sql);
        }

       $_SESSION['message'] = "تمت إضافة المنتج إلى السلة بنجاح!";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['favorite_product_id'])) {
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

        $_SESSION['success_message '] = "تمت إضافة المنتج إلى المفضلة بنجاح!";
    }
}
$searchTerm = '';
$result4 = null;

$perPage = 8;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $perPage;

if (isset($_POST['search'])) {
    $searchTerm = $_POST['search_term'];
    $searchTerm = "%$searchTerm%";

    $countSql = "SELECT COUNT(*) AS total FROM product WHERE ProductName LIKE ? OR Description LIKE ?";
    $countStmt = $conn->prepare($countSql);
    $countStmt->bind_param("ss", $searchTerm, $searchTerm);
    $countStmt->execute();
    $product_count = $countStmt->get_result()->fetch_assoc()['total'];
    $totalPages = ceil($product_count / $perPage);

    $sql = "SELECT * FROM product WHERE ProductName LIKE ? OR Description LIKE ? LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssii", $searchTerm, $searchTerm, $perPage, $offset);
    $stmt->execute();
    $result4 = $stmt->get_result();

    $numResults = $result4->num_rows;
} else {
    $product_count = 0;
    $totalPages = 0;
    $numResults = 0;
}

?>

<!DOCTYPE html>
<html lang="ar">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css.css">
    <title>نتائج البحث</title>
    <?php include("header.php"); ?>
    <?php include("customer-sidebar.php"); ?>
    <style>
@font-face {
    font-family: 'TheYearOfTheCamel';
    src: url('fonts/TheYearofTheCamel-Light.otf') format('opentype');
    font-weight: normal;
    font-style: normal;
}

* {
    font-family: 'TheYearOfTheCamel';
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    outline: none;
    border: none;
    text-decoration: none;
    text-transform: capitalize;
    transition: .2s linear;
    background-color: #faf7f4;
}

html {
    font-size: 62.5%;
    overflow-x: hidden;
    scroll-padding-top: 7rem;
    scroll-behavior: smooth;
}

html::-webkit-scrollbar {
    width: 0.3rem;
}

html::-webkit-scrollbar-track {
    background: transparent;
}

html::-webkit-scrollbar-thumb {
    background-color: var(--white);
    border-radius: 5rem;
}

body {
    direction: rtl;}

#product-list {
    display: grid;
    grid-template-columns: repeat(4, 1fr); 
    gap: 20px; 
    margin: 40px auto; 
    padding: 10px;
    max-width: 1200px; 
}
.product {
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
    font-size: 1.6rem;
    color: #725C3A;
    margin: 10px 0;
}

.product .product-details .price {
    font-size: 1.4rem;
    color: #A4AC86;
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
    margin: 20px 0;
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
    border: none;
    padding: 5px;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s;
    display: inline-block;
    width: 40px; 
    height: 40px;
    display: flex;
    justify-content: center;
    align-items: center;
}

.add-to-cart-btn:hover, .add-to-favorites-btn:hover {
    background-color: #f0e2b6;
}

.add-to-cart-btn img, .add-to-favorites-btn img {
    width: 20px;
    height: 20px;
}

.product-actions {
    display: flex;
    gap: 10px;
    justify-content: center; 
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

<div class="search-results">
    <h2 style="color: #725C3A; font-size: 2.5rem; margin-bottom: 10px;">نتائج البحث</h2>
    <?php if (isset($result4) && $result4->num_rows > 0): ?>
        <h3>عدد النتائج: <?php echo htmlspecialchars($product_count); ?></h3>
        <div id="product-list">
            <?php while ($row = $result4->fetch_assoc()): ?>
                <div class="product">
                    <div class="product-image">
                    <img src="<?php echo $row['ProductImage']; ?>" alt="صورة المنتج">
                    </div>
                    <div class="product-details">
                        <h3><?php echo htmlspecialchars($row['ProductName']); ?></h3>
                        <p class="price">السعر: <?php echo htmlspecialchars($row['Price']); ?> ريال</p>
                    </div>
                    <div class="product-actions">
                        <form action="search_results.php" method="POST">
    <input type="hidden" name="product_id" value="<?php echo $row['ProductID']; ?>">
    <input type="hidden" name="search_term" value="<?php echo htmlspecialchars($_POST['search_term'] ?? ''); ?>">
    <input type="hidden" name="page" value="<?php echo htmlspecialchars($_GET['page'] ?? 1); ?>">
    <button type="submit" class="add-to-cart-btn">
        <img src="images/addtocart.png" alt="أضف إلى السلة" style="width: 20px; height: 20px;">
    </button>
</form>

<form action="search_results.php" method="POST">
    <input type="hidden" name="favorite_product_id" value="<?php echo $row['ProductID']; ?>">
    <input type="hidden" name="search_term" value="<?php echo htmlspecialchars($_POST['search_term'] ?? ''); ?>">
    <input type="hidden" name="page" value="<?php echo htmlspecialchars($_GET['page'] ?? 1); ?>">
    <button type="submit" class="add-to-favorites-btn">
        <img src="images/fav.png" alt="أضف إلى المفضلة" style="width: 20px; height: 20px;">
    </button>
</form>

                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p>لا توجد نتائج مطابقة.</p>
    <?php endif; ?>
</div>





<div class="pagination">
    <?php
    if ($page > 1): ?>
        <a href="?page=<?php echo $page - 1; ?>"><</a>
    <?php endif; ?>

    <?php
    for ($i = 1; $i <= min(3, $totalPages); $i++): ?>
        <a href="?page=<?php echo $i; ?>" <?php if ($i == $page) echo 'style="font-weight: bold; color: #725C3A;"'; ?>>
            <?php echo $i; ?>
        </a>
    <?php endfor; ?>

    <?php
    if ($totalPages > 3): ?>
        <?php if ($page < $totalPages): ?>
            <a href="?page=<?php echo $page + 1; ?>">></a>
        <?php endif; ?>
    <?php endif; ?>
        </div>
</body>
</html>
