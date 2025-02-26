<?php
include("config.php");
session_start();
$first_name = $_SESSION['first_name']; 

$craftsman_id = $_GET['id'];
$sql = "SELECT * FROM users WHERE id = '$craftsman_id' AND role = 'craftsman'";
$result = $mysqli->query($sql);
$row = $result->fetch_assoc();


?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تفاصيل الحرفي</title>
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
    box-sizing: border-box; 
    
        }
      
        body {
            font-family: 'TheYearOfTheCamel'; 
            background-color: #fdf9f0;
        }
main {
    flex: 1;
    padding: 20px;
}

main header {
    text-align: center;
    margin-bottom: 20px;
}

main header h1 {
    font-size: 1.5rem;
}

main header h1 span {
    color: #224F34;
    font-size: 1.2rem;
}

.details-container {
    display: flex;
    gap: 20px;
    padding: 20px;
    justify-content: center;
    flex-direction: row-reverse; 
}

.craftsman-card {
    flex: none;
    width: 250px;
    height: 300px; 
    background: linear-gradient(to bottom, #F7F3EB 42%, #FEFCF9 40%);
    border: 1px solid #ddd;
    border-radius: 30px;
    padding: 20px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    text-align: center;
    direction:rtl;

}

.craftsman-card img {
    width: 80px;
    height: 80px;
    border-radius: 50%; 
    margin-bottom: 10px;
}


.craftsman-card p {
    font-size: 0.9rem;
    margin-bottom: 5px;
    color: #555;
}

.details-box {
    flex: none; 
    width: 400px; 
    height: 300px; 
    background-color: #FEFCF9;
    border: 1px solid #ddd;
    border-radius: 30px;
    padding: 20px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    direction:rtl;
}


.details-box h2 {
    margin-bottom: 33px;
    font-size: 1.5rem;
    color: #7b612b;
    position: relative;
    right: 50px; 
    top:14px;
}


.details-box p {
    font-size: 1.2rem;
    margin-bottom: 22px;
    margin-right: 5px;
    color: #555;
}

.details-box p strong {
    color: #224F34;
}
.product-container {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    justify-content: center;
    padding: 20px;
}

.product-card {
    background-color: #fff;
    border: 1px solid #ddd;
    border-radius: 30px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    width: 300px;
    overflow: hidden;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.product-image {
    width: 100%;
    height: 200px;
    object-fit: cover;
    border-bottom: 1px solid #ddd;
}

.product-details {
    padding: 15px;
    text-align: center;
}

.product-name {
    font-size: 27px;
    color:rgb(69, 55, 35);
    margin: 4px 0;
}

.product-price {
    font-size: 21px;
    color:#224F34;
    font-weight: bold;
    direction: rtl;
    margin-top: 13px;

}

.product-description {
    font-size: 18px;
    color: #666;
    margin-top: 10px;
}

h1 {
    font-size: 28px !important;
    font-weight: bold !important;
    color: #224F34 !important;
    text-align: center !important;
    padding: 10px 0 !important;
    margin-left: 30px !important;
    margin-top: -20px !important;
}


.info {
    display: flex;
    align-items: center;
    justify-content: center;
    direction: rtl;
    width: 88%;
}

.info p {
    margin: 10px 0 7px;
    flex-grow: 0;
    text-align: center;
}

.info .icon {
    width: 20px;
    height: 20px;
    margin-right: 15px;
    position: relative;
    bottom: -5px;
    right:-8px;
}

h3 {
    font-size: 22px;
    color: #725C3A;
    text-align: center;
    margin-left: -2px;
    margin-bottom: 18px;
    margin-top: 27px;


}


    </style>
</head>
<body>
<main>
        <header>
        <h1> معلومات الـحرفـي</h1>
        </header>
        
        <div class="details-container">
    <div class="craftsman-card">
        <?php if (!empty($row['profile_picture'])): ?>
            <img src="<?php echo htmlspecialchars($row['profile_picture']); ?>" alt="أيقونة المستخدم" width="80" height="80">
        <?php else: ?>
            <img src="./images/user.png" alt="أيقونة المستخدم" width="80" height="80">
        <?php endif; ?>

        <h3><?php echo htmlspecialchars($row['first_name']) . ' ' . htmlspecialchars($row['last_name']); ?></h3>
        <p style="font-size: 18px;"><?php echo htmlspecialchars($row['craft_description']); ?></p>

        
        <div class="info">
            <img src="./images/num.png" alt="رقم الجوال" class="icon">
            <p><?php echo htmlspecialchars($row['phone_number']); ?></p>
        </div>

        <div class="info">
            <img src="./images/gmail.png" alt="البريد الإلكتروني" class="icon">
            <p><?php echo htmlspecialchars($row['email']); ?></p>
        </div>
    </div>

    <div class="details-box">
        <h2>البيانات الأساسية</h2>
        <p><strong>الاسم : </strong><?php echo htmlspecialchars($row['first_name']) . ' ' . htmlspecialchars($row['last_name']); ?></p>
        <p><strong>العنوان : </strong><?php echo htmlspecialchars($row['address']); ?></p>
        <p><strong>الوصف : </strong><?php echo htmlspecialchars($row['craft_description']); ?></p>
        <p><strong> رقم الجوال : </strong><?php echo htmlspecialchars($row['phone_number']); ?></p>
        <p><strong>البريد الإلكتروني : </strong><?php echo htmlspecialchars($row['email']); ?></p>
    </div>
</div>

    <section>
        <h2 style="text-align: center; margin-top: 16px; margin-left: 95px;   color: #224F34; ">المنتجات</h2>
        <div class="products-container">
        <?php
$craftsman_id = $_GET['id'];
$product_sql = "SELECT * FROM product WHERE CraftsmanID = '$craftsman_id' ";
$product_result = $mysqli->query($product_sql);
?>

<div class="product-container">
    <?php
    while ($product = $product_result->fetch_assoc()) {
        echo '<div class="product-card">';
        echo '<img src="' . htmlspecialchars($product['ProductImage']) . '" alt="صورة المنتج" class="product-image">';
        echo '<div class="product-details">';
        echo '<h3 class="product-name">' . htmlspecialchars($product['ProductName']) . '</h3>';
        echo '<p class="product-description">' . htmlspecialchars($product['Description']) . '</p>';
        echo '<p class="product-price"><span class="price-value">' . htmlspecialchars($product['Price']) . '</span> ريال</p>';
        echo '</div>';
        echo '</div>';
    }
    ?>
</div>

        </div>
    </section>
    
    </main>
</body>
</html>
