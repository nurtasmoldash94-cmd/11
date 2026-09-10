<?php

/*
============================================================
 DREAMHOUSE
 Modern Architecture & Luxury Homes
============================================================

 Бұл файлдың ішінде:
 - PHP
 - HTML
 - CSS
 - JavaScript

 бәрі бірге орналасқан.

 Негізгі мүмкіндіктер:
 1. Home
 2. About
 3. Services
 4. House Catalog
 5. House Details
 6. Search
 7. Contact Form
 8. Form Validation
 9. Responsive Design
 10. 404 Page
 11. Modal
 12. Slider
 13. Dropdown
 14. Accordion
 15. PHP + MySQL
============================================================
*/


/* =========================================================
   DATABASE CONNECTION
========================================================= */

$host = "localhost";
$user = "root";
$password = "";
$database = "dreamhouse";

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Database connection error: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");


/* =========================================================
   HELPER FUNCTION
========================================================= */

function clean($value)
{
    return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
}


/* =========================================================
   CONTACT FORM
========================================================= */

$messageStatus = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $full_name = clean($_POST["full_name"] ?? "");
    $email = clean($_POST["email"] ?? "");
    $phone = clean($_POST["phone"] ?? "");
    $message = clean($_POST["message"] ?? "");

    if (
        empty($full_name) ||
        empty($email) ||
        empty($phone) ||
        empty($message)
    ) {

        $messageStatus = "error";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $messageStatus = "email_error";

    } else {

        $stmt = $conn->prepare(
            "INSERT INTO requests
            (full_name, email, phone, message)
            VALUES (?, ?, ?, ?)"
        );

        $stmt->bind_param(
            "ssss",
            $full_name,
            $email,
            $phone,
            $message
        );

        if ($stmt->execute()) {
            $messageStatus = "success";
        } else {
            $messageStatus = "error";
        }

        $stmt->close();
    }
}


/* =========================================================
   SEARCH
========================================================= */

$search = clean($_GET["search"] ?? "");

if ($search !== "") {

    $stmt = $conn->prepare(
        "SELECT * FROM houses
         WHERE name LIKE ?
         OR category LIKE ?
         OR description LIKE ?
         ORDER BY id DESC"
    );

    $searchValue = "%" . $search . "%";

    $stmt->bind_param(
        "sss",
        $searchValue,
        $searchValue,
        $searchValue
    );

    $stmt->execute();

    $houses = $stmt->get_result();

    $stmt->close();

} else {

    $houses = $conn->query(
        "SELECT * FROM houses ORDER BY id DESC"
    );
}


/* =========================================================
   HOUSE DETAILS
========================================================= */

$houseId = intval($_GET["house"] ?? 0);

$selectedHouse = null;

if ($houseId > 0) {

    $stmt = $conn->prepare(
        "SELECT * FROM houses WHERE id = ?"
    );

    $stmt->bind_param("i", $houseId);

    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $selectedHouse = $result->fetch_assoc();
    }

    $stmt->close();
}


/* =========================================================
   404
========================================================= */

$page = $_GET["page"] ?? "home";

$is404 = ($page === "404");

?>

<!DOCTYPE html>

<html lang="kk">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>
DreamHouse | Modern Luxury Architecture
</title>


<!-- Google Font -->

<link rel="preconnect"
href="https://fonts.googleapis.com">

<link rel="preconnect"
href="https://fonts.gstatic.com"
crossorigin>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@500;600;700&display=swap"
rel="stylesheet">


<style>

/* =========================================================
   GLOBAL
========================================================= */

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

html {
    scroll-behavior: smooth;
}

body {

    font-family: "Inter", sans-serif;

    background:
        radial-gradient(
            circle at top right,
            rgba(201,166,92,.12),
            transparent 30%
        ),
        #080a09;

    color: #f5f1e8;

    line-height: 1.6;
}

a {
    color: inherit;
    text-decoration: none;
}

img {
    max-width: 100%;
    display: block;
}

.container {

    width: min(1200px, 92%);

    margin: auto;
}


/* =========================================================
   HEADER
========================================================= */

header {

    position: fixed;

    top: 0;
    left: 0;

    width: 100%;

    z-index: 999;

    backdrop-filter: blur(20px);

    background: rgba(8,10,9,.72);

    border-bottom:
        1px solid rgba(255,255,255,.08);
}

.navbar {

    height: 78px;

    display: flex;

    align-items: center;

    justify-content: space-between;
}

.logo {

    font-family: "Playfair Display";

    font-size: 26px;

    font-weight: 700;

    letter-spacing: 1px;
}

.logo span {
    color: #c9a65c;
}

