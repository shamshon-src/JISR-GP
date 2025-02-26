<?php
include("config.php");
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'craftsman') {
    header("Location: login.php");
    exit();
}

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
            background-color:rgb(253, 242, 240);
            padding: 10px 5%;
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
        .content {
            text-align: center; 
            flex: left;
            padding: 20px;
        }
        
        </style>
<header class="header">
    <div class="icons">
        <!-- زر الشريط -->
        <a class="menu-btn" onclick="toggleSidebar()">
            <img src="./images/line.png" alt="شريط">
        </a>
        <!-- زر الحساب -->
        <div class="dropdown">
            <a class="account-btn" onclick="toggleDropdownh()">
            <img src="<?php echo $profile_picture; ?>" alt="الحساب" style="border-radius: 50%; width: 2.5rem; height: 2.5rem; object-fit: cover;">
            <div id="dropdownMenu" class="dropdown-menu">
                <a href="edit-profile.php">
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
        <a href="admin_dashboard.php">الرئيسية</a>
    </nav>

    <div class="logoContent">
        <a href="#"><img src="./images/logo.png" alt="Logo" style="height: 4rem;"></a>
    </div>
    <script>
    function toggleDropdownh() {
    const dropdownMenu = document.getElementById("dropdownMenu");
    dropdownMenu.style.display = dropdownMenu.style.display === "block" ? "none" : "block";
}
</script>

</header>

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
                    break;
                    case 'payment_manage':
                    window.location.href = 'payment_manage.php'; 
                    break;
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