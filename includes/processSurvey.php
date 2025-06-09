<?php include("connectToInfinityDB.inc");?>
<!DOCTYPE html>

<!-- Traitement des soumissions de sondage pour GabonSonde -->
<!-- Affiche un message de remerciement et traite la soumission du sondage. -->
<!-- Adapté pour GabonSonde -->
<!-- Modifié en 2024 -->

<html>
   <head>
      <meta charset="utf-8">
      <title>Thank You</title>
      <link href="https://fonts.googleapis.com/css?family=Chilanka&display=swap" rel="stylesheet">
      <link href="/css/master.css" rel="stylesheet" type="text/css">
   </head>
   <body>
     <section id="pinksquare">
       <!--Thank You message-->
       <h1>Thank You!</h1>
       <p>Your results have been sumbitted. Thank you for taking our survey.</p>
       <p>Your time of submission was:
       <?php
       date_default_timezone_set('Africa/Libreville');
       $date_and_time = date("l, F d Y g:i:s a");
       print($date_and_time);
       ?>
       </p>
     </section>



<?php

//Gives all survey questions an equal weight of 20 points possible. More points = more comfort.
function calculatePoints() {
  // TODO: This function needs a complete rewrite for GabonSonde dynamic survey processing.

  $q1 = $_POST['ans1'] * 20;
  $q2 = $_POST['ans2'] * 2;
  $q3 = $_POST['ans3'] * 20;
  $q4 = $_POST['ans4'] * 20;
  $q5 = $_POST['ans5'] * 20;

  $q6 = $_POST['ans6'] * 2;
  $q7 = $_POST['ans7'] * 20;
  $q8 = $_POST['ans8'] * 5;
  $q9 = $_POST['ans9'] * 20;
  $q10 = $_POST['ans10'] * 5;

  $q11 = $_POST['ans11'] * 20;
  $q12 = $_POST['ans12'] * 20;
  $q13 = $_POST['ans13'] * 20;
  $q14 = $_POST['ans14'] * 20;
  $q15 = $_POST['ans15'] * 20;

  $q16 = $_POST['ans16'] * 5;
  $q17 = $_POST['ans17'] * 20;
  $q18 = $_POST['ans18'] * 20;
  $q19 = $_POST['ans19'] * 20;
  $q20 = $_POST['ans20'] * 20;

  $q21 = $_POST['ans21'] * 20;
  $q22 = $_POST['ans22'] * 20;
  $q23 = $_POST['ans23'] * 20;
  $q24 = $_POST['ans24'] * 20;
  $q25 = $_POST['ans25'] * 20;

  $g1 = ($q1 + $q2 + $q3 + $q4 + $q5) / 5;
  $g2 = ($q6 + $q7 + $q8 + $q9 + $q10) / 5;
  $g3 = ($q11 + $q12 + $q13 + $q14 + $q15) / 5;
  $g4 = ($q16 + $q17 + $q18 + $q19 + $q20) / 5;
  $g5 = ($q21 + $q22 + $q23 + $q24 + $q25) / 5;

  insertDataToDB($g1, $g2, $g3, $g4, $g5);
}



//Update MYSQL table with new values.
function insertDataToDB($p1, $p2, $p3, $p4, $p5)
  // TODO: This function needs a complete rewrite for GabonSonde dynamic survey processing and a new DB schema.
{
	$dataBase = connectDB();
	$st1 = "INSERT INTO surveyresults(cat1, cat2, cat3, cat4, cat5) ";
	$st2 = "VALUES(";
  $st3 = $p1.",";
  $st4 = $p2.",";
  $st5 = $p3.",";
  $st6 = $p4.",";
  $st7 = $p5;
	$st99 = ");";
	//form the query
  $query1 = $st1.$st2.$st3.$st4.$st5.$st6.$st7.$st99;
  //execute the query in the databse
	$result1 = mysqli_query($dataBase, $query1) or die('Query failed: ' . mysqli_error($dataBase));
  $dataBase->close();
}


//calculatePoints(); // Temporarily disabled - needs complete rework for GabonSonde
?>

</body>
</html>
