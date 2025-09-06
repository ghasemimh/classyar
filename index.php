<?php
define('CLASSYAR_APP', true);
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/app/models/db.php';
require_once __DIR__ . '/app/controllers/auth.php';

Auth::auth();

// فعال کردن گزارش خطاهای PHP
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);



require_once __DIR__ . '/app/core/router.php';



// Router::post('product/update/{id}/{subid}', 'ProductController@update');
// می‌تونی مسیرهای دیگه هم اضافه کنی

// اجرای روتر
Router::dispatch();













?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>نمونه قالب – پیاده‌سازی از XD با Tailwind</title>
  <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;600;800&display=swap" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    html, body { 
        font-family: "Vazirmatn", system-ui, -apple-system, Segoe UI, Roboto, "Helvetica Neue", Arial, "Noto Sans", "Liberation Sans", "Apple Color Emoji","Segoe UI Emoji"; 
    }
    /* HTML: <div class="loader"></div> */
    .loader {
        width: 80px;
        aspect-ratio: 1;
        color: #8d7958;
        background:
            radial-gradient(150% 150% at left -40% top -40%,#0000 98%,currentColor) left top,
            radial-gradient(150% 150% at right -40% top -40%,#0000 98%,currentColor) right top,
            radial-gradient(150% 150% at left -40% bottom -40%,#0000 98%,currentColor) left bottom,
            radial-gradient(150% 150% at right -40% bottom -40%,#0000 98%,currentColor) right bottom;
        background-size: 50.3% 50.3%;
        background-repeat: no-repeat;
        -webkit-mask: radial-gradient(circle 5px,#0000 90%,#000);
        animation: l7 1.5s infinite linear;
    }
    @keyframes l7 { 
        100%{transform: rotate(1turn)}
    }

    /* استایل اولیه لودر در مرکز صفحه */
    .loader-container {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
        background-color: #fff; /* پس زمینه سفید برای پوشاندن محتوا */
        z-index: 9999;
        /* این خط اضافه شده */
        transition: opacity 0.5s ease-in-out;
    }

    /* کلاس برای محو شدن لودر */
/* تغییر این بخش */
    .loader-container.fade-out {
    opacity: 0;
    transition: opacity 0.5s ease-in-out;
    /* visibility اینجا حذف می‌شه */
    /* visibility: hidden; */
    }

  </style>
</head>
<body class="bg-gray-50 text-gray-800">
  <div id="loader-wrapper" class="loader-container">
    <div class="loader"></div>
  </div>

  <header class="sticky top-0 z-50 bg-white/80 backdrop-blur border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
      <a href="#" class="font-extrabold text-xl">لوگوی سایت</a>
      <nav class="hidden md:flex items-center gap-6">
        <a class="hover:text-gray-900 text-gray-600" href="#">خانه</a>
        <a class="hover:text-gray-900 text-gray-600" href="#">درباره</a>
        <a class="hover:text-gray-900 text-gray-600" href="#">خدمات</a>
        <a class="hover:text-gray-900 text-white bg-gray-900 rounded-xl px-4 py-2" href="#">تماس</a>
      </nav>
      <button class="md:hidden inline-flex items-center justify-center rounded-xl border px-3 py-2">منو</button>
    </div>
  </header>
  
  <section class="relative">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-24 grid lg:grid-cols-2 gap-10 items-center">
      <div>
        <h1 class="text-3xl md:text-5xl font-extrabold leading-tight">
          تیتر صفحه از XD
        </h1>
        <p class="mt-4 text-gray-600 leading-8">
          اینجا توضیحات کوتاه صفحه می‌آید. من دقیقاً طبق تایپوگرافی و فاصله‌گذاری‌های طرح تو اجرا می‌کنم.
        </p>
        <div class="mt-6 flex items-center gap-3">
          <a href="#" class="px-5 py-3 rounded-2xl bg-gray-900 text-white hover:opacity-90">دکمه اصلی</a>
          <a href="#" class="px-5 py-3 rounded-2xl border border-gray-300 hover:bg-gray-100">دکمه ثانویه</a>
        </div>
      </div>
      <div class="aspect-[16/10] rounded-3xl bg-gray-200"></div>
    </div>
  </section>
  
  <section class="py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-end justify-between mb-6">
        <h2 class="text-2xl font-bold">بخش کارت‌ها (۴تایی)</h2>
        <a href="#" class="text-sm text-gray-500 hover:text-gray-700">مشاهده همه</a>
      </div>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <article class="bg-white rounded-2xl shadow-sm border p-4 hover:shadow-md transition">
          <div class="aspect-[4/3] bg-gray-100 rounded-xl mb-3"></div>
          <h3 class="font-semibold">عنوان کارت</h3>
          <p class="text-sm text-gray-600 mt-1">توضیح کوتاه کارت…</p>
          <a href="#" class="mt-3 inline-block text-sm font-medium underline">ادامه</a>
        </article>
  
        <article class="bg-white rounded-2xl shadow-sm border p-4 hover:shadow-md transition">
          <div class="aspect-[4/3] bg-gray-100 rounded-xl mb-3"></div>
          <h3 class="font-semibold">عنوان کارت</h3>
          <p class="text-sm text-gray-600 mt-1">توضیح کوتاه کارت…</p>
          <a href="#" class="mt-3 inline-block text-sm font-medium underline">ادامه</a>
        </article>
  
        <article class="bg-white rounded-2xl shadow-sm border p-4 hover:shadow-md transition">
          <div class="aspect-[4/3] bg-gray-100 rounded-xl mb-3"></div>
          <h3 class="font-semibold">عنوان کارت</h3>
          <p class="text-sm text-gray-600 mt-1">توضیح کوتاه کارت…</p>
          <a href="#" class="mt-3 inline-block text-sm font-medium underline">ادامه</a>
        </article>
  
        <article class="bg-white rounded-2xl shadow-sm border p-4 hover:shadow-md transition">
          <div class="aspect-[4/3] bg-gray-100 rounded-xl mb-3"></div>
          <h3 class="font-semibold">عنوان کارت</h3>
          <p class="text-sm text-gray-600 mt-1">توضیح کوتاه کارت…</p>
          <a href="#" class="mt-3 inline-block text-sm font-medium underline">ادامه</a>
        </article>
      </div>
    </div>
  </section>
  
  <footer class="border-t border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 text-sm text-gray-500">
      © ۲۰۲۵ نام برند. همه حقوق محفوظ است.
    </div>
  </footer>
</body>
</html>

<script>
  document.addEventListener("DOMContentLoaded", function() {
  const loaderWrapper = document.getElementById("loader-wrapper");
  setTimeout(function() {
    loaderWrapper.classList.add("fade-out");
    // بعد از نیم ثانیه (زمان ترنزیشن) visibility رو hidden کن
    setTimeout(() => {
      loaderWrapper.style.visibility = "hidden";
    }, 500); 
  }, 1000); 
});
</script>