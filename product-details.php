<?php
include("config.php");
session_start();
$isLoggedIn = isset($_SESSION['user_name']);
$userName = $isLoggedIn ? $_SESSION['user_name'] : '';
$userImage = $isLoggedIn ? $_SESSION['user_image'] : '';
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($product_id == 0) {
    echo "معرف المنتج غير صالح.";
    exit;
}
$sql = "SELECT * FROM product WHERE ProductID = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $product = $result->fetch_assoc();
} else {
    echo "المنتج غير موجود!";
    exit;
}
$sql_comments = "SELECT * FROM comment WHERE ProductID = ?";
$stmt_comments = $mysqli->prepare($sql_comments);
$stmt_comments->bind_param("i", $product_id);
$stmt_comments->execute();
$result_comments = $stmt_comments->get_result();
$sql_related = "SELECT * FROM product WHERE Category = ? AND ProductID != ? LIMIT 8";
$stmt_related = $mysqli->prepare($sql_related);
$stmt_related->bind_param("ii", $Category, $product_id);
$stmt_related->execute();
$result_related = $stmt_related->get_result();
if (isset($_POST['submit_comment'])) {
    $UserName = $_POST['firstName'];
    $CommentText = $_POST['comment'];
    $CreatedAt = date("Y-m-d H:i:s");

    if (!empty($UserName) && !empty($CommentText)) {
        $sql_insert_comment = "INSERT INTO comment (ProductID, UserName, CommentText, CreatedAt) VALUES (?, ?, ?, ?)";
        $stmt_insert = $mysqli->prepare($sql_insert_comment);
        if ($stmt_insert) {
            $stmt_insert->bind_param("isss", $product_id, $UserName, $CommentText, $CreatedAt);
            $stmt_insert->execute();
            header("Location: product_details.php?id=" . $product_id);
            exit;
        } else {
            die("فشل تحضير استعلام إضافة التعليق: " . $mysqli->error);
        }
    }
}
if (isset($_SESSION['success_message'])) {
    echo "<div style='background-color: #725C3A; color: white; text-align: center; padding: 10px; font-size: 18px; position: fixed; top: 0px; left: 0; width: 100%; z-index: 1000;'>" . $_SESSION['success_message'] . "</div>";
    unset($_SESSION['success_message']);
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $product_id = intval($_POST['product_id']);
    $customer_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;

    if ($customer_id > 0) {
        $check_cart_sql = "SELECT * FROM cart WHERE CustomerID = ? AND ProductID = ?";
        $stmt_check = $mysqli->prepare($check_cart_sql);
        $stmt_check->bind_param("ii", $customer_id, $product_id);
        $stmt_check->execute();
        $check_result = $stmt_check->get_result();
        if ($check_result->num_rows > 0) {
            $update_sql = "UPDATE cart SET Quantity = Quantity + 1 WHERE CustomerID = ? AND ProductID = ?";
            $stmt_update = $mysqli->prepare($update_sql);
            $stmt_update->bind_param("ii", $customer_id, $product_id);
            $stmt_update->execute();
        } else {
            $insert_sql = "INSERT INTO cart (CustomerID, ProductID, ProductName, Price, Quantity) VALUES (?, ?, ?, ?, ?)";
            $stmt_insert = $mysqli->prepare($insert_sql);
            $quantity = 1;
            $stmt_insert->bind_param("iisdi", $customer_id, $product_id, $product['ProductName'], $product['Price'], $quantity);
            $stmt_insert->execute();
        }
        $_SESSION['message'] = "تمت إضافة المنتج إلى السلة بنجاح!";
        header("Location: product-details.php?id=" . $product_id);
        exit;
    } else {
        echo "يجب عليك تسجيل الدخول لإضافة المنتج إلى السلة.";
    }
}
?>
<!DOCTYPE html>
<html lang="ar">
<head>
<meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تفاصيل المنتج</title>
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
    font-family: 'Quicksand', sans-serif;
    direction: rtl;
    background-color: #faf7f4;
    margin: -90px auto 0 auto;
    padding: 0;
}
.container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .product-info-wrapper {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            gap: 30px;
            flex-wrap: wrap;
            margin-top: 90px;

        }
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
            cursor: default;
            margin-top: 20px;
            overflow: hidden;

        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        .product-card img {
            width: 66%;
            height: 188px;
            object-fit: cover;
            display: block;
            margin: 0 auto;
            object-position: top;
            margin-top:-15px;
        }
        .product-details {
            padding: 15px;
        }
        .product-name {
            font-size: 25px;
            color: rgb(69, 55, 35);
            font-weight: bold;
            margin-right:44px;
        }
        .product-price {
            font-size: 20px;
            color: #224F34;
            font-weight: bold;
            margin-bottom: 19px;
        }
        .product-stock {
            font-size: 16px;
            color: white;
            background-color: #8C7B65;
            padding: 5px 20px;
            border-radius: 50px;
            display: inline-block;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .details-box {
            background-color: #FEFCF9;
            border: 1px solid #ddd;
            border-radius: 30px;
            padding: 20px;
            width: 520px;
            min-height: 360px;
            box-shadow: 0 4px 9px rgba(0, 0, 0, 0.1);
        }
        .details-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        .details-box h1 {
            font-size: 28px;
            font-weight: bold;
            color: #725C3A;
            text-align: center;
            padding: 10px 0;
            margin-bottom: 10px;
        }
        .details-box p {
            font-size: 20px;
            margin: 10px 0;
            color: #555;
            line-height: 1.2;
        }
        .details-box p strong {
            color: #224F34;
        }
        .btn {
            transition: all 0.3s ease-in-out;
            width: 158px;
            height: 43px;
            background-color: #725C3A;
            border-radius: 50px;
            outline: none;
            cursor: pointer;
            border: none;
            font-size: 20px;
            color: white;
            text-align: center;
            line-height: 40px;
            box-shadow: 0 5px 5px -6px rgba(114, 92, 58, 0.5);
            text-decoration: none;
            display: inline-block;
            margin: 5px;
        }
        .btn:hover {
            transform: translateY(3px);
            box-shadow: none;
            background-color: #5E4C2A;
        }
        .btn:active {
            opacity: 0.5;
        }
        .product-reviews {
            margin-top: 40px;
        }
        .review-card {
            border: 1px solid #ddd;
            border-radius: 40px;
            background-color: #FEFCF9;
            box-shadow: 0 10px 10px rgba(0, 0, 0, 0.1);
            padding: 10px;
            margin-bottom: 15px;
            direction: rtl;
            text-align: right;
            max-width: 790px;
            margin-right: auto;
            margin-left: auto;
            display: flex;
            flex-direction: column;
            height: 140px;
        }
        .review-date {
            font-size: 15px;
            color: #999;
            text-align: left;
            margin-bottom: 5px;
            margin-left: 28px;
            position: relative;
            top: 20px;
        }
        .review-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }
        .review-header img {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 10px;
        }
        .review-name {
            font-size: 24px;
            font-weight: bold;
            color: #224F34;
            margin-right: 14px;
            position: relative;
            top: -6px;
        }
        .review-rating {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-right: 130px;
            position: relative;
            top: -54px;
        }
        .rating-stars {
            color: rgb(227, 204, 73);
            font-size: 18px;
        }
        .rating-number {
            font-size: 16px;
            font-weight: bold;
            color: rgb(98, 101, 103);
        }
        .review-text {
            font-size: 20px;
            color: #555;
            position: relative;
            top: -35px;
            margin-right: 77px;
        }
        .review-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        .related-products {
            margin-top: 50px;
            text-align: center;
        }
        .related-products h3 {
            font-size: 2.7rem;
            color: #224F34;
            font-weight: 700;
            margin-bottom: 20px;
            margin-right:-38px;
        }
        .products-grid {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
        }
        .products-grid .product-card {
            width: 220px;
            height: auto;
            padding: 10px;
        }
        .products-grid .product-card h4 {
            font-size: 1.8rem;
            margin: 10px 0;
            color: #224F34;
        }
        .products-grid .product-card p.price {
            font-size: 1.6rem;
            color: #224F34;
            margin-bottom: 10px;
        }
        .products-grid .product-card a {
            font-size: 1.6rem;
            color: #725C3A;
            text-decoration: none;
            font-weight: bold;
        }
        @media screen and (max-width: 768px) {
            .product-info-wrapper {
                flex-direction: column;
                align-items: center;
            }
            .details-box {
                width: 100%;
            }
        }
        .search-box {
    display: none;
    position: absolute;
    bottom: 30px;
    right: 1090px !important;
    border: 0.5px solid rgb(146, 130, 106);
    background-color: #FEFCF9;
    padding: 2px 8px;
    border-radius: 25px;
    width: 200px;
    transition: all 0.3s ease;
    z-index: 1;
}

