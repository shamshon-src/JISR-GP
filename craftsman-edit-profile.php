<?php
include("config.php");
session_start();
$user_id = $_SESSION['user_id'];
$first_name = $_SESSION['first_name']; 
$query = "SELECT profile_picture, last_name, phone_number, address FROM users WHERE id = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($profile_picture, $last_name, $phone_number, $address);
$stmt->fetch();
$stmt->close();

if (empty($profile_picture)) {
    $profile_picture = 'uploads/default-profile.png'; 
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_profile'])) {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $phone_number = $_POST['phone_number'];
    $address = $_POST['address'];

    if (!empty($_FILES['profile_picture']['name'])) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["profile_picture"]["name"]);
        if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
            $profile_picture = $target_file;
        } else {
            echo "<script>alert('حدث خطأ أثناء رفع الصورة الشخصية.');</script>";
        }
    }

    $sql = "UPDATE users 
            SET first_name = ?, 
                last_name = ?, 
                phone_number = ?, 
                address = ?, 
                profile_picture = ? 
            WHERE id = ?";
    
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("sssssi", $first_name, $last_name, $phone_number, $address, $profile_picture, $user_id);

    if ($stmt->execute()) {
        $success_message = "!تم تحديث بيانات الملف الشخصي بنجاح";
        $_SESSION['first_name'] = $first_name;
        $_SESSION['last_name'] = $last_name;
    } else {
        echo "<script>alert('حدث خطأ أثناء التحديث: " . $stmt->error . "');</script>";
    }
    $stmt->close();
}

?>



<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تعديل بيانات الحساب</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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
       .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #fdf9f0;
            padding: 10px 5%;
            margin-top: 30px; 
        }

        .header .icons {
            display: flex;
            gap: 1.5rem;
            align-items: center;
        }

        .header .icons img {
            width: 1.5rem;  
            opacity: 0.8;
            cursor: pointer;
           
        }

        .header .icons img:hover {
            transform: scale(1.2);
            opacity: 1;
        }
        .header .navbar {
            display: flex;
            gap: 2rem;
        }

        .header .navbar a {
            font-size: 1.5rem;
            color: #7b612b;
            font-weight: bold;
            text-decoration: none;
            transition: color 0.3s;
        }

        .header .navbar a:hover {
            color: #A4AC86;
        }

         .dropdown {
    position: relative;
    display: inline-block;
}

.dropdown-menu {
    display: none;
    position: absolute;
    top: 3.9rem;
    left: 50%;
    transform: translateX(-50%);
    background-color: #fff;
    border: 1px solid #ccc;
    box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
    border-radius: 5px;
    z-index: 1000;
    width: 152px;
    padding: 5px 0;
    direction: rtl;
    text-align: center;
}

.dropdown.open .dropdown-menu {
    display: block;
}

.dropdown-menu a {
    display: flex;
    align-items: center;
    padding: 10px 15px;
    text-decoration: none;
    color: #224F34;
    font-size: 16px;
    justify-content: flex-start;
    transition: background-color 0.2s;
}

.dropdown-menu a:hover {
    background-color: #f5f5f5;
}

.dropdown-icon {
    width: 25px;
    height: 25px;
    margin-left: 10.5px;
    filter: brightness(0) saturate(100%)
          invert(33%) sepia(54%) saturate(209%) hue-rotate(2deg) brightness(93%) contrast(88%); 
}

.sidebar {
            width: 250px;
            height: 100%;
            background-color: #EEE9DF;
            box-shadow: -2px 0 5px rgba(0, 0, 0, 0.1);
            position: fixed;
            top: 0;
            right: -250px;
            transition: right 0.3s ease;
            padding-top: 20px;
            display: flex;
            flex-direction: column;
            justify-content: space-between; 
            border-radius: 15px;
            z-index: 10;
        }
        .sidebar .account-icon {
    display: flex;
    align-items: center;
    padding: 15px;
    text-decoration: none;
    color: #7b612b;
    margin-bottom: 15px;
    margin-left: auto; 
    margin-right: 43px;
}


        .sidebar a.logout-btn {
           margin: auto 30px 80px auto;
           text-align: center;
        }

        .sidebar a {
            text-align: center;
            font-size: 20px;
            display: flex;
            align-items: center;
            padding: 10px 15px;
            text-decoration: none;
            color: #7b612b;
            margin: 5px 30px 8px;
            flex-direction: row-reverse;
        }

        .sidebar a img {
            width: 1.2rem; 
            margin-left: 16px; 
            margin-top:-2px;       
        }
        .sidebar a img:not([style*="width: 8rem"]) {
  filter: brightness(0) saturate(100%)
          invert(33%) sepia(54%) saturate(209%) hue-rotate(2deg) brightness(93%) contrast(88%);
}

