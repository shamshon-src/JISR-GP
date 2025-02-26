<?php
include("config.php");
session_start();

$errorMessage = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = trim($_POST['first_name']);
    $last_name  = trim($_POST['last_name']);
    $email      = trim($_POST['email']);
    $phone_number = trim($_POST['phone_number']);
    $password_plain = $_POST['password'];
    $role       = $_POST['role'];
    $security_question = $_POST['security_question'];
    $security_answer = $_POST['security_answer'];
    
    if (!preg_match('/^05\d{8}$/', $phone_number)) {
      echo "رقم الهاتف غير صحيح. يجب أن يبدأ بـ 05 ويتكون من 10 أرقام فقط.";
      exit();
    }
    if (empty($errorMessage)) {
        $password = password_hash($password_plain, PASSWORD_DEFAULT);
        $security_answer = password_hash($security_answer, PASSWORD_DEFAULT);
    
        $checkAdminQuery = "SELECT * FROM users";
        $result = mysqli_query($mysqli, $checkAdminQuery);
        if (mysqli_num_rows($result) == 0) {
            $role = 'admin';
        }
    
        $craft_description = null;
        if ($role === "craftsman" && isset($_POST['craft_description'])) {
            $craft_description = trim($_POST['craft_description']);
        }
    
        $stmt = $mysqli->prepare("INSERT INTO users (first_name, last_name, email, password, role, craft_description, phone_number, security_question, security_answer) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            $errorMessage = "خطأ في تحضير الاستعلام: " . $mysqli->error;
        } else {
            $stmt->bind_param("sssssssss", $first_name, $last_name, $email, $password, $role, $craft_description, $phone_number, $security_question, $security_answer);
    
            if ($stmt->execute()) {
                $user_id = $mysqli->insert_id;
    
                $_SESSION['user_id']    = $user_id;
                $_SESSION['role']       = $role;
                $_SESSION['first_name'] = $first_name;
    
                if ($role === "admin") {
                    header("Location: admin_dashboard.php");
                } elseif ($role === "customer") {
                    header("Location: homepage.php");
                } elseif ($role === "craftsman") {
                    header("Location: craftsman_dashboard.php");
                }
                exit();
            } else {
                $errorMessage = "البريد الإلكتروني مستخدم أو حدث خطأ أثناء التسجيل.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>صفحة التسجيل</title>
  <style>
    @font-face {
      font-family: 'TheYearOfTheCamel';
      src: url('fonts/TheYearofTheCamel-Light.otf') format('opentype');
      font-weight: normal;
      font-style: normal;
    }
    * {
      font-family: 'TheYearOfTheCamel', sans-serif; 
    }
    body {
      background-color: #f9f3e7;
      background-size: cover;
      margin: 0;
      padding: 0;
      color: #5C4727;
      font-size: 14px;
      display: flex;
      justify-content: center;
      height: 100vh;
      direction: rtl;
    }
    .logoContent {
      text-align: right;
      margin-top: 1rem;
    }
    .logoContent img {
      height: 4rem;
    }
    .container {
      display: flex;
      justify-content: space-between;
      align-items: center;
      width: 90%;
      max-width: 1200px;
      gap: 20px;
    }
    .form-section {
  border-radius: 22px;
  padding: 20px;
  width: 40%;       
  max-width: 500px; 
  margin-right:87px;
  height: 660px;   
  margin-top:10px;
  background: white;
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
  text-align: center;
}


    .form-section h2 {
      margin-bottom: 10px;
      margin-top:5px;
      color: #5C4727;
      font-size: 28px;
    }
    .form-section p {
      margin-bottom: 10px;
      font-size:20px;
      margin-top:0px;
     
      
    }
    form {
      text-align: right;
      max-width: 450px;
      margin: 0 auto;
    }
    input, textarea, select {
      width: 100%;
      padding: 10px;
      border: 1px solid #ddd;
      background-color: #fdfbfbcf;
      border-radius: 15px;
      font-size: 13px;
      box-sizing: border-box;
      margin-bottom: 20px;
      max-height:30px;
    }
    select {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    background-color: #fdfbfbcf;
    border-radius: 15px;
    font-size: 13px;
    box-sizing: border-box;
    max-height: 37px;;
    margin-top:1px;
}
    .reg-input {
      background-color: #F7F5F0;
      color: #242424;
      border: none;
      outline: 1px solid rgb(187, 185, 180);
      border-radius: 49px;
      font-size: 15px;
      padding: 10px;
      margin-bottom: 20px;
    }
    form button {
      background-color: #8B6F47;
      color: #FFF;
      padding: 8px 12px;
      border: none;
      border-radius: 6px;
      font-size: 14px;
      cursor: pointer;
      transition: all 0.3s ease;
      display: block;
      width: 100%;
      margin-top: 10px;
    }
    form button:hover {
      background-color: #5C4727;
    }
    .role-options {
      margin-bottom: 10px;
      text-align: right;
      display: flex;
      flex-wrap: wrap;
      justify-content: flex-start;
      gap: 120px;
      color: #63483b;
      align-items: center;
      direction: rtl;
    }
    .role-options label {
      margin-right: 15px;
      font-size: 1vw;
      margin-bottom: 10px;
      flex: 1;
      min-width: 100px;
    }
    .account {
      font-size: 14px;
      text-align: center;
      margin-top: 12px;
    }
    .account a {
      color: #0a880ce1;
      text-decoration: none;
    }
    .account a:hover {
      text-decoration: underline;
    }
    .image-section {
      position: relative;
      width: 45%;
      height: auto;
      min-width: 600px;
      top: -280px; 
      right: 120px;
    }
    .image-section img {
      position: absolute;
      height: auto;
    }
    .image-section img:first-child {
      z-index: 1;
      width: 80%;
      top: 8%;
      left: 20%;
    }
    .image-section img:nth-child(2) {
      z-index: 2;
      width: 70%;
      top: 8%;
      left: 60%;
      transform: translateX(-50%);
    }
    @media (max-width: 768px) {
      .container {
        flex-direction: column;
      }
      .image-section {
        display: none;
      }
      .form-section {
        width: 100%;
        max-width: 100%;
        padding: 15px;
      }
      .role-options {
        justify-content: center;
        gap: 30px;
      }
      .form-section h2 {
        font-size: 26px;
      }
      input, textarea, select {
        font-size: 16px;
        padding: 10px;
        margin: 8px 0;
      }
      form button {
        padding: 12px 18px;
        font-size: 16px;
      }
    }

    .role-options {
  display: flex;
  justify-content: center;
  gap: 20px;
  margin-bottom: 20px;
}

.role-options input {
  display: none; 
}

.role-options label {
  cursor: pointer;
  padding: 10px 20px;
  border: 2px solid #8B6F47;
  border-radius: 25px;
  font-size: 16px;
  background-color: white;
  color: #8B6F47;
  transition: all 0.3s ease;
  text-align: center;
}

.role-options input:checked + label {
  background-color: #8B6F47;
  color: white;
}

.btn {
      transition: all 0.3s ease-in-out;
      width: 190px;
      height: 50px;
      background-color: #725C3A;
      border-radius: 50px;
      box-shadow: 0 20px 30px -6px rgba(188, 183, 176, 0.5);
      outline: none;
      cursor: pointer;
      border: none;
      font-size: 20px;
      color: white;
      margin: 12px auto ;
    }
    .btn:hover {
      transform: translateY(3px);
      box-shadow: none;
      background-color: #5E4C2A;
    }
    .btn:active {
      opacity: 0.5;
    }
    .security-section {
  margin-bottom: 20px;
  text-align: right;
  
}

.security-section .reg-input {
  margin-bottom: 20px;
}

  </style>
</head>
<body>
  <div class="logoContent">
    <a href="#"><img src="./images/logo.png" alt="Logo"></a>
  </div>
  <div class="container">
    <div class="form-section">
      <h2>التسجيل</h2>
      <p>مرحبًا بك</p>
      <?php if (!empty($errorMessage)) : ?>
        <div class="error-message" style="color:red; margin-bottom:10px;">
          <?php echo $errorMessage; ?>
        </div>
      <?php endif; ?>
      <form action="register.php" method="POST" enctype="multipart/form-data" onsubmit="return validatePasswordStrength()">
      <div class="role-options">
  <input type="radio" id="customer" name="role" value="customer" onclick="toggleCraftDescription()" required
    <?php if(isset($_POST['role']) && $_POST['role'] == 'customer') echo 'checked'; ?>>
  <label for="customer">أرغب بالتسجيل كزبون</label>
  
  <input type="radio" id="craftsman" name="role" value="craftsman" onclick="toggleCraftDescription()" required
    <?php if(isset($_POST['role']) && $_POST['role'] == 'craftsman') echo 'checked'; ?>>
  <label for="craftsman">أرغب بالتسجيل كحرفي</label>
</div>


        
        <input type="text" name="first_name" placeholder="الاسم الأول" required class="reg-input"
          value="<?php echo isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : ''; ?>">
        <input type="text" name="last_name" placeholder="الاسم الأخير" required class="reg-input"
          value="<?php echo isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : ''; ?>">
        <input type="email" name="email" placeholder="البريد الإلكتروني" required class="reg-input"
          value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
        <input type="text" id="phone_number" name="phone_number" placeholder="رقم الهاتف" required oninput="validatePhoneNumber()" class="reg-input"
          value="<?php echo isset($_POST['phone_number']) ? htmlspecialchars($_POST['phone_number']) : ''; ?>">
        <div id="phone-error" style="color: red; font-size: 12px;"></div>
        
        
        <input type="text" id="craft_description" name="craft_description" placeholder="صف نوع الحرفة الخاصة بك" style="display: none;" class="reg-input"
          value="<?php echo isset($_POST['craft_description']) ? htmlspecialchars($_POST['craft_description']) : ''; ?>">
        
        <div class="password-container" style="position: relative;">
          <input type="password" id="password" name="password" placeholder="كلمة المرور" oninput="checkPasswordStrength()" required class="reg-input">
          <span id="togglePassword" onclick="togglePasswordVisibility()" style="position: absolute; left: 17px; top: 30%; transform: translateY(-50%); background: none; border: none; font-size: 14px; cursor: pointer;">🙈</span>
        </div>
        <div id="password-strength" class="password-strength" style="font-size: 15px; margin-right:10px; margin-top:-15px; margin-bottom:4px;"></div>
        <div id="password-error" class="password-error" style="font-size: 12px;"></div>

        <div class="security-section">
  <select name="security_question" id="security_question" required class="reg-input">
    <option value="" disabled <?php if(!isset($_POST['security_question']) || $_POST['security_question'] == '') echo "selected"; ?>>اختر سؤالًا أمنيًا</option>
    <option value="اسم أول مدرسة التحقت بها؟" <?php if(isset($_POST['security_question']) && $_POST['security_question'] == "اسم أول مدرسة التحقت بها؟") echo "selected"; ?>>اسم أول مدرسة التحقت بها؟</option>
    <option value="اسم حيوانك الأليف الأول؟" <?php if(isset($_POST['security_question']) && $_POST['security_question'] == "اسم حيوانك الأليف الأول؟") echo "selected"; ?>>اسم حيوانك الأليف الأول؟</option>
    <option value="ما هو اسم مدينتك المفضلة؟" <?php if(isset($_POST['security_question']) && $_POST['security_question'] == "ما هو اسم مدينتك المفضلة؟") echo "selected"; ?>>ما هو اسم مدينتك المفضلة؟</option>
  </select>
  
  <input type="text" name="security_answer" id="security_answer" placeholder="الإجابة" required class="reg-input"
    value="<?php echo isset($_POST['security_answer']) ? htmlspecialchars($_POST['security_answer']) : ''; ?>">

    <p class="important-note" style="text-align: center; font-size: 14px; color: #7f8c8d; margin-top: -10px; margin-left:140px;">
  تنبيه: هذه الإجابة مهمة جداً، تأكد من تذكرها عند إنشاء حسابك.
</p>
</div>



       
<button type="submit" class="btn">تسجيل</button>
        
        <div class="account">
          لديك حساب بالفعل؟ <a href="login.php">تسجيل الدخول</a>
        </div>
      </form>
    </div>
    <div class="image-section">
      <img src="images/back.png" alt="صورة التسجيل">
      <img src="images/logg.png" alt="صورة التسجيل">
    </div>
  </div>
  
  <script>
    function validatePhoneNumber() {
      const phoneInput = document.getElementById('phone_number');
      const phoneError = document.getElementById('phone-error');
      const phonePattern = /^05\d{8}$/; 
      if (!phonePattern.test(phoneInput.value)) {
        phoneError.textContent = "رقم الهاتف يجب أن يبدأ بـ 05 ويتكون من 10 خانات فقط.";
      } else {
        phoneError.textContent = "";
      }
    }
    
    function checkPasswordStrength() {
      const password = document.getElementById('password').value;
      const strengthElement = document.getElementById('password-strength');
      const errorElement = document.getElementById('password-error');
      const hasSpecialChars = /[!@#$%^&*(),.?":{}|<>]/.test(password);
      
      if (password.length === 0) {
        strengthElement.innerHTML = '';
        strengthElement.className = 'password-strength';
        errorElement.style.display = 'none';
      } else if (password.length <= 6) {
        strengthElement.innerHTML = 'ضعيف';
        strengthElement.className = 'password-strength weak';
        errorElement.style.display = 'block';
        errorElement.textContent = 'كلمة المرور ضعيفة. يرجى إدخال كلمة مرور أقوى.';
      } else if (password.length <= 8) {
        strengthElement.innerHTML = 'متوسط';
        strengthElement.className = 'password-strength medium';
        errorElement.style.display = 'block';
        errorElement.textContent = 'كلمة المرور متوسطة. يرجى إدخال كلمة مرور تحتوي على أحرف خاصة.';
      } else if (password.length > 8 && hasSpecialChars) {
        strengthElement.innerHTML = 'قوي';
        strengthElement.className = 'password-strength strong';
        errorElement.style.display = 'none';
      }
    }
    
    function validatePasswordStrength() {
      const passwordStrength = document.getElementById('password-strength').textContent;
      const errorElement = document.getElementById('password-error');
      if (passwordStrength === 'ضعيف' || passwordStrength === 'متوسط') {
        errorElement.style.display = 'block';
        errorElement.textContent = 'يرجى تحسين كلمة المرور لتكون قوية.';
        return false;
      }
      return true;
    }
    
    function togglePasswordVisibility() {
      const passwordInput = document.getElementById('password');
      const toggleButton = document.getElementById('togglePassword');
      if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleButton.textContent = '🙉';
      } else {
        passwordInput.type = 'password';
        toggleButton.textContent = '🙈';
      }
    }
    
    function toggleCraftDescription() {
  const craftsmanOption = document.querySelector('input[name="role"][value="craftsman"]');
  const craftDescriptionField = document.getElementById('craft_description');
  if (craftsmanOption.checked) {
    craftDescriptionField.style.display = 'block';
    craftDescriptionField.setAttribute('required', 'true');
  } else {
    craftDescriptionField.style.display = 'none';
    craftDescriptionField.removeAttribute('required');
  }
}

    
    window.addEventListener('DOMContentLoaded', function() {
      const role = document.querySelector('input[name="role"]:checked');
      if (role && role.value === 'craftsman') {
        document.getElementById('craft_description').style.display = 'block';
      }
    });
  </script>
</body>
</html>
