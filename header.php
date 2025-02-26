<?php
include("config.php");
$searchTerm = '';
?>
<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css.css">
    <style>
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
            z-index: 10;
            direction: rtl;
            width: 100%;
        }
        .header .logo img {
            height: 5rem;
            margin-top:10px;
            margin-right:-13px;

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
            margin-right:40px;
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
            position: relative;
            z-index: 10;
            bottom:2px;

            
        }
        .icons img:hover {
            transform: scale(1.2);
        }

        .cart-icon {
    position: relative;
    top:3px;
}
.header .account-btn img {
    width: 2.6rem !important;
    height: 2.6rem !important;
}
.search-box {
    display: none;
    position: absolute;
    bottom: 30px;
    right: 1140px;
    border: 0.5px solid rgb(146, 130, 106);
    background-color: #FEFCF9;
    padding: 2px 8px;
    border-radius: 25px;
    width: 200px;
    transition: all 0.3s ease;
    z-index: 1;
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

.search-results {
    margin-top: 100px;
    text-align: center;
}
.product-card {
    display: inline-block;
    max-width: 100%;
    margin: 0.625rem;
    padding: 0.625rem;
    background-color: #faf7f4;
    border: 0.0625rem solid #ddd;
    border-radius: 0.3125rem;
    text-align: center;
    box-shadow: 0 0.125rem 0.3125rem rgba(0, 0, 0, 0.1);
}
.product-card img {
    max-width: 100%;
    max-height: auto;
    object-fit: cover;
    border-radius: 0.3125rem;
}
.product-card h2 {
    font-size: 2.5rem;
    color: #725C3A;
    margin: 0.625rem 0;
    text-align: center;
}
.product-card h3 {
    font-size: 1.8rem;
    color: #725C3A;
    margin: 0.625rem 0;
}
.product-card h4 {
    font-size: 1.6rem;
    color: #A4AC86;
    margin: 0.625rem 0;
}
.product-card a {
    display: inline-block;
    margin-top: 0.625rem;
    padding: 0.5rem 1rem;
    background-color: #725C3A;
    color: #fff;
    text-decoration: none;
    border-radius: 0.3125rem;
}
.product-card a:hover {
    background-color: #57482b;
}
@media (max-width: 1024px) {
    .header {
        padding: 1.5rem 5%;
    }
    .header .navbar a {
        font-size: 1.8rem;
        margin-left: 1.5rem;
    }
    .product-card {
        width: 45%;
        margin: 0.625rem;
    }
    .product-card h2 {
        font-size: 2.2rem;
    }
    .product-card h3 {
        font-size: 1.6rem;
    }
    .product-card h4 {
        font-size: 1.4rem;
    }
    .search-box input {
        width: 100%;
    }
}
@media (max-width: 768px) {
    .header {
        padding: 1.2rem 3%;
    }
    .header .logo img {
        height: 5.5rem;
    }
    .header .navbar a {
        font-size: 1.6rem;
        margin-left: 1rem;
    }
    .icons {
        gap: 10px;
    }
    .product-card {
        width: 100%;
        margin: 0.625rem 0;
        padding: 1.25rem;
    }
    .product-card h2 {
        font-size: 2rem;
    }
    .product-card h3 {
        font-size: 1.6rem;
    }
    .product-card h4 {
        font-size: 1.4rem;
    }
    .search-box {
        right: 5%;
    }
    .search-box input {
        width: 100%;
    }
}
    </style>
    <script>
        function toggleSearchBox() {
            var searchBox = document.getElementById("searchBox");
            searchBox.style.display = (searchBox.style.display === "none" || searchBox.style.display === "") ? "block" : "none";
        }
    </script>
</head>
<body>
<header class="header">
    <div class="logo">
        <a href="#"><img src="./images/logo.png" alt="Logo"></a>
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
  <img src="images/cart.png" alt="السلة" width="24px" class="cart-icon">
</a>      
         <a href="login.php"><img src="images/user.png" alt="تسجيل الدخول" width="24px"></a>
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
</body>
</html>