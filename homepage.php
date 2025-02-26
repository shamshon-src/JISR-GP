<?php
session_start();
include("config.php");
$isLoggedIn = isset($_SESSION['user_id']);
if ($isLoggedIn) {
    $userName = $_SESSION['first_name'];
    $userImage = $_SESSION['user_image'] ?? ''; 
} else {
    $userName = null;
    $userImage = null;
}
$query = "SELECT ProductID, ProductName, Description, Price, Category, ProductImage FROM product WHERE IsApproved = 1 ORDER BY sales_count DESC LIMIT 10 "; 
$result6 = mysqli_query($mysqli, $query);
$products = [];
while ($row = mysqli_fetch_assoc($result6)) {
    $products[] = $row;
}
?>
<!DOCTYPE html>
<html lang="ar">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>الصفحة الرئيسية</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <?php if ($isLoggedIn): ?>
        <?php include("customer_header.php"); ?>
        <?php include("homepage-sidebar.php"); ?>
    <?php else: ?>
        <?php include("header.php"); ?>
    <?php endif; ?>
    <style> @font-face {
            font-family: 'TheYearOfTheCamel';
            src: url('fonts/TheYearofTheCamel-Light.otf') format('opentype');
            font-weight: normal;
            font-style: normal;
        }
        * {
            font-family: 'TheYearOfTheCamel', sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            outline: none;
            border: none;
            text-decoration: none;
            text-transform: capitalize;
            transition: .2s linear;
        }
        html {
            font-size: 62.5%;
            overflow-x: hidden;
            scroll-padding-top: 7rem;
            scroll-behavior: smooth;
        }
        html::-webkit-scrollbar {
            width: 0.3rem;
        }
        html::-webkit-scrollbar-track {
            background: transparent;
        }
        html::-webkit-scrollbar-thumb {
            background-color: var(--white);
            border-radius: 5rem;
        }
        body {
            direction: rtl;
            background-color: #fdf9f0;
        }
        section {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Alfa Slab One', cursive;
            display: flex;
            justify-content: center;
            background-color: #f9f3e7;
        }
        .Content {
            padding: 1rem;
        }
        .Content h1 {
            font-size: 2rem;
            margin-bottom: 1rem;
            color: #6B240C;
        }
        .Content p {
            font-size: 1.8rem;
            color: #22092C;
            margin-bottom: 2rem;
        }
        .button {
            font-size: 2rem;
            display: inline-block;
            background-color: #f9f3e7;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            padding: 0.8rem 3rem;
            top: -6rem;
        }
        .contenar {
            font-size: 1.5rem;
            height: 50rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            background-color: #f9f3e7;
            margin: 2rem;
        }
        .border {
            width: 60rem;
            text-align: center;
            border: 1px;
        }
        .text-light {
            color: #2b2624;
            line-height: 10rem;
            font-size: 4.5rem;
            direction: rtl;
            text-align: right;
        }
        .row {
            display: flex;
            flex-wrap: wrap;
        }
        ul {
            list-style: none;
        }
        .rectangle {
            max-width: 93%;
            height: 35rem;
            background-color: #EEE9DF;
            border-radius: 3rem;
            margin: 10rem auto 2rem;
            position: relative;
            overflow: hidden;
        }
        .rectangle img {
            width: 29rem;
            height: 35rem;
            margin: 0rem 29rem;
            text-align: center;
            object-fit: cover;
            clip-path: ellipse(60% 90% at 50% 30%);
        }
        .rectangle .line-img {
            position: absolute;
            bottom: -7rem;
            right: 25rem;
            width: 15rem;
            height: auto;
        }
        .rectangle .line-img-top-left {
            position: absolute;
            top: -6rem;
            right: -12rem;
            width: 15rem;
            height: auto;
            transform: rotate(1deg);
            transform-origin: center;
        }
        .input-container {
            position: relative;
            display: flex;
            justify-content: right;
            color: #492c14;
        }
        .input-container input {
            width: 90%;
            padding: 1rem 1rem 1rem 4rem;
            font-size: 1rem;
            border: 1px solid #ffffff;
            border-radius: 0.5rem;
        }
        .input-container .send-icon {
            position: absolute;
            top: 50%;
            right: 25rem;
            transform: translateY(-50%);
            width: 2.5rem;
            height: 2.5rem;
            color: #ffffff;
            cursor: pointer;
            transition: 0.3s;
        }
        .input-container .send-icon:hover {
            transform: translateY(-50%) scale(1.1);
        }
        .swiper-slide a {
            text-decoration: none;
            color: inherit;
        }
        .swiper-slide {
            border:black;
            position: relative;
            padding: 0.5rem;
            overflow: hidden;
        }
        .swiper-slide img {
            width: 20rem;
            height: 20rem;
            object-fit: cover;
            border-radius: 1rem;
            box-shadow: 0 0.4rem 0.6rem rgba(0, 0, 0, 0.1);
            transition: transform 0.75s ease;
        }
        .swiper-slide::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(0deg, rgba(0,0,0,0.7), rgba(0,0,0,0));
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .swiper-slide:hover::after {
            opacity: 1;
        }
        .swiper-slide h1 {
            position: relative;
            bottom: 3rem;
            left: 0.2rem;
            color:rgb(255, 255, 255);
            z-index: 2;
        }
        .swiper-slide h3 {
            position: relative;
            bottom: 2rem;
            left: 0.2rem;
            color: #fff;
            z-index: 2;
        }
        .rectangle .browse-button {
            display: inline-block;
            text-decoration: none;
            text-align: center;
            font-family: 'TheYearOfTheCamel';
            width: 21.5rem;
            padding: 1.5rem;
            background-color: #725C3A;
            color: #EEE9DF;
            border: none;
            border-radius: 17rem;
            cursor: pointer;
            font-size: 3rem;
            margin-top: 2rem;
            position: absolute;
            bottom: 5.9rem;
            left: 30.5%;
            transform: translateX(-50%);
            transition: background-color 0.3s ease;
            font-weight: 670;
            box-shadow: 0px 1.2rem 0.8rem rgba(0, 0, 0, 0.2);
        }
        .rectangle .browse-button:hover {
            background-color: #5E4C2A;
        }
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(5rem);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        h1 {
            animation: fadeInUp 1.5s ease-in-out;
        }
        @keyframes floating {
            0% { transform: translateY(0); }
            50% { transform: translateY(-1rem); }
            100% { transform: translateY(0); }
        }
        .line-img, .line-img-top-left {
            animation: floating 3s ease-in-out infinite;
        }
        .footer .row {
    background-color: #492c14;
    padding: 60px 10px;
    display: flex; 
    justify-content: space-between; 
    flex-wrap: wrap; 
}
.footer-col {
    
    width: 22%; 
    padding: 10px; 
    margin:  10px 0;
}
    .footer-col h4{
        font-size: 25px;
        color: #ffffff;
        text-transform: capitalize;
        margin-bottom: 40px;
        font-weight: 500;
        position: relative;
    }
    .footer-col ul li:not(:last-child){
        margin-bottom: 10px;
    }
    .footer-col ul li a{
        font-size: 16px;
        color: #ffffff;
        text-decoration:none;
        font-weight: 300;
        color: #bbbbbb;
        display: block;
        transition: all 0.3s ease;
    }
    .footer-col ul li a:hover{
        margin-left: 10px;
        color: #ffffff;
        padding-left: 8px;
        background: rgba(241, 212, 192, 0.187);
    }
         .aboutUs {
            background-color: #e6e2d3;
            border-radius: 30px;
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
           height:500px ;
            width: 100%;
        }

        .aboutUs h1 {
    color: rgb(107, 86, 54);
    font-size: 4.5rem;
    margin: 0;
    font-weight: bold;
    line-height: 2.2;
    opacity: 0; 
    transform: translateY(50px); 
    animation: slideUp 1.2s ease-out forwards;
}

