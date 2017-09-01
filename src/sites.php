<?php
require_once('config.php');

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 

$sql = "SELECT ChurchName, City, State, Website FROM locations WHERE Website IS NOT NULL";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // output data of each row
    echo "<table border=1>";
    while($row = $result->fetch_assoc()) {
        echo "<tr><td>" . $row["ChurchName"] . "</td><td>" . $row["City"] . "</td><td>" . $row["State"] . "</td><td><a href=\"http://" . $row["Website"] . "\">" . $row["Website"] . "</td></tr>";
    }
    echo "</table>";
} else {
    echo "0 results";
}
$conn->close();