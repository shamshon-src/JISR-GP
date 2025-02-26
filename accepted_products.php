<?php
include("config.php");
session_start();
$first_name = $_SESSION['first_name']; 
$sql = "SELECT * FROM product WHERE isApproved = 1";  
$result = $mysqli->query($sql);
$acceptedProducts = mysqli_num_rows($result);
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update-btn'])) {
    $product_id    = $_POST['product_id'];
    $product_name  = $_POST['product_name'];
    $description   = $_POST['description'];
    $price         = $_POST['price'];
    $stock         = $_POST['stock'];
    $category      = $_POST['productCategory'];  

    $update_sql = "UPDATE product 
                   SET ProductName = '$product_name', 
                       Description = '$description', 
                       Price = '$price', 
                       Stock = '$stock',
                       Category = '$category'
                   WHERE ProductID = '$product_id'";

    if ($mysqli->query($update_sql) === TRUE) {
        echo "تم تحديث المنتج بنجاح";
    } else {
        echo "خطأ في التحديث: " . $mysqli->error;
    }
}

?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include("admin-header.php");?>
    <?php include("admin-sidebar.php");?>
    <title>المنتجات المقبولة</title>
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
            padding: 12px;
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
button {
  padding: 12.5px 30px;
  border: 0;
  border-radius: 100px;
  background-color: #2ba8fb;
  color: #ffffff;
  font-weight: bold;
  transition: all 0.5s;
  -webkit-transition: all 0.5s;
}

