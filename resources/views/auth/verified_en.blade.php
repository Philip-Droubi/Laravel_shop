<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>email verified</title>
    <link rel="icon" type="image/png" sizes="32x32" href="Imgs/favicon-32x32.png">
    <style>
* {
  -webkit-box-sizing: border-box;
  -moz-box-sizing: border-box;
  box-sizing: border-box;
  padding: 0;
  margin: 0;
}
/* Small */
@media (min-width: 768px) {
  .container {
    width: 750px;
  }
}
/* Medium */
@media (min-width: 992px) {
  .container {
    width: 970px;
  }
}
/* Large */
@media (min-width: 1200px) {
  .container {
    width: 1170px;
  }
}
/* XLarge */
@media (min-width: 1400px) {
  .container {
    width: 1250px;
  }
}
.container {
  padding-left: 22px;
  padding-right: 22px;
  margin-left: auto;
  margin-right: auto;
  position: absolute;
}
body {
  font-family: sans-serif;
  min-height: 100vh;
  background-color: rgb(245, 187, 80);
}
header{
  text-align: center;
  position: relative;
  height: 80px;
  background-color:yellow;
  display: flex;
align-items: center;
justify-content: center;
}
header .container{
  background-color: green;
  color: red;
  width: 100%;
}
main{
height: 60vh;
width: 100%
}
main .container{
height: 60%;
width: 100%;
position: absolute;
padding: 30px;
display: flex;
align-items: center;
justify-content: center;
}
main p{
text-align: center;
font-weight: 600;
font-size: 20px;
}
footer{
  height: 20vh;
  width: 100%;
  background-color: rgb(252, 216, 149);
  padding: 30px 10px;
  position: absolute;
  bottom: 0;
  display: flex;
  justify-content: center;
  align-items: center;
}
footer .container{
  /* margin-top: 50px; */
  
  text-align: center;
  font-weight: 500;
  font-size: 1em;
  color: #222;
  display: flex;
align-items: center;
justify-content: center;
}
    </style>
</head>
<body>
    <header>
        <div class="container">
          <p>head</p>
        </div>
    </header>
    <main>
      <div class="container">
        <p>You confirmed your email you can now back to the app.</p>
      </div>
    </main>
    <footer>
        <div class="container">
<p>&COPY; All right reserved to shopy team 2022</p>
        </div>
    </footer>
</body>
</html>