/* تعريف الحركة */
@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(50px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

        .aboutUs h3 {
            color: #7b612b;
            font-size: 2.8rem;
            margin-top: 10px;
            line-height: 0.9;
        }


        .aboutUs button {
    font-family: 'TheYearOfTheCamel';
    width: 190px;
    padding: 15px;
    background-color: #725C3A;
    color: #EEE9DF;
    border: none;
    border-radius: 140px;
    cursor: pointer;
    font-size: 30px;
    margin-top: 45px;
    margin-left: 0px;
    transition: background-color 0.3s ease;  
    font-weight: 780;
    box-shadow: 0px 12px 8px rgba(0, 0, 0, 0.2);
}
.aboutUs button:hover {
    background-color: #5E4C2A;  
}

        .About{
        text-align: center;
        margin-left: -120px;
        margin-right: 190px;
    }

/* تحسين العرض على الجوال */
@media (max-width: 768px) {
    .about-container {
        flex-direction: column;
        text-align: center;
    }

    .about-text, .about-image {
        min-width: 100%;
    }
}
/* تحسين العرض على الجوال */
@media (max-width: 768px) {
    .footer {
        flex-direction: column;
        text-align: center;
        font-size: 8px;

    }

    .footer-column {
        min-width: 100%;
        font-size: 6px;

    }
    .footer-col ul li a{
        font-size: 12px;
        color: #ffffff;
        text-decoration:none;
        font-weight: 300;
        color: #bbbbbb;
        display: block;
        transition: all 0.3s ease;
    }
}
@media (max-width: 768px) {
    .rectangle {
        width: 100%; 
        height: auto;  
        margin: 1rem auto;
        padding: 1rem; 
    }

    .rectangle img {
        width: 100%; 
        height: auto; 
        margin: 0;  
        clip-path: none; 
    }

    .rectangle h1 {
        font-size: 3rem;  
        text-align: center;
        margin-top: 1rem; 

    .rectangle .browse-button {
        font-size: 2rem; 
        padding: 1rem 3rem;
        margin-top: 1rem; 
        
    }

    .rectangle .line-img, .rectangle .line-img-top-left {
        width: 10rem;  
        height: auto; 
    }
}}
@media screen and (max-width: 768px) {
    .aboutUs h1 {
        font-size: 2rem;
    }

    .aboutUs h3 {
        font-size: 1rem;
        line-height: 1.5;
    }

    .aboutUs button {
        font-size: 1rem;
        padding: 8px 20px;
    }
}

@media screen and (max-width: 480px) {
    .aboutUs {
        padding: 30px 10px;
    }

    .aboutUs h1 {
        font-size: 2.3rem;
    }

    .aboutUs h3 {
        font-size: 1.5rem;
    }

    .aboutUs button {
        font-size: 1.5rem;
        padding: 7px 15px;
    }
}

    </style>
</head>
<body>
      <div class="rectangle">
        <img src="images/girl.jpg" alt="nothing" width="300px"> 
        <h1 style="font-size: 5rem; color: #725C3A; text-align: center; margin-top: -310px; margin-right: 540px; line-height: 1.6;">
            مِــن أيــدي مُـبـدعـينـــا <br> إلـى قـلـوبـكـم
        </h1>
        <img src="images/line2.png" alt="Line Image" class="line-img">
        <img src="images/line3.png" alt="Line Image Top Left" class="line-img-top-left">
        <a href="product.php" class="browse-button">تصفح منتجاتنا</a>
    </div>
            <h1 id="review" class="text-light" style="color:#224F34; margin-right:92px; bottom:40px;">الأكـثـر مبيعًــا</h1> 
   
    <div class="swiper ">
        <div class="swiper-wrapper">
            <?php foreach ($products as $product): ?>
                <div class="swiper-slide">
                    <a href="product-details.php?id=<?php echo $product['ProductID']; ?>">
                        <img src="<?= $product['ProductImage']; ?>" alt="<?= htmlspecialchars($product['ProductName']); ?>">                   
                        <h1><?= $product['ProductName'] ?></h1>
                        <h3><?= $product['Price'] ?> ريال</h3>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="swiper-button-prev"></div>
        <div class="swiper-button-next"></div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script>
const swiper = new Swiper('.swiper', {
    loop: true,
    autoplay: {
        delay: 1000,
        disableOnInteraction: false
    },
    navigation: {
        nextEl: '.swiper-button-next',
        prevEl: '.swiper-button-prev',
    },
    pagination: {
        el: '.swiper-pagination',
        clickable: true,
    },
    slidesPerView: 3,  
    spaceBetween: 3, 
    breakpoints: {
        768: {
            slidesPerView: 7, 
        },
    },
});

    </script>
  <div class="aboutUs" id="aboutUs">
    
    <h1>مــن نــحــن ؟</h1>
    <h3>” نـحـن في جِسـر نسعى لبناء جسـر يصل بين الحرفييـن والمشتريـن،<br><br>عبـر منصـة شاملـة تعرض إبداعاتهـم اليدويـة لنساهـم في تنميـة أعمـالهم وتوسيـع آفاقهـم. “</h3>
        <form action="Register.php">
            <button type="submit" 
        >انضــم إلينــا</button>
        </form>
</div>

    <footer id="footer" class="footer">

    <div class="container">
            <div class="row">
                <div class="footer-col">
                    <h4>جِسر</h4>
                    <ul>
                        <li><a href="#">                  احصل على خصم 10% لطلبك الاول<!DOCTYPE html> <br>باستخدام الكود     " WELCOMEJISR  "    
                        </a></li>
                    </ul>
                </div>
 <div class="footer-col">
    <h4>وصول سريع</h4>
    <ul>
        <li><a href="#">الرئيسة</a></li>
        <li><a href="product.php">المنتجات</a></li>
        <li><a href="#aboutUs">من نحن</a></li>
        <li><a href="Register.php">انضم إلينا</a></li>
    </ul>
</div>
                <div class="footer-col">
                    <h4>الدليل والإرشادات </h4>
                    <ul>
    <li><a href="terms-and-conditions.php#terms-conditions">الشروط والأحكام</a></li>
    <li><a href="terms-and-conditions.php#faq">الأسئلة الشائعة</a></li>
</ul>
                </div>
                <div class="footer-col">
                    <h4>تواصل معنا</h4>
                    <ul>
                        <li><a href="#"> <i class="fas fa-phone"></i>   05000000000</a></li> 
                        <li><a href="mailto:jiiiiisr@gmail.com"><i class="fas fa-envelope"></i>    jiiiiisr@gmail.com
                        </a></li> 
                        <li><a href="#"> <i class="fa-solid fa-location-dot"></i>◀   القنفذة </a></li> 
                    </ul>
                </div>
            </div>
        </div>    </footer>
</body>
</html>
