<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الأسئلة الشائعة والشروط والأحكام</title>
    <style>
        @font-face {
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

        body {
            direction: rtl;
            background-color: #fdf9f0;
            font-family: 'TheYearOfTheCamel';
        }

        .container {
            max-width: 900px;
            margin: 40px auto;
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            direction: rtl;
            border-radius: 12px;
        }

        h2 {
            color: #224F34;
            margin-bottom: 25px;
            font-size: 32px;
            text-align: center;
        }

        h3 {
            color: #2D3B2F;
            font-size: 22px;
            margin-top: 20px;
            margin-bottom: 10px;
            border-bottom: 2px solid #E1E1E1;
            padding-bottom: 10px;
        }

        p {
            font-size: 18px;
            line-height: 1.6;
            color: #333;
        }

        .faq-item {
            margin-bottom: 20px;
        }

        .faq-question {
            font-size: 20px;
            color: #3E4A39;
        }

        .faq-answer {
            font-size: 18px;
            color: #555;
        }

        .terms-conditions {
            margin-top: 30px;
            font-size: 18px;
            color: #333;
        }

        .button {
            background: #725C3A;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 12px;
            display: block;
            margin: 30px auto 0;
            font-size: 20px;
            width: fit-content;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .button:hover {
            background-color: #3E4A39;
        }
    </style>
</head>
<body>

    <div class="container">
        <header>
            <h2>الأسئلة الشائعة والشروط والأحكام</h2>
        </header>
        <section class="faq">
            <h3>الأسئلة الشائعة</h3>
            <div class="faq-item">
                <p class="faq-question">ما هي طرق الدفع المتاحة؟</p>
                <p class="faq-answer">نحن نقدم مجموعة  من طرق الدفع مثل الدفع عند الاستلام، البطاقات الائتمانية.</p>
            </div>
            <div class="faq-item">
                <p class="faq-question">هل يمكنني إلغاء الطلب؟</p>
                <p class="faq-answer">نعم، يمكنك إلغاء طلبك خلال 24 ساعة من تقديمه. بعد ذلك، قد يختلف الوضع حسب حالة الطلب.</p>
            </div>
            
        </section>
        <section class="terms-conditions">
            <h3>الشروط والأحكام</h3>
            <p>1. جميع الأسعار المعروضة على الموقع هي أسعار شاملة للضرائب.</p>
            <p>2. يجب على العملاء تقديم معلومات دقيقة وكاملة أثناء التسجيل أو تقديم الطلبات.</p>
            <p>3. نحن نحتفظ بالحق في تعديل الأسعار والعروض في أي وقت دون إشعار مسبق.</p>
            <p>4. نحن نلتزم بحماية بيانات عملائنا ونتبع أفضل الممارسات في الأمان الإلكتروني.</p>
            <p>5. يحق لنا رفض أو إلغاء أي طلبات في حال اكتشافنا أن المعلومات المقدمة غير دقيقة أو مخالفة للسياسات.</p>
        </section>
        <button class="button" onclick="window.location.href='homepage.php'">العودة إلى الصفحة الرئيسية</button>
    </div>

</body>
</html>