.nav-links {

    display: flex;

    gap: 30px;

    list-style: none;
}

.nav-links a {

    color: #aaa;

    transition: .3s;

    position: relative;
}

.nav-links a:hover {

    color: #c9a65c;
}

.nav-links a::after {

    content: "";

    position: absolute;

    width: 0;

    height: 2px;

    background: #c9a65c;

    bottom: -7px;

    left: 0;

    transition: .3s;
}

.nav-links a:hover::after {
    width: 100%;
}


/* =========================================================
   DROPDOWN
========================================================= */

.dropdown {
    position: relative;
}

.dropdown-menu {

    position: absolute;

    top: 35px;

    left: -20px;

    width: 190px;

    padding: 12px;

    background: #111512;

    border:
        1px solid rgba(201,166,92,.25);

    border-radius: 15px;

    opacity: 0;

    visibility: hidden;

    transform: translateY(10px);

    transition: .3s;
}

.dropdown:hover .dropdown-menu {

    opacity: 1;

    visibility: visible;

    transform: translateY(0);
}

.dropdown-menu a {

    display: block;

    padding: 10px;

    border-radius: 8px;
}

.dropdown-menu a:hover {
    background: rgba(201,166,92,.1);
}


/* =========================================================
   HERO
========================================================= */

.hero {

    min-height: 100vh;

    display: flex;

    align-items: center;

    position: relative;

    overflow: hidden;

    background:
        linear-gradient(
            90deg,
            rgba(0,0,0,.92),
            rgba(0,0,0,.3)
        ),
        url("https://images.unsplash.com/photo-1600607687920-4e2a09cf159d?auto=format&fit=crop&w=2000&q=90")
        center/cover;
}

.hero-content {

    max-width: 760px;

    padding-top: 80px;

    animation: heroIn 1.2s ease;
}

