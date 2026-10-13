<?php

session_start();

// Check user login or not
if(!isset($_SESSION['username'])){
    header('Location: index.php');
}



?>

 <meta name="viewport" content="width=device-width, initial-scale=1">
<div style="
    margin: 25px;
">
	<h2>
		<h2 style="
    font-style: italic;
    color: #0c75c9;
			   ">  Welcome, Login  </h2><h4>	<a href="logout.php" onclick="w3_close()" style="color: red;margin-left: 85%;display: block;z-index: -1;margin-top: -43px;padding: 1px;">Logout</a></h4> 	
    <div>

		<hr>
		<br>

      <?php if($_SESSION['Photo'] != ""): ?>
       <img src="photo/<?php echo $_SESSION['Photo']?>"style="
    vertical-align: middle;
    width: 100px;
    height: 100px;
    border-radius: 50%;
    box-shadow: 0 4px 8px 0 rgb(38 25 25);
">
        <?php else: ?>
     <img src="photo/no.png" style="
    vertical-align: middle;
    width: 100px;
    height: 100px;
    border-radius: 50%;
    box-shadow: 0 4px 8px 0 rgb(38 25 25);
">
        <?php endif; ?>

	
<h2 style="
    font-style: italic;
    color: #2079ff;
    font-weight: 900;
">  <?php echo $_SESSION['fullname']?></h2> 
<hr>  
</div>
   

<embed src="beep.mp3" style="visibility:hidden" />


	