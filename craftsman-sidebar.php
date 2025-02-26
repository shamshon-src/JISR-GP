<?php
include("config.php");
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'craftsman') {
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
    filter: brightness(0) saturate(100%)
          invert(33%) sepia(54%) saturate(209%) hue-rotate(2deg) brightness(93%) contrast(88%); 
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

 
        </style>
     <div class="sidebar" id="sidebar">
        <a href="#" class="account-icon">
        <img src="<?php echo $profile_picture; ?>" alt="الحساب" style="border-radius: 50%; width: 2.5rem; height: 2.5rem; object-fit: cover;">
        </a>
        <div class="acc">
             <h2>أهلًا بك، <?php echo htmlspecialchars($first_name); ?><br></h2>
             <h3>حرفي</h3>
        </div>
    <nav class="menu">



        <a href="addproduct.php" class="menu-item <?php echo ($current_page == 'addproduct.php') ? 'active' : ''; ?>">
    <img src="./images/add-product.png" alt="إضافة منتج"> إضافة منتج
</a>

    
<a href="craftman_manage_products.php" class="menu-item <?php echo ($current_page == 'craftman_manage_products.php') ? 'active' : ''; ?>">
    <img src="./images/pro_man.png" alt="إدارة المنتجات"> إدارة المنتجات
</a>

<a href="craftman-orders-manegment.php" class="menu-item <?php echo ($current_page == 'craftman-orders-manegment.php') ? 'active' : ''; ?>">
    <img src="./images/manage_orders.png" alt="ادارة الطلبات"> إدارة الطلبات
</a>

<a href="craftsman_terms-and-conditions.php" class="menu-item <?php echo ($current_page == 'craftsman_terms-and-conditions.php') ? 'active' : ''; ?>">
    <img src="./images/شروط.png" alt="الشروط والاحكام "> الشروط والاحكام 
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
                case 'addproduct.php':
                    window.location.href = 'addproduct.php'; 
                    break;
                    case 'craftman_manage_products.php':
                    window.location.href = 'craftman_manage_products.php'; 
                    break;
                case 'craftman-orders-manegment.php':
                    window.location.href = 'craftman-orders-manegment.php'; 
                    break;
                    case 'craftsman_terms-and-conditions.php':
                    window.location.href = 'craftsman_terms-and-conditions.php'; 
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