<?php
session_start();
require_once '../config/db_connection.php';

  
 
  
        $created_at=$_GET['created_at'];
		//$sqls="select * from nametable where rollno='$username'";
		$sqls="select * from admission_forms  where created_at like '%$created_at%'";
		//echo "<script>window.location='view.php';</scrip>";
		
		$result=$con->query($sqls);
		//print_r($result);
		while($rows=$result->fetch_assoc())
		?>