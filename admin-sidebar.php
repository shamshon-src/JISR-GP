<?php
include("config.php");
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$current_page = basename($_SERVER['PHP_SELF']);
 
$user_id = $_SESSION['user_id'];
$query = "SELECT profile_picture FROM users WHERE id = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($profile_picture);
$stmt->fetch();
$stmt->close();

if (empty($profile_picture)) {
    $profile_picture = 'uploads/default-profile.png';  
}
$first_name = $_SESSION['first_name'];


?>
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
}



       .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #fdf9f0;
            padding: 10px 5%;
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

        .submenu-line {
    display: inline-block;
    width: 10px;      
    height: 1.9px;      
    background-color: #7b612b;
    margin-left: 13px; 
    vertical-align: middle;
    
}
        </style>
     <div class="sidebar" id="sidebar">
        <a href="#" class="account-icon">
        <img src="<?php echo $profile_picture; ?>" alt="الحساب" style="border-radius: 50%; width: 8rem; height: 8rem; object-fit: cover;">
        </a>
        <div class="acc">
             <h2>أهلًا بك، <?php echo htmlspecialchars($first_name); ?><br></h2>
             <h3>مدير الموقع</h3>
        </div>
    <nav class="menu">

        <div class="menu-item <?php echo (in_array($current_page, ['manage_craftsmen.php', 'manage_customer.php'])) ? 'active' : ''; ?>">
          <a href="#" onclick="toggleDropdown('users-dropdown')">
              <img src="./images/manage_users.png" alt="إدارة الحسابات"> إدارة الحسابات
              <span class="arrow">▼</span>
          </a>
          <div class="submenu" id="users-dropdown">
              <a href="manage_craftsmen.php" class="<?php echo ($current_page == 'manage_craftsmen.php') ? 'active' : ''; ?>">
                  <span class="submenu-line"></span> الحرفيين
              </a>
              <a href="manage_customer.php" class="<?php echo ($current_page == 'manage_customer.php') ? 'active' : ''; ?>">
                  <span class="submenu-line"></span> العملاء
              </a>
          </div>
      </div>

      <a href="admin_manage_products.php" class="menu-item <?php echo ($current_page == 'admin_manage_products.php') ? 'active' : ''; ?>">
          <img src="./images/pro_man.png" alt="إدارة المنتجات"> إدارة المنتجات
      </a>
  
      <a href="admin-orders-manegment.php" class="menu-item <?php echo ($current_page == 'admin-orders-manegment.php') ? 'active' : ''; ?>">
          <img src="./images/manage_orders.png" alt="إدارة الطلبات"> إدارة الطلبات
      </a>
    </nav>
    <a href="logout.php" class="logout-btn">
      <img src="./images/logout.png" alt="تسجيل الخروج"> <span>تسجيل الخروج</span>
    </a>
  </div>




    <script>

document.addEventListener("click", function(event) {
    const dropdownMenu = document.getElementById("dropdownMenu");
    const menuIcon = document.querySelector(".menu-icon");
    if (!menuIcon.contains(event.target) && !dropdownMenu.contains(event.target)) {
        dropdownMenu.style.display = "none";
    }
});

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
                case 'manage_craftsmen':
                    window.location.href = 'manage_craftsmen.php'; 
                    break;
                    case 'manage_customer':
                    window.location.href = 'manage_customer.php';
                    break;
                case 'manage_products':
                    window.location.href = 'admin_manage_products.php'; 
                    break;;
                    case 'manage_orders':
                    window.location.href = 'admin-orders-manegment.php'; 
                    break;
                default:
                    window.location.href = 'logout.php'; 
            }
        }

        function toggleDropdown(id) {
        const dropdown = document.getElementById(id);
        dropdown.style.display = dropdown.style.display === "block" ? "none" : "block";
    }

    </script>