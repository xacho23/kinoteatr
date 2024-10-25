<!DOCTYPE html>
<html lang="ru">
<head>


<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Бронирование билетов в кинотеатр</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<?php session_start(); ?>
<header style="margin-bottom: -20px;">
<div class="logo"><a href="index.php"><img src="aaa.png" alt="logo"></a></div>
    <button class="hamburger" aria-label="Открыть меню">
    <span></span>
    <span></span>
    <span></span>
    </button>
    <div class="overlay"></div>
    <nav class="navigation">
        <a href="index.php" class="nav-link">Главная</a>
        <a href="rasp.php" class="nav-link">Расписание</a>
        <a href="about.php" class="nav-link">О нас</a>
        <?php if(isset($_SESSION['loggedin']) && $_SESSION['loggedin']): ?>
            <a href="profile.php" class="nav-link special-link">Профиль</a>
            <a href="logout.php" id="regis" class="nav-link special-link">Выйти</a>
        <?php else: ?>
            <a href="login.php" id="vhod" class="nav-link special-link">Вход</a>
            <a href="signup.php" id="regis" class="nav-link special-link">Регистрация</a>
        <?php endif; ?>
    </nav>
</header>


<div class="signin1"> 
    <div class="signin"> 
      <div class="content"> 
       <h2>Регистрация</h2> 
       <form action="register.php" method="post">
       <div class="form"> 
          <div class="inputBox">
            <input type="text" name="mail" required> <i>Эл.почта</i>
          </div>
          <div class="inputBox">
            <input type="password" name="password" required> <i>Пароль</i>
          </div>
          <div class="inputBox">
            <input type="submit" value="Зарегистрироваться">
          </div>
       </div> 
      </form>
      <?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (isset($_SESSION['message'])) {
        $style = "padding: 10px; margin: 10px 0; border-radius: 5px; background-color: #333;";
        if (isset($_SESSION['message_type']) && $_SESSION['message_type'] == 'error') {
            $style .= "color: red; border: 1px solid red;";
        } else {
            $style .= "color: green; border: 1px solid green;";
        }

        echo "<p style='{$style}'>{$_SESSION['message']}</p>";
        unset($_SESSION['message']);
        unset($_SESSION['message_type']); 
    }
?>  
      </div> 
     </div> 
     </div> 





















     <footer class="site-footer">
  <div class="footer-container">
    <div class="footer-about">
      <h3>Кинохаус</h3>
      <p>Кинохаус — там, где каждый фильм становится частью вашей жизни, где встречаются мечты и реальность, где начинается ваша собственная история великолепия кинематографа.</p>
    </div>
    <div class="footer-social">
      <h3 style="text-align:center; margin-left:-5px; color:white">Соцсети</h3>
      <a href="#"><img style="width:60px; height:40px; text-align:center" src="vk.png"></a>
      <a href="#"><img style="width:70px; height:40px; text-align:center" src="yt.png"></a>
      <a href="#"><img style="width:65px; height:40px; text-align:center" src="whats.png"></a>
    </div>
    <div class="footer-contact">
      <h3 class="footertext">Контактная информация</h3>
      <p class="footertext">Телефон: +7 (989) 521-30-69</p>
      <p class="footertext">Email: kinohouse@mail.ru</p>
    </div>
  </div>
</footer>


<script>
document.addEventListener('DOMContentLoaded', function() {
    const hamburger = document.querySelector('.hamburger');
    const nav = document.querySelector('.navigation');
    const overlay = document.querySelector('.overlay');

    hamburger.addEventListener('click', function() {
        this.classList.toggle('active');
        nav.classList.toggle('active');
        overlay.classList.toggle('active');
    });

    overlay.addEventListener('click', function() {
        this.classList.remove('active');
        nav.classList.remove('active');
        hamburger.classList.remove('active');
    });
});


</script>


<script src="script.js"></script>
</body>
</html>