.sidebar a:hover img:not([style*="width: 8rem"]) {
    filter: brightness(0) saturate(100%) invert(14%) sepia(59%) saturate(468%) hue-rotate(99deg) brightness(93%) contrast(93%);
    transform: scale(1.2);
    transition: all 0.3s ease-in-out;
}


        .sidebar a:hover {
         color: #224F34;
         background-color: #ddd; 
         transition: all 0.3s ease-in-out; 
        }   


        .sidebar a:hover {
            background-color: #ddd;
        }

        .sidebar a.active {
         color: #224F34;
        }
        
        .sidebar .acc h3 {
        font-size:2.5erm;
         text-align: center; 
          margin-right: 0px;
          margin-top: -9.5px;   
        }


       .sidebar a.active img {
         filter: brightness(0) saturate(100%) invert(14%) sepia(59%) saturate(468%) hue-rotate(99deg) brightness(93%) contrast(93%); /* تغيير لون الأيقونة */
        }
  
        .account-btn img:hover {
            transform: scale(1.2);
    opacity: 0.9;
        }

        h2 {
            font-size: 30px;
            color: #725C3A;
            margin-bottom: 20px;
            text-align: right;
            margin-right: 50px;
            margin-top: -13.5px;
        }
        .acc {
            color: #725C3A;
            text-align: center; 
            margin-left:12px;
        }
        .acc :hover{
            color: #224F34;
        }
        

        nav ul {
            list-style: none;
            padding: 0;
        }

        nav ul li {
            margin: 15px 0;
            cursor: pointer;
        }

        nav ul li:hover {
            text-decoration: underline;
        }

.content {
    text-align: center;
    flex: left;
    padding: 20px;
}
        h1 {
        text-align: center;
        margin-bottom: 20px;
        margin-top: 0px;
        color: #224F34;
    }

    .profile-container {
        text-align: center;
        margin-top: 20px;
    }

    .profile-picture {
        width: 146px;
        height: 146px;
        border-radius: 50%;
        object-fit: cover;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        border: 1px solid rgb(142, 123, 95);
    }

    form {
        max-width: 500px;
        height: 775px;
    margin: 10px auto;
    background-color:rgb(251, 249, 245);
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    display: flex;
    flex-direction: column;
    align-items: center;
    direction:rtl;
    }

    label {
        margin-top: 28px;
        margin-left: 235px;
        display: block;
        margin-bottom: 8px;
        font-weight: bold;
        color: #224F34;
        font-size:17px;
        cursor: pointer;
    }

    .input {
        max-width: 308px;
        background-color: #F7F5F0;
        color: #242424;
        padding: .15rem .5rem;
        min-height: 50px;
        border-radius: 49px;
        font-size:15px;
        border: none;
        line-height: 1.15;
        width: 100%;
        margin-bottom: -4px;
        padding-right: 18px; 
        outline: 1px solid rgb(187, 185, 180);
        

    }

   
    .input:hover {
        outline: 1px solid rgb(130, 128, 125);
    }

    .btn {
        transition: all 0.3s ease-in-out;
        width: 128px;
        height: 50px;
        background-color: #725C3A;
        border-radius: 50px;
        box-shadow: 0 20px 30px -6px rgba(114, 92, 58, 0.5);
        outline: none;
        cursor: pointer;
        border: none;
        font-size: 26px;
        color: white;
        display: block;
        margin: 40px auto 0;
    }

    .btn:hover {
        transform: translateY(3px);
        box-shadow: none;
        background-color: #5E4C2A;

    }

    .btn:active {
        opacity: 0.5;
    }

    .message {
        display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: flex-start;
  flex-grow: 1;;
    }

    .message.success {
        color: #28a745;
    }

    .message.error {
        color: #dc3545;
    }

    .labelFile {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    width: 200px;
    height: 100px;
    border: 2px dashed #ccc;
    text-align: center;
    padding: 5px;
    color: #404040;
    cursor: pointer;
}

    /* اخفاء input file */
    #profile_picture {
        display: none;
    }

    .profile-wrapper {
    position: relative;
    display: inline-block;
}

.edit-button {
    position: absolute;
    bottom: -8px;
    right: 1px;
    background: none;
    border: none;
    border-radius: 50%;
    padding: 5px;
    cursor: pointer;
    transition: transform 0.3s ease;
}

.edit-button img {
    width: 32px; 
    height: 32px;
}

.edit-button:hover {
    transform: scale(1.1);
}

</style>
</head>

 <header class="header">
    <div class="icons">
        <a class="menu-btn" onclick="toggleSidebar()">
            <img src="./images/line.png" alt="شريط">
        </a>
        <div class="dropdown">
            <a class="account-btn" onclick="toggleDropdown()">
            <img src="<?php echo $profile_picture; ?>" alt="الحساب" style="border-radius: 50%; width: 4rem; height: 4rem; object-fit: cover;">
            </a>
            <div id="dropdownMenu" class="dropdown-menu">
                <a href="craftsman-edit-profile.php">
                    <img src="./images/usercust.png" alt="Profile" class="dropdown-icon">
                    الملف الشخصي
                </a>
                <a href="logout.php">
                    <img src="./images/logout.png" alt="Logout" class="dropdown-icon">
                    تسجيل الخروج
                </a>
            </div>
        </div>
    </div>

    <nav class="navbar">
        <a href="craftsman_dashboard.php">الرئيسية</a>
    </nav>

    <div class="logoContent">
        <a href="#"><img src="./images/logo.png" alt="Logo" style="height: 4rem;"></a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</header>