button:hover {
  background-color: #6fc5ff;
  box-shadow: 0 0 20px #6fc5ff50;
  transform: scale(1.1);
}
button:active {
  background-color: #3d94cf;
  transition: all 0.25s;
  -webkit-transition: all 0.25s;
  box-shadow: none;
  transform: scale(0.98);
}
.modal {
  display: none;
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

.modal-content {
  width: 440px; 
  height: 588px;     
  margin: 13px auto;
  background-color: #fbf9f5;
  padding: 20px;
  border-radius: 8px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  display: flex;
  flex-direction: column;
  align-items: center;
  direction: rtl;
  text-align: center;
  position: relative;
}

.modal-content .close-btn {
  position: absolute;
  top: 10px;    
  left: 14px;   
  width: 41px; 
  height: 41px;  
  cursor: pointer;
  transition: transform 0.3s ease;
}

.modal-content .close-btn:hover {
  transform: scale(1.1);
}

    .modal-content form {
      width: 100%;
    }
    .modal-content label {
      margin-top: 20px;
      text-align: right;
      margin-right: 35px;
      display: block;
      margin-bottom: 8px;
      font-weight: bold;
      color: #224F34;
      font-size: 18px;
      cursor: pointer;
    }
    .modal-content .input {
      max-width: 334px;   
      background-color: #F7F5F0;
      color:rgb(33, 28, 20);
      padding: 0.1rem 0.5rem; 
      min-height: 44px;   
      border-radius: 40px; 
      font-size: 14px;     
      border: none;
      line-height: 1.15;
      font-size: 16px;
      width: 100%;
      margin-bottom: -4px;
      padding-right: 18px;
      outline: 1px solid rgb(187, 185, 180);
    }
   

    .modal-content .input:hover {
      outline: 1px solid rgb(109, 103, 95);
    }

    .modal-content .update-btn {
      transition: all 0.3s ease-in-out;
      width: 150px;
      height: 49px;
      background-color: #725C3A;
      border-radius: 40px;
      box-shadow: 0 15px 25px -6px rgba(114, 92, 58, 0.5);
      outline: none;
      cursor: pointer;
      border: none;
      font-size: 24px;
      color: white;
      display: block;
      margin: 30px auto 0;
    }
    .modal-content .update-btn:hover {
      transform: translateY(3px);
      box-shadow: none;
      background-color: #5E4C2A;
    }
    .modal-content .update-btn:active {
      opacity: 0.5;
    }

        
        .product-actions {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 5px;
    background: none;
    border: none;
    padding: 5px;
}

.product-actions button {
    padding: 5px;
    cursor: pointer;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 5px;
    background: none;
    border: none;
}

.product-actions img {
    width: 36px; 
    height: 36px;
    object-fit: contain; 
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

        .category-options {
    display: flex;
    justify-content: flex-start;
    margin-top: 1px;
    margin-left: 30px;
    flex-direction: row-reverse;
}

.category-option {
    display: flex;
    align-items: left;
    cursor: pointer;
}

.category-input {
    display: none;
}

.category-label {
    display: inline-block;
    align-self: flex-start;
    align-items: center;
    background-color: #F7F5F0;
    padding: 9px 16px;
    border-radius: 20px;
    outline: 1px solid rgb(187, 185, 180);
    color: #4A4A4A;
    font-size: 18px;
    text-align: center;
    cursor: pointer;
    transition: background-color 0.3s ease, color 0.3s ease;
    margin: -10px;
    width: 75px;
}



.category-input:checked + .category-label {
    background-color: #725C3A;
    color: #fff;
}

.category-label:hover {
    outline: 1px solid rgb(109, 103, 95);
}



    </style>
</head>
<body>
    

    <div class="content">
<h1>المنتجات المقبولة</h1>
    <div class="navbar">
        <button onclick="window.location.href='admin_manage_products.php'" >طلبات جديدة</button>
        <button onclick="window.location.href='accepted_products.php'" class="active">طلبات مقبولة</button>
        <button onclick="window.location.href='rejected_products.php'" >طلبات مرفوضة</button>
    </div>
</div>
    <?php if ($acceptedProducts > 0): ?>
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
                    <th>تعديل</th>
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
</td>                        
                        <td><?= htmlspecialchars($row['Stock']); ?></td>
                        <td>
    <div class="product-actions">
        <button onclick="openModal(
            '<?php echo $row['ProductID']; ?>',
            '<?php echo htmlspecialchars($row['ProductName']); ?>',
            '<?php echo htmlspecialchars($row['Description']); ?>',
            '<?php echo $row['Price']; ?>',
            '<?php echo $row['Stock']; ?>'
        )">
            <img src="images/edit.png" alt="Edit" />
            <span>تعديل</span>
        </button>
    </div>
</td>


                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>لا توجد منتجات مقبولة حاليًا.</p>
    <?php endif; ?>

<div class="modal" id="editModal">
    <div class="modal-content">
    <img src="images/close.png" alt="إغلاق" class="close-btn" onclick="closeModal()">

    <form method="POST">
            <input type="hidden" name="product_id" id="product_id" class="input">
            <label>اسم المنتج :</label>
            <input type="text" name="product_name" id="product_name" class="input" required>
            <label>الوصف :</label>
            <input type="text" name="description" id="description" class="input" required>
            <label>السعر :</label>
            <input type="number" name="price" id="price" class="input" required min="0.5" step="0.01">
            <label>الكمية :</label>
            <input type="number" name="stock" id="stock" class="input" required min="0">
            <label>التصنيف :</label>
<div class="category-options">
    <label class="category-option">
        <input type="radio" name="productCategory" value="other" class="category-input">
        <span class="category-label">أخرى</span>
    </label>
    <label class="category-option">
        <input type="radio" name="productCategory" value="bags" class="category-input">
        <span class="category-label">حقائب</span>
    </label>
    <label class="category-option">
        <input type="radio" name="productCategory" value="dolls" class="category-input">
        <span class="category-label">دمى</span>
    </label>
    <label class="category-option">
        <input type="radio" name="productCategory" value="cups" class="category-input" required>
        <span class="category-label">أكواب</span>
    </label>
</div>

            <button type="submit" name="update-btn" class="update-btn">تحديث</button>
        </form>
    </div>
</div>

<script>
    function openModal(id, name, description, price, stock, category) {
        document.getElementById('product_id').value = id;
        document.getElementById('product_name').value = name;
        document.getElementById('description').value = description;
        document.getElementById('price').value = price;
        document.getElementById('stock').value = stock;
        document.getElementById('editModal').style.display = 'flex';
    }

    function closeModal() {
        document.getElementById('editModal').style.display = 'none';
    }


    document.addEventListener('DOMContentLoaded', function() {
        filterProducts('accepted_products.php');  
    });

    </script>
</body>
</html>
