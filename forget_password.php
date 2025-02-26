<?php
session_start();
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    unset($_SESSION['recover_email']);
    unset($_SESSION['security_question']);
    unset($_SESSION['reset_allowed']);
}
include 'config.php';
ob_start();
$error = "";
$show_security_question = false;
$show_password_fields = false;
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['email']) && !isset($_SESSION['recover_email'])) {
    $email = trim($_POST['email']);
    $sql = "SELECT security_question FROM users WHERE email = ?";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        $error = "خطأ في الاستعلام: " . $mysqli->error;
    } else {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->bind_result($security_question);
        if ($stmt->fetch()) {
            $_SESSION['recover_email'] = $email;
            $_SESSION['security_question'] = $security_question;
            $show_security_question = true;
        } else {
            $error = "البريد الإلكتروني غير مسجل!";
        }
        $stmt->close();
    }
}
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['security_answer']) && isset($_SESSION['recover_email'])) {
    $security_answer = trim($_POST['security_answer']);
    $email = $_SESSION['recover_email'];
    $sql = "SELECT security_answer FROM users WHERE email = ?";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        $error = "خطأ في الاستعلام: " . $mysqli->error;
    } else {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        if ($row) {
            $answer_from_db = trim($row['security_answer']);
            if (password_verify($security_answer, $answer_from_db)) {
                $_SESSION['reset_allowed'] = true;
                $show_password_fields = true;
            } else {
                $error = "الإجابة غير صحيحة!";
            }
        } else {
            $error = "حدث خطأ، لم يتم العثور على المستخدم!";
        }
        $stmt->close();
    }
}
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['new_password']) && isset($_SESSION['reset_allowed'])) {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $email = $_SESSION['recover_email'];
    if ($new_password !== $confirm_password) {
        $error = "كلمتا المرور غير متطابقتين!";
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET password = ? WHERE email = ?";
        $stmt = $mysqli->prepare($sql);
        if (!$stmt) {
            $error = "خطأ في الاستعلام: " . $mysqli->error;
        } else {
            $stmt->bind_param("ss", $hashed_password, $email);
            if ($stmt->execute()) {
                unset($_SESSION['reset_allowed']);
                unset($_SESSION['recover_email']);
                unset($_SESSION['security_question']);
                header("Location: login.php");
                exit();
            } else {
                $error = "حدث خطأ، حاول مرة أخرى!";
            }
            $stmt->close();
        }
    }
}
ob_end_flush();
?>
<!DOCTYPE html>
<html lang="ar">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>استعادة كلمة المرور</title>
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
      margin-left: 10px;
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
      margin-bottom:80px;
      max-width: 600px;
      background: white;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
      text-align: center;
    }
    .form-section h2,
    .form-section h3 {
      margin-bottom: 10px;
      color: #63483b;
      font-size: 24px;
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
      min-height: 50px;
      border-radius: 49px;
      font-size: 15px;
      margin-right: 20px;
      border: none;
      line-height: 1.15;
      padding-right: 18px;
      outline: 1px solid rgb(187, 185, 180);
    }
    .input:hover {
      outline: 1px solid rgb(130, 128, 125);
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
    .error-message {
      color: #D9534F;
      font-weight: bold;
      margin-top: 10px;
    }
    .left-section {
      position: relative;
      width: 45%;
      height: auto;
      min-width: 600px;
      top: -280px;
      right: 120px;
    }
    .left-section img {
      position: absolute;
      height: auto;
    }
    .left-section img:first-child {
      z-index: 1;
      width: 80%;
      top: 8%;
      left: 20%;
    }
    .left-section img:nth-child(2) {
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
      .left-section {
        display: none;
      }
      .form-section {
        width: 100%;
        max-width: 100%;
        padding: 15px;
      }
    }
    .password-strength {
      font-size: 14px;
      font-weight: bold;
      padding-top: 5px;
    }
    .password-strength.weak { color: red; }
    .password-strength.medium { color: orange; }
    .password-strength.strong { color: green; }
  </style>
</head>
<body>
  <div class="logoContent">
    <a href="#"><img src="./images/logo.png" alt="Logo"></a>
  </div>
  <div class="container">
    <div class="form-section">
      <h2>استعادة كلمة المرور</h2>
      <?php if (!empty($error)) { echo "<p class='error-message'>$error</p>"; } ?>
      <?php if (!isset($_SESSION['recover_email']) && !$show_security_question && !$show_password_fields) { ?>
        <form action="" method="POST">
          <input type="email" name="email" required class="input" placeholder="البريد الإلكتروني">
          <button type="submit" class="btn">متابعة</button>
        </form>
      <?php } ?>
      <?php if (isset($_SESSION['recover_email']) && !$show_password_fields && $show_security_question) { ?>
        <form action="" method="POST">
          <label>السؤال الأمني:</label>
          <p><strong><?php echo htmlspecialchars($_SESSION['security_question'], ENT_QUOTES, 'UTF-8'); ?></strong></p>
          <label for="security_answer">الإجابة:</label>
          <input type="text" name="security_answer" required class="input">
          <button type="submit" class="btn">تحقق</button>
        </form>
      <?php } ?>
      <?php if ($show_password_fields) { ?>
        <form action="" method="POST">
          <h3>إعادة تعيين كلمة المرور</h3>
          <label for="new_password">كلمة المرور الجديدة:</label>
          <input type="password" id="new_password" name="new_password" required oninput="checkPasswordStrength()" class="input">
          <div id="password-strength" class="password-strength"></div>
          <label for="confirm_password">تأكيد كلمة المرور:</label>
          <input type="password" id="confirm_password" name="confirm_password" required class="input">
          <button type="submit" class="btn">إعادة تعيين</button>
          <div id="password-error" class="password-error" style="color: red;"></div>
        </form>
      <?php } ?>
    </div>
    <div class="left-section">
      <img src="images/back.png" alt="خلفية">
      <img src="images/logg.png" alt="صورة تزيين">
    </div>
  </div>
  <script>
  var passwordStrength = '';

  function checkPasswordStrength() {
    var password = document.getElementById('new_password').value;
    var strengthElement = document.getElementById('password-strength');
    var errorElement = document.getElementById('password-error');
    var hasSpecialChars = /[!@#$%^&*(),.?":{}|<>]/.test(password);

    if (password.length === 0) {
      strengthElement.innerHTML = '';
      strengthElement.className = 'password-strength';
      errorElement.style.display = 'none';
      passwordStrength = '';
    } else if (password.length <= 6) {
      strengthElement.innerHTML = 'ضعيف';
      strengthElement.className = 'password-strength weak';
      errorElement.style.display = 'block';
      errorElement.textContent = 'كلمة المرور ضعيفة. يرجى إدخال كلمة مرور أقوى.';
      passwordStrength = 'ضعيف';
    } else if (password.length <= 8) {
      strengthElement.innerHTML = 'متوسط';
      strengthElement.className = 'password-strength medium';
      errorElement.style.display = 'block';
      errorElement.textContent = 'كلمة المرور متوسطة. يفضل إدخال كلمة مرور تحتوي على أحرف خاصة مثل "@#$%^&*".';
      passwordStrength = 'متوسط';
    } else if (password.length > 8 && hasSpecialChars) {
      strengthElement.innerHTML = 'قوي';
      strengthElement.className = 'password-strength strong';
      errorElement.style.display = 'none';
      passwordStrength = 'قوي';
    } else {
      strengthElement.innerHTML = 'متوسط';
      strengthElement.className = 'password-strength medium';
      errorElement.style.display = 'block';
      errorElement.textContent = 'يفضل استخدام أحرف خاصة لزيادة قوة كلمة المرور.';
      passwordStrength = 'متوسط';
    }
  }

  document.addEventListener("DOMContentLoaded", function () {
    var forms = document.querySelectorAll("form");
    forms.forEach(function(form) {
      form.addEventListener("submit", function (event) {
        var newPasswordField = document.getElementById('new_password');
        var confirmPasswordField = document.getElementById('confirm_password');
        var errorElement = document.getElementById('password-error');

        if (newPasswordField && confirmPasswordField) {
          var password = newPasswordField.value;
          var confirmPassword = confirmPasswordField.value;

          if (password !== confirmPassword) {
            event.preventDefault();
            errorElement.style.display = 'block';
            errorElement.textContent = 'كلمة المرور وتأكيدها غير متطابقين!';
          } else if (passwordStrength === 'ضعيف' || passwordStrength === 'متوسط') {
            event.preventDefault();
            errorElement.style.display = 'block';
            errorElement.textContent = 'يجب أن تكون كلمة المرور قوية قبل المتابعة!';
          } else {
            errorElement.style.display = 'none';
          }
        }
      });
    });
  });
</script>

</body>
</html>
