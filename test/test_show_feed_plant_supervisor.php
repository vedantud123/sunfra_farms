<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Styled Clickable Table</title>
<style>

    * {

      margin: 0;

      padding: 0;

      box-sizing: border-box;

      font-family: 'Segoe UI', sans-serif;

    }
 
    body {
	  background-color: #cfe9f2;
	  min-height: 100vh;
	  display: flex;
	  align-items: center;
	  justify-content: center;
	  padding: 30px 20px;
	  animation: fadeIn 1s ease-in-out;
	}
    .container {

      background: rgba(255, 255, 255, 0.1);

      border: 2px solid rgba(255, 255, 255, 0.3);

      backdrop-filter: blur(12px);

      -webkit-backdrop-filter: blur(12px);

      border-radius: 20px;

      padding: 30px 30px 35px;

      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);

      max-width: 420px;

      width: 100%;

      animation: slideUp 1s ease forwards;

      position: relative;

    }
 
    .back-button {

      font-size: 15px;

      background: linear-gradient(to right, #0077cc, #0099ff);

      color: white;

      border: none;

      border-radius: 8px;

      padding: 10px 18px;

      cursor: pointer;

      margin-bottom: 20px;

      transition: all 0.3s ease;

    }
 
    .back-button:hover {

      background: linear-gradient(to right, #005fa3, #007fcc);

      transform: scale(1.05);

    }
 
    h1 {

      text-align: center;

      margin-bottom: 30px;

      color: #004c99;

      font-size: 28px;

      letter-spacing: 1px;

      text-shadow: 1px 1px 2px rgba(0,0,0,0.1);

    }
 
    table {

      width: 100%;

      border-collapse: collapse;

    }
 
    td {

      padding: 10px 0;

    }
 
    .button {

      width: 100%;

      padding: 14px 0;

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

      .back-button {

        font-size: 14px;

        padding: 8px 14px;

      }

    }
</style>
</head>
<body>
<div class="container">
<button class="back-button" onclick="window.location.href='https://sunfra.com/farm/test/test_dashboard.php'">← Back</button>
 
    <h1>Select an Option</h1>
 
    <table>
<tr>
<td>
<button class="button" onclick="window.location.href='https://sunfra.com/farm/test/feedformuladetails/feed_formula_details.php'">Feed Formula</button>
</td>
</tr>
<tr>
<td>
<button class="button" onclick="window.location.href='https://sunfra.com/farm/test/feedrawmaterial/feed_raw_material_json_to_web.php'">Feed Raw Material</button>
</td>
</tr>
<tr>
<td>
<button class="button" onclick="window.location.href='https://sunfra.com/farm/test/feedsheadfeeding/feed_shead_feeding.php'">Feed Material to Shead</button>
</td>
</tr>
<tr>
<td>
<button class="button" onclick="window.location.href='https://sunfra.com/farm/test/sanitization/sanitization.php'">Water Medicine</button>
</td>
</tr>
<tr>
<td>
<button class="button" onclick="window.location.href='https://sunfra.com/farm/test/feednewstockloading/feed_new_stock_loading.php'">Update Raw Material</button>
</td>
</tr>
</table>
</div>
 
  <script>

    function goBack() {

      window.history.back();

    }
</script>
</body>
</html>

 