.search-box input {
    padding: 8px 10px;
    width: 100%; 
    font-size: 1.1rem;
    border: none;
    border-radius: 20px;
    outline: none;
    background-color: #FEFCF9;
}

.search-box button {
    display: none; }

    </style>
</head>

<body>
    <header class="header">
        <?php include('customer_header.php'); ?>
        <?php include('homepage-sidebar.php'); ?>

    </header>
    <div class="container">
    <div class="product-info-wrapper">
    <div class="product-card">
    <img src="<?php echo htmlspecialchars($product['ProductImage']); ?>" alt="صورة المنتج">
      <div class="product-details">
        <h2 class="product-name"><?php echo htmlspecialchars($product['ProductName']); ?></h2>
       
        <p class="product-price"><?php echo htmlspecialchars($product['Price']); ?> ريال</p>
        <p class="product-stock">
          <?php echo $product['Stock'] > 0 ? 'متوفر' : 'غير متوفر'; ?>
        </p>
      </div>
    </div>
    <div class="details-box">
    <h1>تفاصيل المنتج</h1>
    <p><strong>اسم المنتج :</strong> <?php echo htmlspecialchars($product['ProductName']); ?></p>
            <p><strong>التصنيف :</strong>
                <?php 
                    $categories = [
                        'cups'   => 'أكواب',
                        'dolls'  => 'دمى',
                        'bags'   => 'حقائب',
                        'other'  => 'أخرى'
                    ];
                    $category = htmlspecialchars($product['Category']);
                    echo isset($categories[$category]) ? $categories[$category] : $category;
                ?>
            </p>
            <p><strong>الوصف :</strong> <?php echo nl2br(htmlspecialchars($product['Description'])); ?></p>
            <p><strong>السعر :</strong> <?php echo htmlspecialchars($product['Price']) . " ريال"; ?></p>
            <p><strong>الكمية :</strong> <?php echo htmlspecialchars($product['Stock']); ?></p>
            <div class="btn-container" style="display: flex; justify-content: center; gap: 0px; margin-top: 40px;">
            <a href="product.php" class="btn">العودة إلى المنتجات</a>

  <form method="POST" action="product-details.php?id=<?php echo $product_id; ?>" style="margin: 0;">
    <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
    <input type="hidden" name="custom_text" id="custom-text-input"> 
    <button type="submit" class="btn">أضف إلى السلة</button>
  </form>
