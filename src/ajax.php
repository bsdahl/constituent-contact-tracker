<?php
session_start();
//PUT THIS HEADER ON TOP OF EACH UNIQUE PAGE
if(!isset($_SESSION['username'])){
	header("location:login/main_login.php");
}
$sessionuser = $_SESSION['username'];

require_once('config.php');
require_once('functions.php');

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 

$action = $_POST['action'];



// ACTION getChurchINFO
////////////////////////
if ($action == "getChurchInfo") {
	$locationID = $_POST['locationID'];

	$sql = "SELECT * FROM locations WHERE LocationID = " . $locationID ."";

	$result = $conn->query($sql);

	if ($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
	    	$toecho = "<div class=\"well\"><p style=\"font-size:1.2em;\"><strong>" . htmlspecialchars($row["ChurchName"], ENT_QUOTES) . "</strong> <a href=\"#\" data-toggle=\"modal\" data-target=\"#editModal\">";

	    	if(is_admin())
	    		$toecho .= "<span style=\"font-size:.6em;\">edit</span>";

	    	$toecho .= "</a><br>" . $row["Address"] . " " . $row["Address2"] . "<br>" 
	    	. $row["City"] . ", " . $row["State"] . " " . $row["Zip"] . "<br>";
	    	if ($row["ChurchPhone"] !== null && $row["ChurchPhone"] !== '')
	    		$toecho .= "<span class=\"glyphicon glyphicon-earphone\"></span>&nbsp;<a href=\"tel:" . $row["ChurchPhone"] . "\">" . $row["ChurchPhone"] . "</a><br>";
	    	if ($row["Website"] !== null && $row["Website"] !== '')
	    		$toecho .= "<span class=\"glyphicon glyphicon-globe\"></span>&nbsp;<a href=\"". addhttp($row["Website"]) . "\" target=\"_blank\">" . $row["Website"] . "</a><br>";
	    	if ($row["ChurchEmail"] !== null && $row["ChurchEmail"] !== '') 
	    		$toecho .= "<span class=\"glyphicon glyphicon-envelope\"></span>&nbsp;<a href=\"mailto:" . $row["ChurchEmail"] . "\">" . $row["ChurchEmail"] . "</a><br>";

	    	
	    	$toecho .= "</p>";

	    	$toecho .= "<p style=\"font-size:1.2em;\"><strong>" . $row["PastorName"] . "</strong><br>";
	    	if ($row["PastorEmail"] !== null && $row["PastorEmail"] !== '')
	    		$toecho .= "<span class=\"glyphicon glyphicon-envelope\"></span>&nbsp;<a href=\"mailto:" . $row["PastorEmail"] . "\">" . $row["PastorEmail"] . "</a><br>";
	    	if ($row["PastorWorkPhone"] !== null && $row["PastorWorkPhone"] !== '')
	    		$toecho .= "<span class=\"glyphicon glyphicon-earphone\"></span>&nbsp;<a href=\"tel:" . $row["PastorWorkPhone"] . "\">" . $row["PastorWorkPhone"] . "</a><br>";
	    	if ($row["PastorHomePhone"] !== null && $row["PastorHomePhone"] !== '')
	    		$toecho .= "<span class=\"glyphicon glyphicon-home\"></span>&nbsp;<a href=\"tel:" . $row["PastorHomePhone"] . "\">" . $row["PastorHomePhone"] . "</a><br>";
	    	if ($row["PastorCellPhone"] !== null && $row["PastorCellPhone"] !== '')
	    		$toecho .= "<span class=\"glyphicon glyphicon-phone\"></span>&nbsp;<a href=\"tel:" . $row["PastorCellPhone"] . "\">" . $row["PastorCellPhone"] . "</a><br>";
	    	
	    	$toecho .= "</p></div>";

	    	if (is_admin()) {
	    	$toecho .= "
	    		<div id=\"editModal\" class=\"modal fade\" tabindex=\"-1\" role=\"dialog\">
					  <div class=\"modal-dialog\">
					    <div class=\"modal-content\">
					      <div class=\"modal-header\">
					        <button type=\"button\" class=\"close\" data-dismiss=\"modal\" aria-label=\"Close\"><span aria-hidden=\"true\">&times;</span></button>
					        <h4 class=\"modal-title\">Edit Church Info</h4>
					      </div>
					      <div class=\"modal-body\">
							<form id=\"editForm\" action=\"ajax.php\" method=\"post\">
    							<input type=\"hidden\" name=\"action\" value=\"editChurch\">
    							<input type=\"hidden\" name=\"LocationID\" id=\"LocationID\" value=\"" . $row["LocationID"] . "\">";

    		foreach ($row as $key => $value) {
    			if ($key !== "LocationID" && $key !== "ChurchName" && $key !== "Address" && $key !== "Address2" && $key !== "City" && $key !== "State" && $key !== "Zip" && $key !== "District" && $key !== "Latitude" && $key !== "Longitude") {
    				$toecho .= "
    				<div class=\"form-group\">
    					<label>" . $key . "</label>
    					<input type=\"text\" class=\"form-control\" name=\"" . $key . "\" value=\"" . $value . "\">
    				</div>";
    			}
    		}
    							

    		$toecho .= "		<button type=\"submit\" class=\"btn btn-default\">Submit</button>
    						</form>
    						<div id=\"editFormSucess\"></div>
					      </div>
					      <div class=\"modal-footer\">
					        <button id=\"closeEditForm\" type=\"button\" class=\"btn btn-default\" data-dismiss=\"modal\">Close</button>
					      </div>
					    </div><!-- /.modal-content -->
					  </div><!-- /.modal-dialog -->
					</div><!-- /.modal -->
	    	";
	    	}
	    }
	}

	$sql = "SELECT *
	FROM visit
	WHERE LocationID = " . $locationID . " AND Owner = '" . $sessionuser . "'
	ORDER BY VisitDate DESC";
	
	$result = $conn->query($sql);

	if ($result->num_rows > 0) {
		$toecho .= "<div class=\"panel panel-default\"><div class=\"panel-heading\">Recent Contact List</div><table id=\"contactTable\" class=\"table table-striped\"><thead><tr><th style=\"width: 90px;\">Date</th><th>Name</th><th>Comment</th><th></th><tr></thead><tbody>";
			while($row = $result->fetch_assoc()) {
				$toecho .= "<tr class=\"record\"><td>" . $row["VisitDate"] . "</td><td>" . $row["VisitorsName"] . "</td><td class=\"comment\">" . $row["Comment"] . "</td><td><span id=\"" . $row["VisitID"] . "\" class=\"glyphicon glyphicon-remove-circle\" style=\"display:none;cursor:pointer;\" data-toggle=\"tooltip\" data-placement=\"top\" title=\"Delete?\"></span></tr>";
			}
		$toecho .= "</tbody></table></div>";
	} else if ($result->num_rows == 0) {
		$toecho .= "<p>No recent contacts.</p>";
	}

	echo $toecho;
}

