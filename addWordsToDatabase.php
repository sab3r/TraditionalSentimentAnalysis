<?php 
$txt_file    = file_get_contents('/var/www/twitter/SentiWordNet_3.0.0_20130122.txt');//SentiWordNet_3.0.0_20130122
$rows        = explode("\n", $txt_file);
array_shift($rows);

$DBhostname = "MySQL Server hostname goes here";
$DBusername = "MySQL username goes here";
$DBpassword = "MySQL password goes here";
$DBdatabase = "seminar"; // Database name
$con = mysqli_connect($DBhostname,$DBusername,$DBpassword,$DBdatabase);

$i=0;
foreach($rows as $row => $data)
{
$i=$i+1;
$data = str_replace("#", " ", $data);
$row_data = explode(" ", $data);
$data = $row_data[0];
$rank = explode("\t",$row_data[1]);

$data = explode("\t",$data);
//echo $data[0]." ".$data[1]." ".$data[2]." ".$data[3]." ".$data[4]." ";echo $rank[0];

mysqli_query($con,"INSERT INTO dictionary (type, ID, pos , neg , word , rank)
VALUES ('".$data[0]."', ".$data[1].",".$data[2].",".$data[3].",'".$data[4]."',".$rank[0].")");
//echo $data;

  echo $i."\n";  
}
mysqli_close($con);
?>