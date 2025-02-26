<?php
include("config.php");

$isLoggedIn = isset($_SESSION['user_id']);
$userName = $isLoggedIn ? $_SESSION['first_name'] : null;

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
$searchTerm = '';
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>جِسر</title>
    <link rel="stylesheet" href="css.css">
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
            background-color: #fdf9f0;
            margin: 0;
            padding: 0;
        }
        
        .header {
            top: 0;
            right: 0;
            left: 0;
            background-color: #fdf9f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: fixed;
            padding: 1.5rem 7%;
            z-index: 1000;
            direction: rtl;
            width: 100%;
        }
        .header .logo img {
            height: 5rem;
        }

        .menu-toggle {
            display: none;
            font-size: 24px;
            background: none;
            border: none;
            color: #7b612b;
            cursor: pointer;
        }

        .navbarheader {
            display: flex;
            gap: 3rem;
        }
        .navbarheader a {
            font-size: 2rem;
            color: #7b612b;
            font-weight: bold;
            text-decoration: none;
            transition: color 0.3s;
        }
        .navbarheader a:hover {
            color: #A4AC86;
        }

        .icons {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        .icons img {
            cursor: pointer;
            transition: transform 0.3s ease;
        }
        .icons img:hover {
            transform: scale(1.2);
        }

        .dropdown {
            position: relative;
            display: inline-block;
        }
        .dropdown-menu {
            display: none;
            position: absolute;
            margin-top: 8px;
            right: 0;
            background-color: #fff;
            border: 1px solid #ddd;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            z-index: 1000;
            width: 150px;
            padding: 5px 0;
            text-align: right;
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
            font-size: 14px;
            transition: background-color 0.2s ease;
        }
        .dropdown-menu a:hover {
            background-color: #f5f5f5;
        }
.search-box {
    display: none;
    position: absolute;
    top: 55px; 
    right: 5%; 
    border: 0.5px solid rgb(146, 130, 106);
    background-color: #FEFCF9;
    padding: 2px 8px;
    border-radius: 25px;
    width: 200px;
    transition: all 0.3s ease;
    z-index: 999;
}

.search-box input {
    padding: 8px 10px;
    width: 100%;
    font-size: 1.1rem;
    border: none;
    border-radius: 20px;
    outline: none;
    background-color: #FEFCF9;
}

.search-box button {
    display: none;
}

@media (max-width: 768px) {
    .search-box {
        right: 10%; 
        width: 70%; 
    }

    .search-box input {
        font-size: 1rem; 
    }
}

@media (max-width: 480px) {
    .search-box {
        right: 5%;
        width: 85%;
    }

    .search-box input {
        font-size: 0.9rem;
    }
}


        @media (max-width: 768px) {
            .menu-toggle {
                display: block;
            }
            .navbarheader {
                display: none;
                flex-direction: column;
                position: absolute;
                top: 70px;
                right: 0;
                background: #fdf9f0;
                width: 200px;
                padding: 10px;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                border-radius: 10px;
                text-align: right;
            }
            .navbarheader.show {
                display: flex;
            }
        }
    </style>
</head>
<body>

<header class="header">
<div class="logo">
        <a href="#" onclick="toggleMenu()">
            <img src="./images/logo.png" alt="Logo">
        </a>
    </div>


    <nav class="navbarheader">
        <a href="homepage.php">الرئيسيـة</a>
        <a href="product.php">المنتجـات</a>
        <a href="homepage.php#aboutUs">من نـحن</a>
        <a href="homepage.php#footer">تواصل معنا</a>
    </nav>
    <div class="icons">
    <img src="images/search.png" alt="بحث" width="24px" onclick="toggleSearchBox()">
    <a href="cart.php">
            <img src="images/cart.png" alt="السلة" width="24px">
        </a>
        <div class="dropdown">
            <a class="account-btn" onclick="toggleDropdownh()">
                <img src="<?php echo $profile_picture; ?>" alt="الحساب" style="border-radius: 50%; width: 2.5rem; height: 2.5rem; object-fit: cover;">
            </a>
            <div id="dropdownMenu" class="dropdown-menu">
                <a href="customer-edit-profile.php"> الملف الشخصي</a>
                <a href="logout.php"> تسجيل الخروج</a>
            </div>
        </div>
        <a class="menu-btn" onclick="toggleSidebar()">
            <img src="./images/line.png" alt="شريط">
        </a>

    </div>
    <script>
    function toggleDropdownh() {
    const dropdownMenu = document.getElementById("dropdownMenu");
    dropdownMenu.style.display = dropdownMenu.style.display === "block" ? "none" : "block";
}

</script>
    </div>
    <div id="searchBox" class="search-box">
    <form method="POST" action="search_results.php">
        <input type="text" name="search_term" placeholder="ابحث عن منتج..." required>
        <button type="submit" name="search">
            <img src="images/search.png" width="24px">
        </button>
    </form>
</div>
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
                case 'homepage': 
                    window.location.href = 'homepage.php'; 
                    break;
                    case 'orders':
                    window.location.href = 'orders.php'; 
                    break;
                    case 'cart':
                    window.location.href = 'cart.php'; 
                    break;
                    case 'wishlist':
                    window.location.href = 'wishlist.php'; 
                    break;
                default:
                    window.location.href = 'logout.php'; 
            }
        }

        function toggleDropdownh() {
    const dropdownMenu = document.getElementById("dropdownMenu");
    dropdownMenu.style.display = dropdownMenu.style.display === "block" ? "none" : "block";
}

    </script>
    <script>
        function toggleSearchBox() {
            var searchBox = document.getElementById("searchBox");
            searchBox.style.display = (searchBox.style.display === "none" || searchBox.style.display === "") ? "block" : "none";
        }
    </script>
<script>
    function toggleMenu() {
        let menu = document.querySelector(".navbarheader");
        menu.classList.toggle("show");
    }

    function toggleSearchBox() {
        var searchBox = document.getElementById("searchBox");
        searchBox.style.display = (searchBox.style.display === "none" || searchBox.style.display === "") ? "block" : "none";
    }

    function toggleDropdownh() {
        const dropdownMenu = document.getElementById("dropdownMenu");
        dropdownMenu.style.display = dropdownMenu.style.display === "block" ? "none" : "block";
    }
</script>

</body>
</html>