// ACTION addVisit
////////////////////////

if ($action == "addVisit") {

	// prepare and bind
	$stmt = $conn->prepare("INSERT INTO visit (LocationID, Owner, VisitorsName, VisitDate, Comment) VALUES (?, ?, ?, ?, ?)");
	$stmt->bind_param("issss",$locationID, $sessionuser, $visitorsName, $visitDate, $comment);

	$locationID = sanitize($_POST["LocationID"]);
	$visitorsName = sanitize($_POST["VisitorsName"]);
	$time = strtotime(sanitize($_POST["VisitDate"]));
	$visitDate = date('Y-m-d',$time);
	$comment = sanitize($_POST["Comment"]);

	if ($stmt->execute()) {
		echo "<br><div class=\"alert alert-success alert-dismissible\" role=\"alert\"><button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\"><span aria-hidden=\"true\">&times;</span></button>Success</div><p>Reload the page to refresh the map icon.</p>";
	} else {
		echo "<br><div class=\"alert alert-warning\" role=\"alert\"><button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\"><span aria-hidden=\"true\">&times;</span></button>Error<br></div>";
	}

	$stmt->close();
}

// ACTION deleteVisit
////////////////////////
if ($action == "deleteVisit") {

	$visitID = $_POST["visitID"];

	$sql = "DELETE FROM visit
			WHERE VisitID = " . $visitID ." AND Owner = '" . $sessionuser . "'";

	$result = $conn->query($sql);

}

// ACTION editChurch
////////////////////////
if ($action == "editChurch" && is_admin()) {

	$sql = "UPDATE locations SET ";
	foreach($_POST as $key => $value) {
		if($key !== 'LocationID' && $key !== 'action' && $value !== '') {
			$sql .= $key . "=\"" . sanitize($value) . "\", ";
		} else if($key !== 'LocationID' && $key !== 'action' && $value == ''){
			$sql .= $key . "=NULL, ";
		}
	}
	$sql = rtrim($sql, ', ');
	$sql .= " WHERE locationID=" . sanitize($_POST["LocationID"]) . "";

	$result = $conn->query($sql);

	if ($result) {
		echo "<br><div class=\"alert alert-success alert-dismissible\" role=\"alert\"><button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\"><span aria-hidden=\"true\">&times;</span></button>Success</div>";
	} else {
		echo "<br><div class=\"alert alert-warning alert-dismissible\" role=\"alert\"><button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\"><span aria-hidden=\"true\">&times;</span></button>Error<br>" . $sql . "</div>";
	}
	//var_dump($_POST);

}

// ACTION getRcentContacts
////////////////////////
if ($action == "getRecentContacts") {

// Recent Contacts Query
$sql = "SELECT VisitDate, VisitorsName, Comment, ChurchName, City, State
FROM visit
LEFT JOIN (
	SELECT LocationID, ChurchName, City, State
	From locations
	GROUP BY LocationID) groupv
ON visit.LocationID = groupv.LocationID
WHERE Owner = '" . $sessionuser . "'
ORDER BY VisitDate DESC
LIMIT 5";

$recentresult = $conn->query($sql);

$toecho = "<div class=\"jumbotron\">
					  <h2>Welcome!</h2>
					  <p>Click on a map icon to view and add new contacts.</p>
					</div>";

if ($recentresult->num_rows > 0) {
	$toecho .= "<div class=\"panel panel-default\"><div class=\"panel-heading\">Most Recent Contacts</div><table id=\"recent-contacts\" class=\"table table-striped table-condensed\"><thead><tr><th style=\"width: 90px;\">Date</th><th>Church</th><th>City</th><th>State</th><th>Name</th><th>Comment</th></tr></thead><tbody>";
		
	while($row = $recentresult->fetch_assoc()) {
		$toecho .= "<tr class=\"record\"><td>" . $row["VisitDate"] . "</td><td>" . $row["ChurchName"] . "</td><td>" . $row["City"] . "</td><td>" . $row["State"] . "</td><td>" . $row["VisitorsName"] . "</td><td class=\"comment\">" . $row["Comment"] . "</td><td></tr>";
	}

	$toecho .= "</tbody></table></div>";
} else if ($recentresult->num_rows == 0) {
	$toecho .= "<p>No recent contacts.</p>";
}

echo $toecho;

}

$conn->close(); ?>