<?php
include("config.php");
session_start();
$first_name = $_SESSION['first_name']; 
if(isset($_GET['approve_id'])) {
    $ProductID = $_GET['approve_id'];  
    $sql = "UPDATE product SET isApproved = 1 WHERE ProductID = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $ProductID);  
    $stmt->execute();
    header("Location: admin_manage_products.php"); 
    exit;
}
if(isset($_POST['reject_id'])) {
    $ProductID = $_POST['reject_id'];
    $RejectionReason = $_POST['RejectionReason'];  
    $sql = "UPDATE product SET isApproved = 0, RejectionReason = ? WHERE ProductID = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("si", $RejectionReason, $ProductID);
    $stmt->execute();
    header("Location: admin_manage_products.php"); 
    exit;
}
$sql = "SELECT * FROM product WHERE isApproved IS NULL";  
$result = $mysqli->query($sql);
$requestsPending = mysqli_num_rows($result);
?>
<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة المنتجات</title>
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

        p {
            text-align: center;
            font-size: 18px;
            color: #7f8c8d;
            margin-top: 20px;
        }


        .product-actions {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
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
    width: 35px; 
    height: 35px;
    object-fit: contain; 
    transition: transform 0.2s; 
}

.product-actions img:hover {
    transform: scale(1.2); 
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
.confirm-modal {
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
    font-size: 24px;
    margin-bottom: 20px;
    color: #224F34;
    margin-left: 49px;
    white-space: nowrap;
}
.btn-container {
    display: flex;
    justify-content: center;
    width: 100%;
    margin-top: 10px;
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
.btn-yes:hover {
    transform: scale(1.1);
}
.btn-no {
    background-color: #ddd;
    color: #224F34;
}
.btn-no:hover {
    transform: scale(1.1);
}
.input {
    max-width: 308px;
    background-color: #F7F5F0;
    color: #242424;
    padding: .15rem .5rem;
    min-height: 40px;
    border-radius: 49px;
    font-size: 15px;
    border: none;
    line-height: 1.15;
    width: 100%;
    margin-bottom: -4px;
    padding-right: 18px; 
    outline: 1px solid rgb(187, 185, 180);
}


</style>
<body>
    <div class="content">
    <h1>طلبات المنتجات الجديدة</h1>
    <div class="navbar">
        <button onclick="window.location.href='admin_manage_products.php'" class="active">طلبات جديدة</button>
        <button onclick="window.location.href='accepted_products.php'">طلبات مقبولة</button>
        <button onclick="window.location.href='rejected_products.php'">طلبات مرفوضة</button>
    </div>
</div>
       <?php if ($requestsPending > 0): ?>
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
        <th>الإجراءات</th>
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

            <td class="product-actions">
    <a href="?approve_id=<?= $row['ProductID']; ?>" class="approve">
        <img src="./images/check.png" alt="موافقة">
    </a>
    <button type="button" class="reject" onclick="openRejectModal(<?= $row['ProductID']; ?>)">
        <img src="./images/reject.png" alt="رفض">
    </button>
    <div id="reject-modal-<?= $row['ProductID']; ?>" class="confirm-modal">
  <div class="confirm-modal-content">
    <h2>سبب رفض المنتج :</h2>
    <form action="" method="post">
      <input type="hidden" name="reject_id" value="<?= $row['ProductID']; ?>">
      <input type="text" name="RejectionReason" placeholder="اكتب سبب الرفض هنا..." class="input" required>
      <div class="btn-container">
        <button type="submit" class="btn-yes">
          <img src="./images/check.png" alt="موافقة">
        </button>
        <button type="button" class="btn-no" onclick="closeRejectModal(<?= $row['ProductID']; ?>)">
          <img src="./images/reject.png" alt="رفض">
        </button>
      </div>
    </form>
  </div>
</div>


</td>
<script>
   function openRejectModal(productId) {
    document.getElementById(`reject-modal-${productId}`).style.display = 'flex';
}
function closeRejectModal(productId) {
    document.getElementById(`reject-modal-${productId}`).style.display = 'none';
}

</script>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>.لا توجد منتجات جديدة حاليًا</p>
    <?php endif; ?>
</div>
</body>
</html>
