<?php
include("config.php");
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $stmt = $mysqli->prepare("SELECT id, password, role, first_name, last_name FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name'] = $user['last_name'];  
            $_SESSION['role'] = $user['role'];
            if ($user['role'] === "admin") {
                header("Location: admin_dashboard.php");
                exit();
            } elseif ($user['role'] === "customer") {
                header("Location: homepage.php");
                exit();
            } elseif ($user['role'] === "craftsman") {
                header("Location: craftsman_dashboard.php");
                exit();
            }
        } else {
            echo "<script>alert('كلمة المرور غير صحيحة.');</script>";
        }
    } else {
        echo "<script>alert('البريد الإلكتروني غير مسجل.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>صفحة تسجيل الدخول</title>
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
      font-family: 'TheYearOfTheCamel';
      background-color: #fdf9f0;
      direction: rtl;
      margin: 0;
      padding: 0;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
    }
    .logoContent {
      text-align: right;
      margin-top: -40rem;
      margin-left:10px;
    }
    .logoContent img {
      height: 4rem;
    }
    .container {
      display: flex;
      justify-content: center;
      align-items: center;
      width: 90%;
      max-width: 1200px;
      gap: 20px;
    }
    .form-section {
      border-radius: 22px;
      padding: 20px;
      width: 45%;
      max-width: 600px;
      background: white;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
      text-align: center;
    }
    .form-section h2 {
      margin-bottom: 10px;
      color: #63483b;
      font-size: 24px;
    }
    .form-section p {
      margin-bottom: 20px;
      color: #8d6e63;
      font-size: 16px;
    }
    .form-section form {
      display: flex;
      flex-direction: column;
      gap: 10px;
      text-align: right;
    }
    .input {
      width: 87%;
      background-color: #F7F5F0;
      color: #242424;
      padding: .15rem .5rem;
      min-height: 40px; /* ارتفاع الحقل أصبح أقل */
      border-radius: 49px;
      font-size: 15px;
      margin-right:20px;
      border: none;
      line-height: 1.15;
      padding-right: 18px;
      outline: 1px solid rgb(187, 185, 180);
    }
    .password-container .input {
      width: 91%;
    }
    .input:hover {
      outline: 1px solid rgb(130, 128, 125);
    }
    .password-container {
      position: relative;
      width: 95%;
    }
    .password-container span {
      position: absolute;
      left: 10px;
      top: 50%;
      transform: translateY(-50%);
      cursor: pointer;
      font-size: 18px;
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
      font-size: 26px;
      color: white;
      margin: 17px auto 8px;
    }
    .btn:hover {
      transform: translateY(3px);
      box-shadow: none;
      background-color: #5E4C2A;
    }
    .btn:active {
      opacity: 0.5;
    }
    .forgot-password,
    .account {
      font-size: 15px;
      text-align: center;
      margin-top: 0px;
    }
    .forgot-password a,
    .account a {
      color: #0a880ce1;
      text-decoration: none;
    }
    .forgot-password a:hover,
    .account a:hover {
      text-decoration: underline;
    }
    .image-section {
      position: relative;
      width: 45%;
      height: auto;
      min-width: 600px;
      top: -280px; 
      right:120px;
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
    }
  </style>
</head>
<body>
  <div class="logoContent">
    <a href="#"><img src="./images/logo.png" alt="Logo"></a>
  </div>
  <div class="container">
    <div class="form-section">
      <h2>تسجيل الدخول</h2>
      <p>مرحبًا بعودتك</p>
      <form action="login.php" method="POST" onsubmit="return validatePasswordStrength()">
      <input type="email" name="email" placeholder="البريد الإلكتروني" class="input" required>
        <div class="password-container">
          <input type="password" id="password" name="password" placeholder="كلمة المرور" class="input" required>
          <span id="togglePassword" onclick="togglePasswordVisibility()" style="position: absolute; left: 28px; top: 50%; transform: translateY(-50%); background: none; border: none; font-size: 19px; cursor: pointer;">🙈</span>
        </div>
        <button type="submit" class="btn">دخول</button>
        <div class="forgot-password">
          <a href="forget_password.php">نسيت كلمة المرور؟</a>
        </div>
        <div class="account">
          ليس لديك حساب؟ <a href="Register.php">سجل الآن</a>
        </div>
      </form>
    </div>
    <div class="image-section">
      <img src="images/back.png" alt="صورة التسجيل">
      <img src="images/logg.png" alt="صورة التسجيل">
    </div>
  </div>

  <script>
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
    
  </script>
  <script>
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

  function validatePasswordStrength() {
      const password = document.getElementById('password').value;
      const hasSpecialChars = /[!@#$%^&*(),.?":{}|<>]/.test(password);
      
      if (password.length === 0) {
          alert("يرجى إدخال كلمة مرور.");
          return false;
      } else if (password.length <= 6) {
          alert("كلمة المرور ضعيفة جدًا. يرجى اختيار كلمة مرور أقوى.");
          return false;
      } else if (password.length <= 8 && !hasSpecialChars) {
          alert("كلمة المرور متوسطة. يجب أن تحتوي على أحرف خاصة لتكون أقوى.");
          return false;
      }
      return true;
  }
</script>

</body>
</html>
