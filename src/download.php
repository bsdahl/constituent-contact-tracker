<?php
session_start();
//PUT THIS HEADER ON TOP OF EACH UNIQUE PAGE
if(!isset($_SESSION['username'])){
	header("location:login/main_login.php");
}
$sessionuser = $_SESSION['username'];

require_once('config.php');

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 


// output headers so that the file is downloaded rather than displayed
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=data.csv');

// create a file pointer connected to the output stream
$output = fopen('php://output', 'w');

// output the column headings
fputcsv($output, array('VisitID', 'VisitDate', 'VisitorsName', 'Comment', 'LocationID', 'ChurchName', 'City', 'State'));

// fetch the data
$sql = "SELECT VisitID, VisitDate, VisitorsName, Comment, visit.LocationID, ChurchName, City, State
FROM visit
LEFT JOIN (
	SELECT LocationID, ChurchName, City, State
	From locations
	GROUP BY LocationID) groupv
ON visit.LocationID = groupv.LocationID
WHERE Owner = '" . $sessionuser ."'";

$result = $conn->query($sql);

// loop over the rows, outputting them
while ($row = $result->fetch_assoc()) fputcsv($output, $row);
?>