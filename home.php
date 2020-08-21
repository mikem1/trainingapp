<?php
// We need to use sessions, so you should always start sessions using the below code.
session_start();
// If the user is not logged in redirect to the login page...
if ( !isset( $_SESSION[ 'loggedin' ] ) ) {
  header( 'Location: index.html' );
  exit;
}

include 'include.php';
$host = $sqlh;
$user = $sqlu;
$passwd = $sqlp;
$db = $sqld;

$con = new mysqli( $host, $user, $passwd, $db );

if ( $con->connect_errno ) {

  printf( "connection failed: %s\n", $con->connect_error() );
  exit();
}

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Home Page</title>
<link href="style.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css">
</head>
<body class="loggedin">
<nav class="navtop">
  <div>
    <h1><a href="home.php" style="font-size: 22px;">Training App</a></h1>
    <a href="profile.php"><i class="fas fa-user-circle"></i>Profile</a> <a href="logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a> </div>
</nav>
<div class="content">
  <?php
  if ( !isset( $_GET[ 'co' ] ) ) {
    echo "<h2>Courses</h2>";
    $query = "SELECT * FROM course";

    if ( $res = $con->query( $query ) ) {  //====COURSES!!!!=====

      while ( $row = $res->fetch_object() ) {
		  
        $q3 = "SELECT * FROM progress LEFT JOIN concept ON (concept.cid = progress.pcid) WHERE ccoid = '" . $row->coid . "' AND paid = '" . $_SESSION[ 'id' ] . "'";
        //echo $q3;
		$r3 = $con->query( $q3 );
        if ( $row3 = $r3->fetch_object() ) {
          $resume = "RESUME";
        } else {
          $resume = "";
        }
        // if num rows in progress = num rows concepts mark as complete
        // this is the last completed
        $q4 = "SELECT * FROM concept LEFT JOIN progress ON (progress.pcid = concept.cid) WHERE ccoid = '" . $row->coid . "'";
        $r4 = $con->query( $q4 );

        // this is the total incl not completed
        $q5 = "SELECT * FROM concept LEFT JOIN progress ON (progress.pcid = concept.cid) WHERE ccoid = '" . $row->coid . "' AND paid IS NOT NULL";
        $r5 = $con->query( $q5 );


        if ($r4->num_rows == 0){
			$resume = "COMING SOON!";	
		}elseif( $r4->num_rows == $r5->num_rows ){
			$resume = "COMPLETED";		
		}			
		  
		  
        echo "<p><a href='?co=" . $row->coid . "'>" . $row->coname . "</a> ".$resume."</p>";

      }

      $res->close();
    } else {

      echo "failed to fetch data\n";
    }
  } elseif ( !isset( $_GET[ 'l' ] ) ) { // ======= LESSONS!!! ========
    echo "<h2><a href='home.php'>Back to Courses</a></h2>";
    $query = "SELECT * FROM lesson WHERE lcoid = '" . $_GET[ 'co' ] . "'";

    if ( $res = $con->query( $query ) ) {

      //printf("Select query returned %d rows.\n", $res->num_rows);
      //echo "<p><a href='?co=".$_GET['co']."&l=".@$row->lid."&c=".@$row2->cid."'>".@$row->lname."</a></p>";

      while ( $row = $res->fetch_object() ) {
        //find the last concept id
        $q2 = "SELECT * FROM concept LEFT JOIN progress ON (progress.pcid = concept.cid) WHERE clid = '" . $row->lid . "' AND paid IS NULL";
        //echo $q2;
        $r2 = $con->query( $q2 );
        $row2 = $r2->fetch_object();

        $q3 = "SELECT * FROM progress LEFT JOIN concept ON (concept.cid = pcid) WHERE clid = '" . $row->lid . "' AND paid = '" . $_SESSION[ 'id' ] . "'";
        //echo $q3;
        $r3 = $con->query( $q3 );
        if ( $row3 = $r3->fetch_object() ) {
          $resume = "RESUME";
        } else {
          $resume = "";
        }
        // if num rows in progress = num rows concepts mark as complete
        // this is the last completed
        $q4 = "SELECT * FROM concept LEFT JOIN progress ON (progress.pcid = concept.cid) WHERE clid = '" . $row->lid . "'";
        $r4 = $con->query( $q4 );

        // this is the total incl not completed
        $q5 = "SELECT * FROM concept LEFT JOIN progress ON (progress.pcid = concept.cid) WHERE clid = '" . $row->lid . "' AND paid IS NOT NULL";
        $r5 = $con->query( $q5 );


        if ( $r4->num_rows == $r5->num_rows )$resume = "COMPLETED";

        echo "<p><a href='?co=" . $_GET[ 'co' ] . "&l=" . @$row->lid . "&c=" . @$row2->cid . "'>" . @$row->lname . "</a> " . $resume . "</p>";
      }

      $res->close();
    } else {

      echo "failed to fetch data\n";
    }
  } elseif ( !isset( $_GET[ 'c' ] ) || isset( $_GET[ 'c' ] ) ) { // =============== Concepts =============


    echo "<h2><a href='home.php?co=" . $_GET[ 'co' ] . "'>Back to Lessons</a></h2>";
    $query = "SELECT * FROM concept LEFT JOIN lesson ON (lesson.lid = concept.clid) LEFT JOIN progress ON (progress.pcid = concept.cid) WHERE lid = '" . $_GET[ 'l' ] . "' AND (paid = '" . $_SESSION[ 'id' ] . "' OR paid IS NULL)";


    if ( $res = $con->query( $query ) ) {

      //echo "<div style='width:300px;'>";
      while ( $row = $res->fetch_object() ) {
        $t = "";
        if ( $row->pdone == '1' )$t = "^";
        echo "<p>" . $t . "<a href='?co=" . $row->lid . "&l=" . $row->lid . "&c=" . $row->cid . "'>" . $row->cname . "</a></p>";

        //printf("%s %s %s\n", $row->id, $row->name, $row->price);
      }
      //echo "</div>";
      //	echo "<div style='width:300px;'>video goes here";
      //echo "</div>";


      // anti dupe insert progress 
      if ( $_GET[ 'c' ] != "" ) {
        $q2 = "SELECT * FROM progress WHERE pcoid = '" . $_GET[ 'co' ] . "' AND plid = '" . $_GET[ 'l' ] . "' AND pcid = '" . $_GET[ 'c' ] . "' AND pdone = 1 AND paid = '" . $_SESSION[ 'id' ] . "'";

        //echo $q2;
        $r2 = $con->query( $q2 );
        if ( $row2 = $r2->fetch_object() ) {

        } else {
          $q3 = "INSERT INTO progress (pcoid,plid,pcid,pdone,paid) VALUES ('" . $_GET[ 'co' ] . "','" . $_GET[ 'l' ] . "','" . $_GET[ 'c' ] . "','1','" . $_SESSION[ 'id' ] . "')";

          echo $q3;
          $r3 = $con->query( $q3 );

        }
        //die;


      }

      $res->close();
    } else {

      echo "failed to fetch data\n";
    }
  } elseif ( isset( $_GET[ 'c' ] ) ) { // =============== Concepts  IF SET=============


      echo "<h2><a href='home.php?co=" . $_GET[ 'co' ] . "'>Back to Lessons</a></h2>";
      //$query = "SELECT * FROM concept LEFT JOIN lesson ON (lesson.lid = concept.clid) LEFT JOIN progress ON (progress.pcid = concept.cid) WHERE lid = '".$_GET['l']."'";
      $query = "SELECT * FROM concept LEFT JOIN lesson ON (lesson.lid = concept.clid) LEFT JOIN progress ON (progress.pcid = concept.cid) WHERE lid = '" . $_GET[ 'l' ] . "' AND (paid = '1' OR paid IS NULL)";

      if ( $res = $con->query( $query ) ) {

        //printf("Select query returned %d rows.\n", $res->num_rows);
        //echo "<div style='width:300px;'>";
        while ( $row = $res->fetch_object() ) {
          echo "<p><a href='?co=" . $row->lid . "&l=" . $row->lid . "'>" . $row->cname . "</a></p>";

          //printf("%s %s %s\n", $row->id, $row->name, $row->price);
        }
        //echo "</div>";
        //	echo "<div style='width:300px;'>video goes here";
        //echo "</div>";

        $res->close();
      } else {

        echo "failed to fetch data\n";
      }
    } // end endif lesson


  ?>
</div>
</body>
</html>