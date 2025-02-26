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
        $success_message = "تم تحديث بيانات الملف الشخصي بنجاح!";
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
    <?php include("admin-header.php");?>
    <?php include("admin-sidebar.php");?>
    <style>
@font-face {
         font-family: 'TheYearOfTheCamel';
         src: url('fonts/TheYearofTheCamel-Light.otf') format('opentype');
         font-weight: normal;
         font-style: normal;
        }
        body {
            font-family: 'TheYearOfTheCamel'; 
            background-color: #fdf9f0;
        }
        .header {
            margin-top: 30px; 
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
