<?php
session_start();

$user_id = $_SESSION['user_id'] ?? null;
$isLoggedIn = isset($user_id) && !empty($user_id);


$customer_id = $_SESSION['user_id'];
$first_name = isset($_SESSION['first_name']) ? $_SESSION['first_name'] : '';

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    echo "<p style='color:red;'>يرجى تسجيل الدخول للتقييم.</p>";
    exit;
}
$host = 'localhost';
$db = 'jisrgp';
$user = 'root';
$pass = '';
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $OrderID = $_GET['order_id'] ?? null;
    if (!$OrderID) {
        echo "<p style='color:red;'>رقم الطلب غير موجود.</p>";
        exit;
    }

    $stmt = $pdo->prepare("SELECT first_name FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        echo "<p style='color:red;'>المستخدم غير موجود.</p>";
        exit;
    }
    $username = $user['first_name']; 

    $stmt = $pdo->prepare("
        SELECT order_items.*, product.ProductName, product.ProductImage 
        FROM order_items 
        INNER JOIN product ON order_items.ProductID = product.ProductID 
        WHERE order_items.OrderID = ?"
    );
    $stmt->execute([$OrderID]);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "<p style='color:red;'>خطأ في الاتصال بقاعدة البيانات: " . $e->getMessage() . "</p>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ProductID = $_POST['product_id'];
    $rating = $_POST['rating'];
    $comment = $_POST['comment'];

    if ($rating > 0 && $comment != "") {
        $stmt = $pdo->prepare("SELECT * FROM comment WHERE ProductID = ? AND UserName = ?");
        $stmt->execute([$ProductID, $username]);
        $existingComment = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existingComment) {
            echo json_encode(['status' => 'error', 'message' => 'لقد قمت بإضافة تقييم لهذا المنتج من قبل.']);
        } else {
            $stmt = $pdo->prepare("INSERT INTO comment (ProductID, UserName, CommentText, Rating, CreatedAt) VALUES (?, ?, ?, ?, NOW())");
            $stmt->execute([$ProductID, $username, $comment, $rating]);
            echo json_encode(['status' => 'success', 'message' => 'تم إضافة تقييمك بنجاح!']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'يرجى إدخال تقييم وتعليق صالحين.']);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>صفحة تقييم منتجات</title>
    <?php if ($isLoggedIn): ?>
        <?php include("customer_header.php"); ?>

        <?php include("homepage-sidebar.php"); ?>
    <?php else: ?>
        <?php include("header.php"); ?>
    <?php endif; ?>

    <style>
        @font-face {
         font-family: 'TheYearOfTheCamel';
         src: url('fonts/TheYearofTheCamel-Light.otf') format('opentype');
         font-weight: normal;
         font-style: normal;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        .search-box {
    display: none;
    position: absolute;
    bottom: 40px;
    right: 1110px;
    border: 0.5px solid rgb(146, 130, 106);
    background-color: #FEFCF9;
    padding: 2px 8px;
    border-radius: 25px;
    width: 190px;
    transition: all 0.3s ease;
    z-index: 1;
}

.search-box input {
    padding: 8px 10px;
    width: 100%; 
    font-size: 0.8rem;
    border: none;
    border-radius: 20px;
    outline: none;
    background-color: #FEFCF9;
}

.search-box button {
    display: none; }

.acc h3 {
    font-size: 1.2rem; 
    color: #7b612b;
    margin-top: -10px;
    margin-bottom: 24px;
    margin-right:-10px;
    text-align: center;
}

        .icons {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        .icons img {
            cursor: pointer;
            transition: transform 0.3s ease;
            position: relative;
            z-index: 10;
            bottom:2px;

            
        }
        .icons img:hover {
            transform: scale(1.2);
        }

        .cart-icon {
    position: relative;
    top:2px;
}
.header .account-btn img {
    width: 1.9rem !important;
    height: 1.9rem !important;
}

.icon-home {
    margin-right: -10px;
    margin-left: 14px;
    width: 1.6rem;
    height: auto;
}
.icon-orders {
    margin-right: -7px;
    margin-left: 14px;
    width: 1.3rem;
    height: auto;
}
.sidebar a.logout-btn img {
            width: 2.8rem;
            height: auto;
            margin-right: 15px;
}
.header .logo img {
            height: 3rem;
            margin-top:10px;
            margin-right:-13px;

        }
.icon-fav {
    margin-right: -14px;
    margin-left: 14px;
    width: 2rem;
    height: auto;
}
.icon-cart {
    margin-right: -10px;
    margin-left: 14px;
    width: 1.6rem;
    height: auto;
}
.dropdown-menu {
    display: none;
    position: absolute;
    top: 1rem !important;
    left: 50%;
    transform: translateX(-50%);
    background-color: #fff;
    border: 1px solid #ccc;
    box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
    border-radius: 5px;
    z-index: 1000;
    width: 150px;
    min-height:70x;
    padding: 3px 0;
    direction: rtl;
    max-height: 89px;
    text-align: center;
}
.navbarheader {
            display: flex;
            gap: 3rem;
            margin-right:40px;
        }
        .navbarheader a {
            font-size: 1.2rem;
            color: #7b612b;
            font-weight: bold;
            text-decoration: none;
            transition: color 0.3s;
        }
        .navbarheader a:hover {
            color: #A4AC86;
        }
        body {
            color: #725C3A;
            direction: rtl;
            padding-top: 110px; 

        }
        .container {
            max-width: 800px;
            margin: 20px auto;
            background-color: #fff;
            border-radius: 25px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
        .notification {
            position: relative;
            background-color: #fff;
            padding: 10px 20px;
            border-radius: 5px;
            box-shadow: 0 10px 10px rgba(0, 0, 0, 0.2);
            font-size: 1rem;
            z-index: 1000;
            opacity: 0;
            transition: opacity 0.5s ease-in-out, transform 0.5s ease-in-out;
        }
        .notification.success {
            border-left: 5px solid #28a745;
            color: #155724;
        }
        .notification.error {
            border-left: 5px solid #dc3545;
            color: #721c24;
        }
        .notification.show {
            opacity: 1;
            transform: translateY(0);
        }
        .notification.hide {
            opacity: 0;
            transform: translateY(-20px);
        }
        .product {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        .product-container {
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .product img {
            width: 100px;
            height: 100px;
            border-radius: 15px;
            object-fit: cover;
            margin-left: 20px;
            border: 1px solid #725C3A;
        }
        .product h3 {
            font-size: 1.2rem;
            color: #725C3A;
        }
        .review-form label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #725C3A;
        }
        .review-form .stars {
            display: flex;
            gap: 5px;
            margin-bottom: 15px;
            align-items: center;
        }
        .review-form .star {
            font-size: 1.5rem;
            cursor: pointer;
            color: #ccc;
            transition: color 0.3s;
        }
        .review-form .star.selected {
            color: gold;
        }
        .review-form .rating-text {
            margin-right: 10px;
            font-size: 1rem;
            font-weight: bold;
            color: #224F34;
        }
        .review-form textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #725C3A;
            border-radius: 25px;
            resize: none;
            margin-bottom: 15px;
            color: #725C3A;
        }
        .review-form button {
            background-color: #725C3A;
            color: #fff;
            border: none;
            padding: 10px 30px;
            font-size: 1rem;
            cursor: pointer;
            border-radius: 50px;
                }
        .review-form button:hover {
            background-color: #5c4a2e;
        }
        .reviews {
            margin-top: 20px;
        }
        .review {
            border-bottom: 1px solid #725C3A;
            padding: 10px 0;
        }
        .review p {
            margin: 5px 0;
            color: #725C3A;
        }
        .review .rating {
            color: #725C3A;
            font-weight: bold;
        }
       
        .h2 {
            text-align: right;
            font-size: 33px;
            font-weight: bold;
            color: #224F34;
            margin-bottom: 29px;
            margin-top:0px;
        }

        p {
            text-align: center;
            font-size: 15px;
            color: #7f8c8d;
            margin-top: 20px;
        }
    </style>
</head>
<body>
<h2 style="text-align: right; margin-right:360px; font-size: 33px; font-weight: bold; color: #224F34; margin-bottom: 29px; margin-top: 18px;">
    تقييم المنتجات
</h2>
<div class="container">
    <?php foreach ($products as $product): ?>
    <div class="product-container">
        <div class="product">
            <img src="<?php echo htmlspecialchars($product['ProductImage']); ?>" alt="صورة المنتج">
            <h3><?php echo $product['ProductName']; ?></h3>
        </div>
        <div class="review-form" data-product="<?php echo $product['ProductID']; ?>">
            <form method="POST" action="">
                <label for="stars-<?php echo $product['ProductID']; ?>">تقييم المنتج:</label>
                <div class="stars" id="stars-<?php echo $product['ProductID']; ?>">
                    <span class="star" data-value="1">&#9733;</span>
                    <span class="star" data-value="2">&#9733;</span>
                    <span class="star" data-value="3">&#9733;</span>
                    <span class="star" data-value="4">&#9733;</span>
                    <span class="star" data-value="5">&#9733;</span>
                    <span class="rating-text" id="rating-text-<?php echo $product['ProductID']; ?>"></span>
                </div>
                <label for="comment-<?php echo $product['ProductID']; ?>">تعليقك:</label>
                <textarea name="comment" id="comment-<?php echo $product['ProductID']; ?>" rows="4" placeholder="اكتب رأيك هنا..."></textarea>
                <input type="hidden" name="product_id" value="<?php echo $product['ProductID']; ?>">
                <input type="hidden" name="rating" id="rating-<?php echo $product['ProductID']; ?>" value="0">
                <button type="submit">إرسال</button>
            </form>
            <div class="notification" id="notification-<?php echo $product['ProductID']; ?>"></div>
        </div>
        <div class="reviews" id="reviews-<?php echo $product['ProductID']; ?>">
            <h4>تقييمك لهذا المنتج: </h4>
            <?php
            $stmt = $pdo->prepare("SELECT * FROM comment WHERE ProductID = ? AND UserName = ?");
            $stmt->execute([$product['ProductID'], $username]);
            $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($comments):
                foreach ($comments as $comment): ?>
                    <div class="review">
                        <p class="rating">التقييم: <?php echo $comment['Rating']; ?> / 5</p>
                        <p>تعليق: <?php echo $comment['CommentText']; ?></p>
                    </div>
                <?php endforeach;
            else: ?>
                <p>لا توجد تقييمات لهذا المنتج حتى الآن.</p>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<script>
    const ratingTexts = [
        "مستاء جدًا",
        "غير راضٍ",
        "محايد",
        "راضٍ",
        "راضي جدًا"
    ];

    document.querySelectorAll('.star').forEach(star => {
        star.addEventListener('click', function() {
            const stars = this.parentElement.querySelectorAll('.star');
            const rating = this.dataset.value;
            const productID = this.closest('.review-form').dataset.product;
            
            document.getElementById(`rating-${productID}`).value = rating;
            
            stars.forEach(star => {
                if (parseInt(star.dataset.value) <= rating) {
                    star.classList.add('selected');
                } else {
                    star.classList.remove('selected');
                }
            });

            document.getElementById(`rating-text-${productID}`).textContent = ratingTexts[rating - 1];
        });
    });

    function showNotification(productID, message, type) {
        const notification = document.getElementById(`notification-${productID}`);
        notification.textContent = message;
        notification.className = `notification show ${type}`;
        setTimeout(() => {
            notification.classList.remove('show');
        }, 4000);
    }

    document.querySelectorAll('.review-form form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const productID = this.closest('.review-form').dataset.product;

            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    showNotification(productID, data.message, 'success');
                } else {
                    showNotification(productID, data.message, 'error');
                }
            })
            .catch(error => {
                showNotification(productID, 'حدث خطأ أثناء إرسال التقييم.', 'error');
            });
        });
    });
</script>
</body>
</html>