</div>

    </div>
    </div>
    <section>
        <h2 style="text-align: center; margin-top: 56px; color: #224F34; margin-left:99px; margin-bottom:-20px;">مراجعات العملاء</h2>
    </section>

    <div class="product-reviews" style="direction: rtl;">
        <?php
        include("config.php");

        // دالة حساب الوقت المنقضي منذ إنشاء التعليق
        function time_elapsed_string($datetime, $full = false) {
            $now = new DateTime;
            $ago = new DateTime($datetime);
            $diff = $now->diff($ago);
            $diff->w = floor($diff->d / 7);
            $diff->d -= $diff->w * 7;
            $string = array(
                'y' => 'سنة',
                'm' => 'شهر',
                'w' => 'أسبوع',
                'd' => 'يوم',
                'h' => 'ساعة',
                'i' => 'دقيقة',
                's' => 'ثانية',
            );
            foreach ($string as $k => &$v) {
                if ($diff->$k) {
                    $v = $diff->$k . ' ' . $v;
                } else {
                    unset($string[$k]);
                }
            }
            if (!$full) $string = array_slice($string, 0, 1);
            return $string ? 'قبل ' . implode(', ', $string) : 'الآن';
        }

        function getUserProfilePicture($userId, $mysqli) {
            $query = "SELECT profile_picture FROM users WHERE id = ?";
            $stmt_user = $mysqli->prepare($query);
            $stmt_user->bind_param("i", $userId);
            $stmt_user->execute();
            $stmt_user->bind_result($profile_picture);
            $stmt_user->fetch();
            $stmt_user->close();
            if (!empty($profile_picture)) {
                return 'uploads/' . htmlspecialchars($profile_picture);
            }
            return 'uploads/default-profile.png';
        }

        $sql_comments = "SELECT * FROM comment WHERE ProductID = ?";
        $stmt_comments = $mysqli->prepare($sql_comments);
        $stmt_comments->bind_param("i", $product_id);
        $stmt_comments->execute();
        $result_comments = $stmt_comments->get_result();

        if ($result_comments->num_rows > 0) {
            while ($comment = $result_comments->fetch_assoc()) {
                if (isset($comment['UserID']) && !empty($comment['UserID'])) {
                    $userImage = getUserProfilePicture($comment['UserID'], $mysqli);
                } else {
                    $userImage = 'uploads/default-profile.png';
                }
                $rating = floatval($comment['Rating']);
                $fullStars = floor($rating);
                $halfStar = ($rating - $fullStars) >= 0.5 ? 1 : 0;
                $emptyStars = 5 - ($fullStars + $halfStar);
                ?>
                <div class="review-card">
                    <div class="review-date"><?php echo time_elapsed_string($comment['CreatedAt']); ?></div>
                    <div class="review-header">
                        <img src="<?php echo $userImage; ?>" alt="صورة العميل">
                        <span class="review-name"><?php echo htmlspecialchars($comment['UserName']); ?></span>
                    </div>
                    <div class="review-rating">
                        <span class="rating-stars">
                            <?php
                            for ($i = 0; $i < $fullStars; $i++) {
                                echo "★";
                            }
                            if ($halfStar) {
                                echo "½";
                            }
                            for ($i = 0; $i < $emptyStars; $i++) {
                                echo "☆";
                            }
                            ?>
                        </span>
                        <span class="rating-number"><?php echo number_format($rating, 1); ?></span>
                    </div>
                    <div class="review-text"><?php echo htmlspecialchars($comment['CommentText']); ?></div>
                </div>
                <?php
            }
        } else {
            echo "<p style='text-align:center; font-size:1.8rem; color: #7f8c8d;  margin-right:-30px;'>لا توجد تقييمات لهذا المنتج حتى الآن.</p>";
        }
        
        $stmt_comments->close();
        $mysqli->close();
        ?>
    </div>
    <div class="related-products">
        <h3>قد يعجبك أيضًا</h3>
        <div class="products-grid">
            <?php
            if ($result_related->num_rows > 0) {
                while ($related_product = $result_related->fetch_assoc()) {
                    echo '<div class="product-card">';
                    echo '<img src="' . $related_product['ProductImage'] . '" alt="منتج متعلق">';
                    echo '<h4>' . $related_product['ProductName'] . '</h4>';
                    echo '<p class="price">' . $related_product['Price'] . ' ريال</p>';
                    echo '<a href="product-details.php?id=' . $related_product['ProductID'] . '">عرض التفاصيل</a>';
                    echo '</div>';
                }
            } else {
                echo "<p style='text-align:center; font-size:1.8rem; color: #7f8c8d; margin-right:-30px;'>لا يوجد منتجات  متعلقة.</p>";
            }
            ?>
        </div>
    </div>
    <script>
    document.querySelector("form").addEventListener("submit", function(event) {
        var customText = document.getElementById("custom-text").value;
        if (customText !== "") {
            document.getElementById("custom-text-input").value = customText; 
        }
    });
</script>
<script>
    function toggleCustomization() {
        const content = document.getElementById("customization-content");
        const icon = document.getElementById("toggle-icon");
        if (content.style.display === "none" || content.style.display === "") {
            content.style.display = "block";
            icon.innerHTML = "&#x25B2;"; 
        } else {
            content.style.display = "none";
            icon.innerHTML = "&#x25BC;"; 
        }
    }
</script>

</body>
</html>
