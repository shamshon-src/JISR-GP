<?php
include("config.php");
session_start();
$first_name = $_SESSION['first_name']; 

$sql = "SELECT * FROM product WHERE isApproved = 0";  
$result = $mysqli->query($sql);
$rejectedProducts = mysqli_num_rows($result);


?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>المنتجات المرفوضة</title>
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
     
        .content {
            width: 80%;
            padding: 20px;
            margin: auto;
        }

        .content h1 {
            color: #466952;
            padding: 12px;
            font-size: 26px;
            margin-bottom: 15px;
            text-align: right;
        }
        .tabs {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 20px;
        }

        .tabs button {
            background-color: #f3f0e9;
            border: none;
            padding: 10px 20px;
            margin-left: 10px;
            border-radius: 20px;
            cursor: pointer;
            color: #6D5633;
            font-weight: bold;
        }

        .tabs button.active {
            background-color: #6D5633;
            color: white;
        }
        .table {
            text-align: center;
    width: 80%;
    border-collapse: collapse;
    direction:rtl;
    margin-top:20px;
    margin-left:145px;
    font-size:19px;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1); 
        }

        .table th, .table td {
            padding: 17px;
    border: 1px solid rgb(238, 235, 235);;
    background-color: rgb(255, 255, 255);
        }

        .table th {
            color: #725C3A;
    background-color: rgb(255, 255, 255);
    align-items: center;
            
        }

        table td img {
            width: 80px;
            height: 80px;
            object-fit: cover;
        }

     
.navbar {
    flex-direction: row-reverse;
    display: flex;
    gap: 16px;
    justify-content: center;
}

.navbar button {
    background-color: #EEE9DF;
    color: white;
    border: none;
    border-radius: 55px;
    padding: 10px 70px;
    cursor: pointer;
    font-size: 20px;
    transition: all 0.3s ease;
    color:rgb(31, 26, 24);
}

.navbar button:hover {
    background-color: #725C3A;
}

.navbar button.active {
    background-color: #725C3A;
    color: #fff;
    font-weight: bold;
}


.product-actions {
    background: none;
    border: none;
    padding: 2px;
    cursor: pointer;
    display: inline-block;
}

.product-actions img {
    width: 30px;
    height: 30px;
    transition: transform 0.2s;
}

.product-actions img:hover {
    transform: scale(1.2);
}

p {
            text-align: center;
            font-size: 18px;
            color: #7f8c8d;
            margin-top: 20px;
        }     
    </style>
</head>
<body>

    <div class="content">
    <h1>المنتجات المرفوضة</h1>
    <div class="navbar">
        <button onclick="window.location.href='admin_manage_products.php'" >طلبات جديدة</button>
        <button onclick="window.location.href='accepted_products.php'">طلبات مقبولة</button>
        <button onclick="window.location.href='rejected_products.php'" class="active">طلبات مرفوضة</button>
    </div>
</div>
    <?php if ($rejectedProducts > 0): ?>
        <table class="table">
            <thead>
                <tr>
                    <th>الصورة</th>
                    <th>اسم المنتج</th>
                    <th>الوصف</th>
                    <th>اسم الحرفي</th>
                    <th>السعر</th>
                    <th>التصنيف</th>
                    <th>الكمية</th>
                    <th>سبب الرفض</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><img src="<?= htmlspecialchars($row['ProductImage']); ?>" alt="صورة المنتج" width="80" height="80"></td>
                        <td><?= htmlspecialchars($row['ProductName']); ?></td>
                        <td><?= htmlspecialchars($row['Description']); ?></td>
                        <td><?= htmlspecialchars($row['CraftsmanFullName']); ?></td>
                        <td><?= htmlspecialchars($row['Price']); ?> ريال</td>
                        <td>
    <?php 
    $category_ar = [
        'cups'  => 'أكواب',
        'bags'  => 'حقائب',
        'dolls' => 'دمى',
        'other' => 'أخرى'
    ];
    echo htmlspecialchars($category_ar[$row['Category']] ?? $row['Category']);
    ?>
</td>                        <td><?= htmlspecialchars($row['Stock']); ?></td>
                        <td><?= htmlspecialchars($row['RejectionReason']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>.لا توجد منتجات مرفوضة حاليًا</p>
    <?php endif; ?>
</div>
  
  <script>

        
    document.addEventListener('DOMContentLoaded', function() {
        filterProducts('rejected_products.php'); 
    });
    </script>
</body>
</html>
