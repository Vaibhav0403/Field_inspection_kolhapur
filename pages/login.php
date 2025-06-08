<!DOCTYPE html>
<html lang="mr">
<head>
  <meta charset="UTF-8">
  <title>फिल्ड इन्स्पेक्शन सिस्टिम</title>
 <style>
  body {
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
      background-color: #ffffff;
    }

    header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      background-color: #007bff;
      color: white;
      padding: 30px 20px;
    }

    header img {
      height: 60px;
    }

    header .title {
      text-align: center;
      flex: 1;
    }

    header .title h1 {
      margin: 0;
      font-size: 24px;
    }

    header .title h2 {
      margin: 0;
      font-size: 16px;
      color: #cfe2ff;
    }

    .container {
      display: flex;
      justify-content: space-between;
      padding: 10px;
      align-items: center;
      margin-top: 210px;
      margin-right:100px;
      flex-wrap: wrap;
    }

    .image-section {
      flex: 1;
      text-align: center;
    }

    .image-section img {
      max-width: 100%;
      height: 500px;
    }

    .login-section {
      flex: 1;
      max-width: 400px;
      background: #fff;
      padding: 50px;
      border-radius: 10px;
      box-shadow: 0 0 15px rgba(0,0,0,0.1);
    }

    .login-section h2 {
      text-align: center;
      color: #007bff;
      margin-bottom: 20px;
    }

    .form-group {
      margin-bottom: 20px;
    }

    .form-group label {
      display: block;
      margin-bottom: 5px;
      font-weight: bold;
    }

    .form-group input {
      width: 100%;
      padding: 10px;
      font-size: 16px;
      border-radius: 5px;
      border: 1px solid #ccc;
    }

    .login-btn {
      width: 100%;
      padding: 12px;
      background-color: #007bff;
      color: white;
      font-size: 16px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
    }

    .login-btn:hover {
      background-color: #0056b3;
    }

    @media (max-width: 768px) {
      .container {
        flex-direction: column;
        padding: 20px;
      }

      .login-section {
        margin-top: 30px;
      }
    }
    </style>
</head>
<body>

<header>
  <img src="../assets/images/satymev-jayte.png" alt="India Emblem" />
  <div class="title">
    <h1>फिल्ड इन्स्पेक्शन सिस्टिम</h1>
    <h2>जिल्हा परिषद, कोल्हापूर</h2>
  </div>
  <img src="../assets/images/zplogofin.png" alt="ZP Logo" />
</header>

<div class="container">
  <div class="image-section">
    <img src="../assets/images/login_image.jpeg" alt="Illustration">
  </div>

  <div class="login-section">
    <h2>लॉगिन</h2>
   <form action="../actions/login_action.php" method="POST">
      <div class="form-group">
        <label for="username">युजरनेम</label>
        <input type="text" id="username" name="username" placeholder="उदाहरण: sanjay">
      </div>

      <div class="form-group">
        <label for="password">पासवर्ड</label>
        <input type="password" id="password" name="password" placeholder="पासवर्ड">
      </div>

      <button type="submit" class="login-btn">Login</button>
    </form>
  </div>
</div>

</body>
</html>