<div class="sidebar" id="sidebar">
    <a href="#" class="account-icon" onclick="showContent('account')">
       <img src="<?php echo $profile_picture; ?>" alt="الحساب"style="border-radius: 50%; width: 8rem; height:8rem; object-fit: cover; ">
    </a>
 <div class="acc">
    <h2>أهلًا بك، <?php echo htmlspecialchars($first_name); ?><br></h2>
    <h3>حرفـيّ</h3>
    </div>

    <a href="addproduct.php">
    <img src="./images/add-product.png" alt="إضافة منتج"> إضافة منتج
    </a>

    <a href="craftman_manage_products.php">
        <img src="./images/pro_man.png" alt="إدارة المنتجات"> إدارة المنتجات
    </a>
    <a href="craftman-orders-manegment.php">
        <img src="./images/manage_orders.png" alt="ادارة الطلبات"> إدارة الطلبات
    </a>


    <a href="logout.php" class="logout-btn">
    <img src="./images/logout.png" alt="تسجيل الخروج"> <span>تسجيل الخروج</span>
</a>

</div>

 
<body>

<?php if (!empty($success_message)) : ?>
        <div id="success-message" style="background-color: #725C3A; color: white; text-align: center; padding: 10px; font-size: 18px; position: fixed; top: 0; left: 0; width: 100%; z-index: 1000;">
            <?php echo $success_message; ?>
        </div>
    <?php endif; ?>

<main class="content">

<form method="POST" enctype="multipart/form-data">
    <div class="profile-container">
        <h1>تعديل الملف الشخصي</h1>
        <div class="profile-wrapper">

            <img src="<?php echo htmlspecialchars($profile_picture); ?>" alt="Profile Picture" class="profile-picture">

            <button type="button" class="edit-button" onclick="document.getElementById('profile_picture').click();">
                <img src="images/edit_profile.png" alt="تعديل الصورة">
            </button>
        </div>
    </div>

    <input type="file" name="profile_picture" id="profile_picture" style="display: none;">

    <label for="first_name">الاسم الأول :</label>
    <input type="text" name="first_name" id="first_name" class="input" value="<?php echo htmlspecialchars($first_name); ?>" placeholder="الاسم الأول" required>
    
    <label for="last_name">الاسم الأخير :</label>
    <input type="text" name="last_name" id="last_name" class="input" value="<?php echo htmlspecialchars($last_name); ?>" placeholder="الاسم الأخير" required>
    
    <label for="phone_number">رقم الهاتف :</label>
    <input type="text" name="phone_number" id="phone_number" class="input" value="<?php echo htmlspecialchars($phone_number); ?>" placeholder="رقم الهاتف" required>
    
    <label for="address">العنوان  :</label>
    <input type="text" name="address" id="address" class="input" value="<?php echo htmlspecialchars($address); ?>" placeholder="العنوان" required>
    
    <button type="submit" name="edit_profile" class="btn">تـحديث</button>
</form>

<script>
    document.getElementById('profile_picture').addEventListener('change', function() {
        if (this.files && this.files[0]) {
            var imgURL = URL.createObjectURL(this.files[0]);
            document.querySelector('.profile-picture').src = imgURL;
        }
    });
</script>


</main>

    <script>
                    
function toggleDropdown() {
    const dropdownMenu = document.getElementById("dropdownMenu");
    dropdownMenu.style.display = dropdownMenu.style.display === "block" ? "none" : "block";
}


       function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            if (sidebar.style.right === '0px') {
                sidebar.style.right = '-250px';
            } else {
                sidebar.style.right = '0px';
            }
        }

        function showContent(page) {
            const content = document.getElementById('content');
            

            switch (page) {

                case 'addproduct':
                    window.location.href = 'addproduct.php'; 
                    break;
                    case '"craftman_manage_products.php">':
                    window.location.href = "craftsman_manage_products.php"; 
                    break;
                    case 'craftman-orders-manegment.php':
                    window.location.href = 'craftman-orders-manegment.php';
                    break;
                default:
                    window.location.href = 'logout.php'; 
            }
        }
        
              
 document.getElementById('profile_picture').addEventListener('change', function() {
        var fileInput = document.getElementById('profile_picture');
        if (fileInput.files && fileInput.files[0]) {
            var fileName = fileInput.files[0].name;
            document.getElementById('file-name').textContent = fileName;

            var imgPreview = document.querySelector('.profile-picture');
            imgPreview.src = URL.createObjectURL(fileInput.files[0]);
        }
    });

        if (isValid) {
            message.textContent = "تم حفظ التغييرات بنجاح!";
            message.classList.add("success");
            message.classList.remove("error");
        } else {
            message.textContent = "تأكد من إدخال جميع الحقول!";
            message.classList.add("error");
            message.classList.remove("success");
        }

        if (!form.contains(message)) {
            form.appendChild(message);
        }
        
  document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                var successMessage = document.getElementById('success-message');
                if (successMessage) {
                    successMessage.style.display = 'none';
                }
            }, 5000); 
        });  
</script>
  
</body>
</html>