@keyframes heroIn {

    from {
        opacity: 0;
        transform: translateY(40px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.hero-label {

    color: #c9a65c;

    text-transform: uppercase;

    letter-spacing: 5px;

    font-size: 13px;

    margin-bottom: 20px;
}

.hero h1 {

    font-family: "Playfair Display";

    font-size: clamp(50px, 8vw, 100px);

    line-height: .95;

    margin-bottom: 30px;
}

.hero h1 span {
    color: #c9a65c;
}

.hero p {

    color: #ccc;

    max-width: 600px;

    font-size: 18px;

    margin-bottom: 35px;
}


/* =========================================================
   BUTTON
========================================================= */

.btn {

    display: inline-flex;

    align-items: center;

    justify-content: center;

    padding: 14px 26px;

    border-radius: 50px;

    border: 1px solid #c9a65c;

    background: #c9a65c;

    color: #0a0c0b;

    font-weight: 700;

    transition: .3s;

    cursor: pointer;
}

.btn:hover {

    transform: translateY(-4px);

    box-shadow:
        0 15px 40px rgba(201,166,92,.25);
}

.btn-outline {

    background: transparent;

    color: #fff;

    margin-left: 10px;
}


/* =========================================================
   SECTIONS
========================================================= */

section {

    padding: 110px 0;
}

.section-head {

    text-align: center;

    max-width: 700px;

    margin: 0 auto 55px;
}

.section-label {

    color: #c9a65c;

    letter-spacing: 4px;

    text-transform: uppercase;

    font-size: 12px;

    margin-bottom: 12px;
}

.section-head h2 {

    font-family: "Playfair Display";

    font-size: 50px;

    margin-bottom: 15px;
}

.section-head p {
    color: #aaa;
}


/* =========================================================
   ABOUT
========================================================= */

.about-grid {

    display: grid;

    grid-template-columns:
        1fr 1fr;

    gap: 70px;

    align-items: center;
}

.about-image {

    border-radius: 30px;

    overflow: hidden;

    position: relative;
}

.about-image img {

    width: 100%;

    height: 500px;

    object-fit: cover;

    transition: .7s;
}

.about-image:hover img {

    transform: scale(1.07);
}

.about-content h3 {

    font-family: "Playfair Display";

    font-size: 40px;

    margin-bottom: 20px;
}

.about-content p {

    color: #aaa;

    margin-bottom: 25px;
}

.stats {

    display: grid;

    grid-template-columns:
        repeat(3,1fr);

    gap: 15px;
}

.stat {

    padding: 20px;

    border:
        1px solid rgba(255,255,255,.08);

    border-radius: 18px;

    background: rgba(255,255,255,.03);
}

.stat strong {

    font-size: 28px;

    color: #c9a65c;

    display: block;
}


/* =========================================================
   SERVICES
========================================================= */

.services-grid {

    display: grid;

    grid-template-columns:
        repeat(4,1fr);

    gap: 20px;
}

.service {

    padding: 35px 25px;

    border-radius: 25px;

    background:
        linear-gradient(
            145deg,
            rgba(255,255,255,.06),
            rgba(255,255,255,.02)
        );

    border:
        1px solid rgba(255,255,255,.08);

    transition: .4s;
}

.service:hover {

    transform:
        translateY(-10px);

    border-color:
        rgba(201,166,92,.5);
}

.service-icon {

    font-size: 42px;

    margin-bottom: 20px;
}

.service h3 {
    margin-bottom: 10px;
}

.service p {
    color: #999;
}


/* =========================================================
   CATALOG
========================================================= */

.catalog {

    background: #0b0e0c;
}

.search-box {

    max-width: 650px;

    margin: 0 auto 50px;

    display: flex;

    gap: 10px;
}

.search-box input {

    flex: 1;

    padding: 17px 22px;

    border-radius: 50px;

    border:
        1px solid rgba(255,255,255,.1);

    background: #151915;

    color: white;

    outline: none;
}

.search-box input:focus {

    border-color: #c9a65c;
}

.house-grid {

    display: grid;

    grid-template-columns:
        repeat(3,1fr);

    gap: 25px;
}

.house-card {

    overflow: hidden;

    border-radius: 25px;

    background: #121512;

    border:
        1px solid rgba(255,255,255,.08);

    transition: .4s;
}

.house-card:hover {

    transform: translateY(-8px);

    box-shadow:
        0 20px 60px rgba(0,0,0,.5);
}

.house-image {

    height: 250px;

    overflow: hidden;

    position: relative;
}

.house-image img {

    width: 100%;

    height: 100%;

    object-fit: cover;

    transition: .6s;
}

.house-card:hover .house-image img {

    transform: scale(1.1);
}

.badge {

    position: absolute;

    top: 15px;

    left: 15px;

    background: #c9a65c;

    color: #111;

    padding: 7px 13px;

    border-radius: 50px;

    font-size: 11px;

    font-weight: 700;
}

.house-info {

    padding: 25px;
}

.house-info h3 {

    font-family:
        "Playfair Display";

    font-size: 26px;

    margin-bottom: 10px;
}

.house-info p {

    color: #999;

    font-size: 14px;

    margin-bottom: 20px;
}

.house-meta {

    display: flex;

    justify-content: space-between;

    color: #aaa;

    margin-bottom: 20px;

    font-size: 13px;
}

.price {

    color: #c9a65c;

    font-size: 22px;

    font-weight: 700;

    margin-bottom: 20px;
}


/* =========================================================
   DETAIL PAGE
========================================================= */

.detail {

    padding-top: 160px;

    min-height: 100vh;
}

.detail-grid {

    display: grid;

    grid-template-columns:
        1.2fr .8fr;

    gap: 50px;

    align-items: center;
}

.detail-image {

    border-radius: 30px;

    overflow: hidden;
}

.detail-image img {

    width: 100%;

    height: 600px;

    object-fit: cover;
}

.detail-content h1 {

    font-family:
        "Playfair Display";

    font-size: 60px;

    margin-bottom: 20px;
}

.detail-content p {

    color: #aaa;

    margin-bottom: 25px;
}

.detail-features {

    display: grid;

    grid-template-columns:
        repeat(2,1fr);

    gap: 15px;

    margin-bottom: 30px;
}

.detail-feature {

    padding: 18px;

    border-radius: 15px;

    background: rgba(255,255,255,.04);
}


/* =========================================================
   SLIDER
========================================================= */

.slider {

    overflow: hidden;

    border-radius: 30px;

    position: relative;
}

.slides {

    display: flex;

    transition: .6s;
}

.slide {

    min-width: 100%;

    height: 500px;
}

.slide img {

    width: 100%;

    height: 100%;

    object-fit: cover;
}

.slider-buttons {

    position: absolute;

    bottom: 20px;

    right: 20px;

    display: flex;

    gap: 10px;
}

.slider-buttons button {

    width: 45px;

    height: 45px;

    border-radius: 50%;

    border: none;

    cursor: pointer;

    background: #c9a65c;

    font-size: 20px;
}


/* =========================================================
   ACCORDION
========================================================= */

.accordion {

    max-width: 800px;

    margin: auto;
}

.accordion-item {

    border-bottom:
        1px solid rgba(255,255,255,.1);
}

.accordion-title {

    padding: 22px 0;

    cursor: pointer;

    display: flex;

    justify-content: space-between;

    font-size: 18px;
}

.accordion-content {

    max-height: 0;

    overflow: hidden;

    transition: .4s;

    color: #999;
}

.accordion-content p {
    padding-bottom: 20px;
}

.accordion-item.active
.accordion-content {

    max-height: 150px;
}


/* =========================================================
   CONTACT
========================================================= */

.contact-grid {

    display: grid;

    grid-template-columns:
        .8fr 1.2fr;

    gap: 60px;
}

.contact-info h3 {

    font-family:
        "Playfair Display";

    font-size: 42px;

    margin-bottom: 20px;
}

.contact-info p {
    color: #999;
}

.contact-card {

    padding: 20px 0;

    border-bottom:
        1px solid rgba(255,255,255,.08);
}

.form {

    display: grid;

    gap: 15px;
}

.form input,
.form textarea {

    width: 100%;

    padding: 17px 20px;

    border-radius: 14px;

    background: #121512;

    border:
        1px solid rgba(255,255,255,.1);

    color: white;

    outline: none;
}

.form textarea {

    min-height: 150px;

    resize: vertical;
}

.form input:focus,
.form textarea:focus {

    border-color: #c9a65c;
}


/* =========================================================
   ALERT
========================================================= */

.alert {

    padding: 15px;

    margin-bottom: 20px;

    border-radius: 12px;

    text-align: center;
}

.alert-success {

    background: rgba(0,200,100,.12);

    color: #6cffad;
}

.alert-error {

    background: rgba(255,50,50,.12);

    color: #ff8585;
}


/* =========================================================
   MODAL
========================================================= */

.modal {

    position: fixed;

    inset: 0;

    background: rgba(0,0,0,.8);

    display: none;

    align-items: center;

    justify-content: center;

    z-index: 2000;

    padding: 20px;
}

.modal.active {
    display: flex;
}

.modal-box {

    width: min(500px,100%);

    background: #121512;

    padding: 40px;

    border-radius: 25px;

    border:
        1px solid rgba(201,166,92,.3);

    position: relative;
}

.modal-close {

    position: absolute;

    right: 20px;

    top: 15px;

    background: none;

    border: none;

    color: white;

    font-size: 25px;

    cursor: pointer;
}


/* =========================================================
   404
========================================================= */

.error-page {

    min-height: 100vh;

    display: flex;

    justify-content: center;

    align-items: center;

    text-align: center;
}

.error-page h1 {

    font-size: 150px;

    font-family:
        "Playfair Display";

    color: #c9a65c;
}

.error-page p {

    color: #aaa;

    margin-bottom: 30px;
}


/* =========================================================
   FOOTER
========================================================= */

footer {

    padding: 70px 0 30px;

    border-top:
        1px solid rgba(255,255,255,.08);

    background: #060806;
}

.footer-grid {

    display: grid;

    grid-template-columns:
        2fr 1fr 1fr 1fr;

    gap: 40px;

    margin-bottom: 50px;
}

.footer-logo {

    font-family:
        "Playfair Display";

    font-size: 30px;

    margin-bottom: 15px;
}

.footer-grid p,
.footer-grid a {

    color: #888;

    display: block;

    margin-bottom: 9px;
}

.footer-grid a:hover {
    color: #c9a65c;
}

.copyright {

    padding-top: 25px;

    border-top:
        1px solid rgba(255,255,255,.07);

    color: #666;

    text-align: center;

    font-size: 13px;
}


/* =========================================================
   RESPONSIVE
========================================================= */

@media(max-width: 950px) {

    .nav-links {
        display: none;
    }

    .services-grid {

        grid-template-columns:
            repeat(2,1fr);
    }

    .house-grid {

        grid-template-columns:
            repeat(2,1fr);
    }

    .about-grid,
    .detail-grid,
    .contact-grid {

        grid-template-columns: 1fr;
    }

    .footer-grid {

        grid-template-columns:
            repeat(2,1fr);
    }
}


@media(max-width: 600px) {

    section {
        padding: 75px 0;
    }

    .hero h1 {
        font-size: 55px;
    }

    .hero p {
        font-size: 15px;
    }

    .btn {
        width: 100%;

        margin:
            5px 0;
    }

    .btn-outline {
        margin-left: 0;
    }

    .services-grid,
    .house-grid,
    .stats {

        grid-template-columns: 1fr;
    }

    .footer-grid {

        grid-template-columns: 1fr;
    }

    .detail-content h1 {
        font-size: 42px;
    }

    .detail-image img {
        height: 400px;
    }

    .error-page h1 {
        font-size: 90px;
    }
}

</style>

</head>


<body>


<?php if ($is404): ?>

<!-- =======================================================
     404 PAGE
======================================================= -->

<div class="error-page">

    <div>

        <div class="section-label">
            DREAMHOUSE
        </div>

        <h1>404</h1>

        <p>
            Бұл бет табылмады.
            Бірақ сіздің арманыңыздағы үйді табуға көмектесе аламыз.
        </p>

        <a href="index.php"
           class="btn">
            ← Home бетіне қайту
        </a>

    </div>

</div>


<?php elseif ($selectedHouse): ?>

<!-- =======================================================
     HOUSE DETAIL
======================================================= -->

<header>

<div class="container navbar">

    <a href="index.php"
       class="logo">
        DREAM<span>HOUSE</span>
    </a>

    <ul class="nav-links">

        <li>
            <a href="index.php#home">
                Home
            </a>
        </li>

        <li>
            <a href="index.php#about">
                About
            </a>
        </li>

        <li class="dropdown">

            <a href="#">
                Explore ▾
            </a>

            <div class="dropdown-menu">

                <a href="index.php#services">
                    Services
                </a>

                <a href="index.php#catalog">
                    Catalog
                </a>

                <a href="index.php#faq">
                    FAQ
                </a>

            </div>

        </li>

        <li>
            <a href="index.php#contact">
                Contact
            </a>
        </li>

    </ul>

</div>

</header>


<main class="detail">

<div class="container">

    <div class="detail-grid">

        <div class="detail-image">

            <img
                src="<?= clean($selectedHouse['image']) ?>"
                alt="<?= clean($selectedHouse['name']) ?>"
            >

        </div>


        <div class="detail-content">

            <div class="section-label">
                <?= clean($selectedHouse['category']) ?>
            </div>

            <h1>
                <?= clean($selectedHouse['name']) ?>
            </h1>

            <p>
                <?= clean($selectedHouse['description']) ?>
            </p>


            <div class="detail-features">

                <div class="detail-feature">
                    🏠 <?= $selectedHouse['area'] ?> м²
                </div>

                <div class="detail-feature">
                    🛏 <?= $selectedHouse['rooms'] ?> бөлме
                </div>

                <div class="detail-feature">
                    ✨ Premium дизайн
                </div>

                <div class="detail-feature">
                    ⚡ Smart Home
                </div>

            </div>


            <div class="price">

                <?= number_format(
                    $selectedHouse['price'],
                    0,
                    '.',
                    ' '
                ) ?> ₸

            </div>


            <a
                href="index.php#contact"
                class="btn"
            >
                Осы үйге тапсырыс беру
            </a>

            <a
                href="index.php#catalog"
                class="btn btn-outline"
            >
                ← Каталог
            </a>

        </div>

    </div>

</div>

</main>


<?php else: ?>

<!-- =======================================================
     NORMAL WEBSITE
======================================================= -->

<header>

<div class="container navbar">

    <a href="index.php"
       class="logo">
        DREAM<span>HOUSE</span>
    </a>


    <ul class="nav-links">

        <li>
            <a href="#home">
                Home
            </a>
        </li>

        <li>
            <a href="#about">
                About
            </a>
        </li>

        <li class="dropdown">

            <a href="#">
                Explore ▾
            </a>

            <div class="dropdown-menu">

                <a href="#services">
                    Services
                </a>

                <a href="#catalog">
                    Catalog
                </a>

                <a href="#faq">
                    FAQ
                </a>

            </div>

        </li>

        <li>
            <a href="#contact">
                Contact
            </a>
        </li>

    </ul>

</div>

</header>


<!-- =======================================================
     HERO
======================================================= -->

<section
    class="hero"
    id="home"
>

<div class="container">

<div class="hero-content">

    <div class="hero-label">
        Architecture of tomorrow
    </div>

    <h1>
        Your dream.
        <br>
        Our <span>architecture.</span>
    </h1>

    <p>
        Біз жай ғана үй салмаймыз.
        Біз сіздің өмір салтыңызға арналған
        ерекше кеңістік жасаймыз.
    </p>

    <a
        href="#catalog"
        class="btn"
    >
        Армандаған үйімді табу →
    </a>

    <a
        href="#contact"
        class="btn btn-outline"
    >
        Кеңес алу
    </a>

</div>

</div>

</section>


<!-- =======================================================
     ABOUT
======================================================= -->

<section id="about">

<div class="container">

<div class="section-head">

    <div class="section-label">
        Who we are
    </div>

    <h2>
        Үй емес.
        <br>
        Өмір салты.
    </h2>

    <p>
        DreamHouse — заманауи архитектура,
        интерьер және smart home технологияларын
        біріктіретін архитектуралық жоба.
    </p>

</div>


<div class="about-grid">

<div class="about-image">

    <img
        src="https://images.unsplash.com/photo-1600607688969-a5bfcd646154?auto=format&fit=crop&w=1200&q=80"
        alt="Modern house"
    >

</div>


<div class="about-content">

    <h3>
        Әр үйдің өз тарихы бар.
    </h3>

    <p>
        Біз клиенттің өмір салтын,
        қалауын және болашақ жоспарын зерттеп,
        соған сәйкес ерекше архитектура жасаймыз.
    </p>

    <p>
        Біздің мақсатымыз —
        әдемі көрінетін ғана емес,
        өмір сүруге ыңғайлы үй жасау.
    </p>


    <div class="stats">

        <div class="stat">
            <strong>12+</strong>
            Жыл тәжірибе
        </div>

        <div class="stat">
            <strong>350+</strong>
            Жоба
        </div>

        <div class="stat">
            <strong>18</strong>
            Архитектор
        </div>

    </div>

</div>

</div>

</div>

</section>


<!-- =======================================================
     SERVICES
======================================================= -->

<section id="services">

<div class="container">

<div class="section-head">

    <div class="section-label">
        What we do
    </div>

    <h2>
        Біздің қызметтер
    </h2>

    <p>
        Үй идеядан бастап дайын кілтке дейін.
    </p>

</div>


<div class="services-grid">


<div class="service">

    <div class="service-icon">
        
    </div>

    <h3>
        Architecture
    </h3>

    <p>
        Үйдің архитектуралық жобасын
        сіздің қажеттілігіңізге сай әзірлейміз.
    </p>

</div>


<div class="service">

    <div class="service-icon">
        
    </div>

    <h3>
        Interior
    </h3>

    <p>
        Интерьердің стилін,
        жиһазын және жарықтандыруын жобалаймыз.
    </p>

</div>


<div class="service">

    <div class="service-icon">
        
    </div>

    <h3>
        Landscape
    </h3>

    <p>
        Аула, бақ, бассейн және
        сыртқы кеңістікті толық жоспарлаймыз.
    </p>

</div>


<div class="service">

    <div class="service-icon">
        
    </div>

    <h3>
        Smart Home
    </h3>

    <p>
        Қауіпсіздік, жарық,
        климат және автоматтандыру жүйелерін орнатамыз.
    </p>

</div>


</div>

</div>

</section>


<!-- =======================================================
     CATALOG
======================================================= -->

<section
    class="catalog"
    id="catalog"
>

<div class="container">

<div class="section-head">

    <div class="section-label">
        Our collection
    </div>

    <h2>
        DreamHouse Collection
    </h2>

    <p>
        Өзіңізге ұнайтын архитектураны таңдаңыз.
    </p>

</div>


<!-- SEARCH -->

<form
    class="search-box"
    method="GET"
>

    <input
        type="text"
        name="search"
        placeholder="Үй атауын немесе категорияны іздеу..."
        value="<?= $search ?>"
    >

    <button
        type="submit"
        class="btn"
    >
         Іздеу
    </button>

</form>


<?php if ($search !== ""): ?>

<p style="
text-align:center;
color:#aaa;
margin-bottom:30px;
">

    «<?= $search ?>»
    бойынша іздеу нәтижелері

</p>

<?php endif; ?>


<div class="house-grid">


<?php if ($houses && $houses->num_rows > 0): ?>

<?php while ($house = $houses->fetch_assoc()): ?>


<div class="house-card">


<div class="house-image">

    <img
        src="<?= clean($house['image']) ?>"
        alt="<?= clean($house['name']) ?>"
    >

    <span class="badge">
        <?= clean($house['category']) ?>
    </span>

</div>


<div class="house-info">

    <h3>
        <?= clean($house['name']) ?>
    </h3>

    <p>
        <?= clean($house['description']) ?>
    </p>


    <div class="house-meta">

        <span>
             <?= $house['area'] ?> м²
        </span>

        <span>
             <?= $house['rooms'] ?> бөлме
        </span>

    </div>


    <div class="price">

        <?= number_format(
            $house['price'],
            0,
            '.',
            ' '
        ) ?> ₸

    </div>


    <a
        href="index.php?house=<?= $house['id'] ?>"
        class="btn"
    >
        Толық ақпарат →
    </a>

</div>

</div>


<?php endwhile; ?>

<?php else: ?>

<div style="
grid-column:1/-1;
text-align:center;
padding:80px 0;
">

    <h3>
         Үй табылмады
    </h3>

    <p style="color:#999">
        Басқа атау немесе категория енгізіп көріңіз.
    </p>

</div>

<?php endif; ?>


</div>

</div>

</section>


<!-- =======================================================
     SLIDER
======================================================= -->

<section>

<div class="container">

<div class="section-head">

    <div class="section-label">
        Inspiration
    </div>

    <h2>
        Architecture that speaks
    </h2>

</div>


<div class="slider">

<div class="slides" id="slides">

    <div class="slide">

        <img
            src="https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?auto=format&fit=crop&w=1600&q=80"
            alt="Villa"
        >

    </div>

    <div class="slide">

        <img
            src="https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=1600&q=80"
            alt="House"
        >

    </div>

    <div class="slide">

        <img
            src="https://images.unsplash.com/photo-1600047509807-ba8f99d2cdde?auto=format&fit=crop&w=1600&q=80"
            alt="Modern house"
        >

    </div>

</div>


<div class="slider-buttons">

    <button onclick="prevSlide()">
        ←
    </button>

    <button onclick="nextSlide()">
        →
    </button>

</div>

</div>

</div>

</section>


<!-- =======================================================
     FAQ / ACCORDION
======================================================= -->

<section id="faq">

<div class="container">

<div class="section-head">

    <div class="section-label">
        FAQ
    </div>

    <h2>
        Жиі қойылатын сұрақтар
    </h2>

</div>


<div class="accordion">


<div class="accordion-item">

    <div
        class="accordion-title"
        onclick="toggleAccordion(this)"
    >

        Үй салу қанша уақыт алады?

        <span>+</span>

    </div>

    <div class="accordion-content">

        <p>
            Жоба көлеміне байланысты орташа
            6–14 ай аралығында аяқталады.
        </p>

    </div>

</div>


<div class="accordion-item">

    <div
        class="accordion-title"
        onclick="toggleAccordion(this)"
    >

        Жобаға интерьер кіреді ме?

        <span>+</span>

    </div>

    <div class="accordion-content">

        <p>
            Иә. Біз архитектура,
            интерьер және landscape жобасын
            бір жүйе ретінде жасай аламыз.
        </p>

    </div>

</div>


<div class="accordion-item">

    <div
        class="accordion-title"
        onclick="toggleAccordion(this)"
    >

        Smart Home орнатуға бола ма?

        <span>+</span>

    </div>

    <div class="accordion-content">

        <p>
            Әрине. Жарық, климат,
            қауіпсіздік және басқа жүйелерді
            автоматтандыруға болады.
        </p>

    </div>

</div>


</div>

</div>

</section>


<!-- =======================================================
     CONTACT
======================================================= -->

<section id="contact">

<div class="container">

<div class="section-head">

    <div class="section-label">
        Contact
    </div>

    <h2>
        Үйіңізді бірге бастайық.
    </h2>

    <p>
        Бізге өз идеяңызды жіберіңіз.
    </p>

</div>


<div class="contact-grid">


<div class="contact-info">

    <h3>
        Let's build
        something beautiful.
    </h3>

    <p>
        Сұрақтарыңыз болса,
        бізге хабарлама қалдырыңыз.
        Архитектор сізбен байланысады.
    </p>


    <div class="contact-card">
         Алматы, Қазақстан
    </div>

    <div class="contact-card">
         +7 700 555 55 55
    </div>

    <div class="contact-card">
         hello@dreamhouse.kz
    </div>

    <div class="contact-card">
        Instagram · Telegram · WhatsApp
    </div>

</div>


<div>

<?php if ($messageStatus === "success"): ?>

<div class="alert alert-success">

     Өтініміңіз сәтті жіберілді!
    Біз сізбен жақын арада байланысамыз.

</div>

<?php elseif ($messageStatus === "error"): ?>

<div class="alert alert-error">

     Барлық өрісті толтырыңыз.

</div>

<?php elseif ($messageStatus === "email_error"): ?>

<div class="alert alert-error">

     Email форматы дұрыс емес.

</div>

<?php endif; ?>


<form
    class="form"
    method="POST"
    onsubmit="return validateForm()"
>

    <input
        type="text"
        id="full_name"
        name="full_name"
        placeholder="Аты-жөніңіз"
    >


    <input
        type="email"
        id="email"
        name="email"
        placeholder="Email"
    >


    <input
        type="tel"
        id="phone"
        name="phone"
        placeholder="Телефон"
    >


    <textarea
        id="message"
        name="message"
        placeholder="Хабарламаңыз..."
    ></textarea>


    <button
        type="submit"
        class="btn"
    >
        Өтінім жіберу →
    </button>

</form>

</div>

</div>

</div>

</section>


<!-- =======================================================
     MODAL
======================================================= -->

<div
    class="modal"
    id="welcomeModal"
>

<div class="modal-box">

    <button
        class="modal-close"
        onclick="closeModal()"
    >
        ×
    </button>

    <div class="section-label">
        DREAMHOUSE
    </div>

    <h2 style="
    font-family:'Playfair Display';
    font-size:35px;
    margin-bottom:15px;
    ">

        Үй таңдауға дайынсыз ба?

    </h2>

    <p style="
    color:#999;
    margin-bottom:25px;
    ">

        Біз сіздің арманыңыздағы үйді
        жобалауға көмектесеміз.

    </p>

    <a
        href="#contact"
        class="btn"
        onclick="closeModal()"
    >
        Кеңес алу
    </a>

</div>

</div>


<!-- =======================================================
     FOOTER
======================================================= -->

<footer>

<div class="container">

<div class="footer-grid">


<div>

    <div class="footer-logo">
        DREAM<span style="color:#c9a65c">
            HOUSE
        </span>
    </div>

    <p>
        Modern architecture.
        Timeless living.
    </p>

    <p>
        Армандаған үйіңізді
        бүгіннен бастаңыз.
    </p>

</div>


<div>

    <h4>
        Navigation
    </h4>

    <br>

    <a href="#home">
        Home
    </a>

    <a href="#about">
        About
    </a>

    <a href="#services">
        Services
    </a>

    <a href="#catalog">
        Catalog
    </a>

</div>


<div>

    <h4>
        Explore
    </h4>

    <br>

    <a href="#faq">
        FAQ
    </a>

    <a href="#contact">
        Contact
    </a>

    <a href="index.php?page=404">
        404 Page
    </a>

</div>


<div>

    <h4>
        Social
    </h4>

    <br>

    <a href="#">
        Instagram
    </a>

    <a href="#">
        Telegram
    </a>

    <a href="#">
        WhatsApp
    </a>

</div>


</div>


<div class="copyright">

    © 2026 DreamHouse.
    All Rights Reserved.

    <br>

    Designed for Web Project.

</div>

</div>

</footer>


<?php endif; ?>


<!-- =======================================================
     JAVASCRIPT
======================================================= -->

<script>


/* =========================================================
   FORM VALIDATION
========================================================= */

function validateForm() {

    const name =
        document.getElementById("full_name").value.trim();

    const email =
        document.getElementById("email").value.trim();

    const phone =
        document.getElementById("phone").value.trim();

    const message =
        document.getElementById("message").value.trim();


    if (!name || !email || !phone || !message) {

        alert(
            " Барлық өрісті толтырыңыз!"
        );

        return false;
    }


    const emailPattern =
        /^[^\s@]+@[^\s@]+\.[^\s@]+$/;


    if (!emailPattern.test(email)) {

        alert(
            " Email дұрыс енгізілмеген!"
        );

        return false;
    }


    if (phone.length < 10) {

        alert(
            " Телефон нөмірін дұрыс енгізіңіз!"
        );

        return false;
    }


    return true;
}


/* =========================================================
   SLIDER
========================================================= */

let currentSlide = 0;

const slides =
    document.getElementById("slides");


function showSlide(index) {

    if (!slides) return;

    const total =
        slides.children.length;

    if (index >= total) {
        currentSlide = 0;
    }

    if (index < 0) {
        currentSlide = total - 1;
    }

    slides.style.transform =
        `translateX(-${currentSlide * 100}%)`;
}


function nextSlide() {

    currentSlide++;

    showSlide(currentSlide);
}


function prevSlide() {

    currentSlide--;

    showSlide(currentSlide);
}


/* =========================================================
   AUTO SLIDER
========================================================= */

setInterval(() => {

    nextSlide();

}, 5000);


/* =========================================================
   ACCORDION
========================================================= */

function toggleAccordion(element) {

    const item =
        element.parentElement;

    item.classList.toggle("active");

    const icon =
        element.querySelector("span");

    if (item.classList.contains("active")) {

        icon.textContent = "−";

    } else {

        icon.textContent = "+";

    }
}


/* =========================================================
   MODAL
========================================================= */

function closeModal() {

    const modal =
        document.getElementById("welcomeModal");

    modal.classList.remove("active");
}


/* =========================================================
   SHOW MODAL
========================================================= */

window.addEventListener(
    "load",
    function() {

        setTimeout(() => {

            const modal =
                document.getElementById("welcomeModal");

            if (modal) {

                modal.classList.add("active");

            }

        }, 2500);

    }
);


/* =========================================================
   CLOSE MODAL WHEN CLICKING OUTSIDE
========================================================= */

window.addEventListener(
    "click",
    function(event) {

        const modal =
            document.getElementById("welcomeModal");

        if (
            modal &&
            event.target === modal
        ) {

            closeModal();

        }

    }
);

</script>


</body>

</html>