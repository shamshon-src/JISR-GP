<?php
include("config.php");
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
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
    $profile_picture = 'images/usercust.png';
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
    top: 2rem;
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
    z-index: 1000;
}
.sidebar .account-icon img {
    width: 5rem; 
    margin: auto;
}
.sidebar a.logout-btn {
           margin: auto 10px 70px auto;
           text-align: center;
           font-size:20px;
        }
        .sidebar a.logout-btn img {
            width: 4rem;
            height: auto;
            margin-right: 15px;
}

       .sidebar a {
    text-align: center;
    font-size: 21px;
    display: flex;
    align-items: center;
    padding: 10px 15px;
    text-decoration: none;
    color: #7b612b;
    margin: 5px 40px 8px;
    flex-direction: row; 
    justify-content: flex-start; 
}
.sidebar a img {
    margin-top: -2px; 
    filter: none;
    transition: transform 0.3s ease-in-out, filter 0.3s ease-in-out;
}

.icon-home {
    margin-right: -10px;
    margin-left: 14px;
    width: 2.4rem;
    height: auto;
}
.icon-orders {
    margin-right: -7px;
    margin-left: 14px;
    width: 1.8rem;
    height: auto;
}
.icon-fav {
    margin-right: -14px;
    margin-left: 14px;
    width: 2.9rem;
    height: auto;
}
.icon-cart {
    margin-right: -10px;
    margin-left: 14px;
    width: 2.4rem;
    height: auto;
}

.sidebar a.active .icon-home,
.sidebar a.active .icon-orders,
.sidebar a.active .icon-fav,
.sidebar a.active .icon-cart {
    filter: brightness(0) saturate(100%) invert(14%) sepia(59%) saturate(468%) hue-rotate(99deg) brightness(93%) contrast(93%);
    transform: scale(1.1);غغ
    transition: all 0.3s ease-in-out;
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
        .acc h3 {
    font-size: 2rem; 
    color: #7b612b;
    margin-top: -10px;
    margin-bottom: 24px;
    text-align: center;
}
.sidebar a.active img {
    filter: none;
}
.menu-item {
        position: relative;
    }
    .submenu {
        display: none;
        padding-left: 20px;
        background-color:#EEE9DF;
    }
    .submenu a {
        padding: 8px 10px;
        font-size: 14px;
        border: none;
        color: #7b612b;
    }
    .submenu a:hover {
        background-color:#ddd;
    }
    .arrow {
        margin-left: auto;
        font-size: 12px;
    }
    .menu-item.active .submenu {
        display: block;
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
            margin-right: 54px;
            margin-top: 5px;
        }
        .acc {
            color: #725C3A;
            text-align: center; 
            text-size:1.5rem
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
        </style>
         <div class="sidebar" id="sidebar">
    <a href="#" class="account-icon">
        <img src="<?php echo $profile_picture; ?>" alt="الحساب" style="border-radius: 50%; width: 8rem; height: 8rem; object-fit: cover; margin: auto;">
    </a>
    <div class="acc">
        <h2>أهلًا بك، <?php echo htmlspecialchars($first_name); ?><br></h2>
        <h3>عميل</h3>
    </div>
    <nav class="menu">
    <div class="menu-item">
        <a href="homepage.php" class="menu-item <?php echo ($current_page == 'homepage.php') ? 'active' : ''; ?>">
            <img src="./images/home.png" alt="الرئيسية" class="icon-home"> الرئيسية
        </a>
        <a href="my_order.php" class="menu-item <?php echo ($current_page == 'my_order.php') ? 'active' : ''; ?>">
            <img src="./images/orders.png" alt="طلباتي" class="icon-orders"> طلباتـي
        </a>
        <a href="fav.php" class="menu-item <?php echo ($current_page == 'fav.php') ? 'active' : ''; ?>">
            <img src="images/fav1.png" alt="المفضلة" class="icon-fav"> المفضلـة
        </a>
        <a href="cart.php" class="menu-item <?php echo ($current_page == 'cart.php') ? 'active' : ''; ?>">
            <img src="./images/cart1.png" alt="السلة" class="icon-cart"> السلـة
        </a>
    </div>
</nav>


    <a href="logout.php" class="logout-btn">
        <img src="./images/logout1.png" alt="تسجيل الخروج"> <span>تسجيل الخروج</span>
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
                case 'homepage': 
                    window.location.href = 'homepage.php'; 
                    break;
                    case 'orders':
                    window.location.href = 'my_order.php'; 
                    break;
                    case 'cart':
                    window.location.href = 'cart.php'; 
                    break;
                    case 'fav':
                    window.location.href = 'fav.php'; 
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