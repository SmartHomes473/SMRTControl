<html>
<style>
#header {
    background-color:purple;
    color:black;
    width:100%;
    text-align:center;
    padding:1px;
}
#nav {
    line-height:30px;
    background-color:black;
    height:75px;
    width:100%;
    float:left;
    padding:1px;
    overflow-x: scroll;
}
#section {
    width:100%;
    float:left;
    padding:10px; 
}
#footer {
    background-color:black;
    color:white;
    clear:both;
    text-align:center;
    padding:5px; 
}
#navelement{
    color:yellow;
    float:left;
    padding-right:8px;
}
<!--
a:link{
    color: yellow ! important;
}
a:visited{
    color: yellow ! important;
}-->
</style>

<body align="center">
<div id="header">
<h1> <IMG SRC="SMRTHAUSlogoV1.png" WIDTH=60 HEIGHT=60> SMRTControl for SMRTHAUS</h1>
</div>
<div id="nav">
   <div id="navelement"><a href="wwf.php">Wall Weather Forecaster</a></div>
   <div id="navelement"><a href="wwf.php">Wall Weather Forecaster</a></div>
   <div id="navelement"><a href="wwf.php">Wall Weather Forecaster</a></div>
   <div id="navelement"><a href="wwf.php">Wall Weather Forecaster</a></div>
   <div id="navelement"><a href="wwf.php">Wall Weather Forecaster</a></div>
   <div id="navelement"><a href="wwf.php">Wall Weather Forecaster</a></div>
   <div id="navelement"><a href="roomba.php">Roomba</a></div>
</div>
<!-- BGCOLOR="DDA0DD" align="middle" ><h1>SMRTControl for SMRTHAUS</h1>
<p>THIS WEBSITE IS UNDER CONTRUCTION</p>
<p><a href="jk.html">Unless you click this link</a></p>
<p>Test: <a href="control.php">GPIO</a> or 
<a href="uart.php">UART</a></p>
<p><IMG SRC="SMRTHAUSlogoV1.png" WIDTH=200 HEIGHT=200 ></p>
-->

<div id="section">
<iframe src="wwf.php" style="width: 100%; height: 1000px" frameBorder="0" scrolling="yes" ></iframe>
</div>
</body>
</html>
