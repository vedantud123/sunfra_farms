<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Labour Management</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Segoe UI', sans-serif;
    }

    body {
      background: linear-gradient(135deg, #cfd9df, #e2ebf0);
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 30px 20px;
      animation: fadeIn 1s ease-in-out;
    }

    .go-back {
      margin-bottom: 20px;
      background-color: #ffffffaa;
      color: #004c99;
      padding: 10px 20px;
      font-size: 15px;
      border: 1px solid #0077cc;
      border-radius: 8px;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .go-back:hover {
      background-color: #e6f3ff;
      color: #003366;
    }

    .container {
      background: rgba(255, 255, 255, 0.1);
      border: 2px solid rgba(255, 255, 255, 0.3);
      backdrop-filter: blur(12px);
      -webkit-backdrop-filter: blur(12px);
      border-radius: 20px;
      padding: 35px 30px;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
      max-width: 420px;
      width: 100%;
      animation: slideUp 1s ease forwards;
      text-align: center;
    }

    h1 {
      text-align: center;
      margin-bottom: 30px;
      color: #004c99;
      font-size: 28px;
      letter-spacing: 1px;
      text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
    }

    .button {
      width: 100%;
      padding: 14px 0;
      margin: 12px 0;
      font-size: 18px;
      font-weight: bold;
      color: #ffffff;
      background: linear-gradient(to right, #0077cc, #0099ff);
      border: none;
      border-radius: 10px;
      cursor: pointer;
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
    }

    .button:hover {
      transform: scale(1.03);
      background: linear-gradient(to right, #005fa3, #007fcc);
      box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
    }

    .button:active {
      transform: scale(0.97);
    }

    @keyframes slideUp {
      from {
        transform: translateY(40px);
        opacity: 0;
      }
      to {
        transform: translateY(0);
        opacity: 1;
      }
    }

    @keyframes fadeIn {
      from {
        opacity: 0;
      }
      to {
        opacity: 1;
      }
    }

    @media (max-width: 600px) {
      body {
        padding: 20px 10px;
      }

      .container {
        padding: 25px 20px;
      }

      h1 {
        font-size: 24px;
      }

      .button {
        font-size: 16px;
        padding: 12px 0;
      }

      .go-back {
        font-size: 14px;
        padding: 8px 16px;
      }
    }
  </style>
</head>
<body>
  

  <div class="container">
	<button class="go-back" onclick="location.href='https://sunfra.com/farm/test/test_dashboard.php'">⬅️ Go Back</button>
	
    <h1>Labour Management</h1>

    <button class="button" onclick="location.href='https://sunfra.com/farm/test/labour_master_json_to_web.php'">
      ➤ Labour Details
    </button>

    <button class="button" onclick="location.href='https://sunfra.com/farm/test/labour_attendance_json_to_web.php'">
      ✔️ Labour Attendance
    </button>
	
	<button class="button" onclick="location.href='https://sunfra.com/farm/test/new_labour_master.php'">
      ✔️ Assigned Master
    </button>
  </div>

</body>
</html>
