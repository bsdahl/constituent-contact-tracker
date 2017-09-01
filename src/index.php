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

$sql = "SELECT *, locations.LocationID, MaxVisitDate
	FROM locations
	LEFT JOIN (
		SELECT LocationID, Owner, MAX(VisitDate) AS MaxVisitDate
		FROM visit
		Where Owner = '" . $sessionuser ."'
		GROUP BY LocationID) groupv
	ON locations.LocationID = groupv.LocationID";

$result = $conn->query($sql);

$numberLocations = $result->num_rows;

//checkGeo($result);



?>
<!DOCTYPE html>
<html>
  <head>
    <title>AFLC Churches Map Tracker</title>
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no">
    <meta charset="utf-8">

    <!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
	
	<!-- Optional theme -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css">

	<!-- DatePicker CSS -->
	<link rel="stylesheet" href="./css/bootstrap-datetimepicker.min.css">

	<!-- Main Style CSS -->
	<link rel="stylesheet" href="./css/style.css">

    <!-- Jquery CDN -->
	<script src="https://code.jquery.com/jquery-2.2.3.min.js"></script>

	<!-- Latest compiled and minified JavaScript -->
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>

	<!-- Moment JS -->
	<script type="text/javascript" src="./js/moment.js"></script>

	<!-- DatePicker -->
	<script type="text/javascript" src="./js/bootstrap-datetimepicker.min.js"></script>

	<!-- Google Maps API -->
	<script type="text/javascript" src="https://maps.google.com/maps/api/js?&key=AIzaSyBIV-EZqvBqdWHVnT8IZknFPvg8tc2dT_0&libraries=places"></script>
    
  </head>
  <body>
    
    <div class="container-fluid">
    	<div class="row">
    		<div id="left" class="col-md-4">
    			<!-- Begin Navbar -->
				<nav class="navbar navbar-default">
				  <div class="container-fluid">
				    <div class="navbar-header">
				    	<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#mainNav" aria-expanded="false">
						  <span class="sr-only">Toggle navigation</span>
						  <span class="icon-bar"></span>
						  <span class="icon-bar"></span>
						  <span class="icon-bar"></span>
						</button>
				      <a class="navbar-brand" href="./">Contact Tracker</a>
				    </div>
					<div class="collapse navbar-collapse" id="mainNav">
    				  <ul class="nav navbar-nav navbar-right">
    				    <li class="dropdown">
    				      <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><?php echo $sessionuser . '&nbsp;';?><span class="caret"></span></a>
    				      <ul class="dropdown-menu">
    				      	<li><a href="download.php">Download CSV</a></li>
    				        <li><a href="login/logout.php">Logout</a></li>
    				      </ul>
    				    </li>
    				  </ul>
    				</div><!-- /.navbar-collapse -->
				  </div>
				</nav>
    			
    			<!-- Begin Left Sidebar Content -->
    			<div id="home">
					<div class="jumbotron">
					  <h2>Welcome!</h2>
					  <p>Click on a map icon to view and add new contacts.</p>
					</div>
					

					<?php
						// Recent Contacts Query
						$sql = "SELECT VisitDate, visit.LocationID, VisitorsName, Comment, ChurchName, City, State
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
						$toecho = '';
						if ($recentresult->num_rows > 0) {
							$toecho .= "<div class=\"panel panel-default\"><div class=\"panel-heading\">Most Recent Contacts</div><table id=\"recent-contacts\" class=\"table table-striped table-condensed\"><thead><tr><th style=\"width: 90px;\">Date</th><th>Church</th><th>City</th><th>State</th><th>Name</th><th>Comment</th></tr></thead><tbody>";
								
							while($row = $recentresult->fetch_assoc()) {
								$toecho .= "<tr id=\"" . $row["LocationID"] ."\" class=\"record\" style=\"cursor:pointer;\" ><td>" . $row["VisitDate"] . "</td><td>" . $row["ChurchName"] . "</td><td>" . $row["City"] . "</td><td>" . $row["State"] . "</td><td>" . $row["VisitorsName"] . "</td><td class=\"comment\">" . $row["Comment"] . "</td><td></tr>";
							}

							$toecho .= "</tbody></table></div>";
						} else if ($recentresult->num_rows == 0) {
							$toecho .= "<p>No recent contacts.</p>";
						}
					
						echo $toecho;

						// Recently Added Contacts Query
						$sql = "SELECT VisitID, visit.LocationID, VisitDate, VisitorsName, Comment, ChurchName, City, State
						FROM visit
						LEFT JOIN (
							SELECT LocationID, ChurchName, City, State
							From locations
							GROUP BY LocationID) groupv
						ON visit.LocationID = groupv.LocationID
						WHERE Owner = '" . $sessionuser . "'
						ORDER BY VisitID DESC
						LIMIT 5";
						
						$recentresult = $conn->query($sql);
						$toecho = '';
						if ($recentresult->num_rows > 0) {
							$toecho .= "<div class=\"panel panel-default\"><div class=\"panel-heading\">Recently Added</div><table id=\"recently-added\" class=\"table table-striped table-condensed\"><thead><tr><th style=\"width: 90px;\">Date</th><th>Church</th><th>City</th><th>State</th><th>Name</th><th>Comment</th></tr></thead><tbody>";
								
							while($row = $recentresult->fetch_assoc()) {
								$toecho .= "<tr id=\"" . $row["LocationID"] ."\" class=\"record\" style=\"cursor:pointer;\"><td>" . $row["VisitDate"] . "</td><td>" . $row["ChurchName"] . "</td><td>" . $row["City"] . "</td><td>" . $row["State"] . "</td><td>" . $row["VisitorsName"] . "</td><td class=\"comment\">" . $row["Comment"] . "</td><td></tr>";
							}

							$toecho .= "</tbody></table></div>";
						} else if ($recentresult->num_rows == 0) {
							$toecho .= "<p>No recent contacts.</p>";
						}
					
						echo $toecho;
					?>
    			</div>

    			<div id="info" style="display:none;"></div>

    			<div id="addDiv" style="display:none;">
    				<button class="btn btn-default" style="margin-bottom:15px;" data-toggle="modal" data-target="#addContactModal">Add a new contact</button><br>
  
					<div id="addContactModal" class="modal fade" tabindex="-1" role="dialog">
					  <div class="modal-dialog">
					    <div class="modal-content">
					      <div class="modal-header">
					        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					        <h4 class="modal-title">Add a new contact</h4>
					      </div>
					      <div class="modal-body">
							<form id="addVisit" action="ajax.php" method="post">
    							<input type="hidden" name="action" value="addVisit">
    							<input type="hidden" name="LocationID" id="LocationID" value="0">
    							<div class="form-group">
    								<label>Name</label>
    								<input type="text" class="form-control" name="VisitorsName">
    							</div>
								<div class="form-group">
									<label>Date</label>
								    <div class='input-group date' id='datetimepicker'>
								        <input type='text' class="form-control" name="VisitDate" value="<?php echo date("Ymd"); ?>"/>
								        <span class="input-group-addon">
								            <span class="glyphicon glyphicon-calendar">
								            </span>
								        </span>
									</div>
								</div>
    							<div class="form-group">
    								<label>Comment</label>
    								<textarea id="comment_textarea" class="form-control" name="Comment"></textarea>
    								<div id="comment_feedback" style="color:red;"></div>
    							</div>
    							<button type="submit" class="btn btn-default">Submit</button>
    						</form>
    						<div id="formSucess"></div>
					      </div>
					      <div class="modal-footer">
					        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
					      </div>
					    </div><!-- /.modal-content -->
					  </div><!-- /.modal-dialog -->
					</div><!-- /.modal -->

    			</div>

    		</div>

    		<div id="right" class="col-md-8">
    			
    			<div id="map"></div>
    			
    			<input id="pac-input" class="controls" type="text" placeholder="Search Box">
				
				<div id="legend" class="hidden-xs hidden-sm">
				  <button type="button" class="close" id="closeLegend" aria-label="Close"><span aria-hidden="true" style="font-size:0.8em;">&times;</span></button>
				  <table>
				  	<caption>Legend

				  	</caption>
				  	<tr>
				  		<td><img src="church-1.png" height="70%;"></td>
				  		<td> < 1 year</td>
				  	</tr>
				  	<tr>
				  		<td><img src="church-2.png" height="70%;"></td>
				  		<td> < 2 year</td>
				  	</tr>
				  	<tr>
				  		<td><img src="church-3.png" height="70%;"></td>
				  		<td> > 2 year</td>
				  	</tr>
				  	<tr>
				  		<td><img src="church-4.png" height="70%;"></td>
				  		<td> No Contact</td>
				  	</tr>
				  </table>
				</div>

    		</div>
    	</div>
	</div>

	<div id="footer"><?php echo $numberLocations ?> Churches Loaded </div>
	
	<script>
		<?php include('js/main.js.php'); ?>
	</script>
  </body>
</html>
<?php $conn->close(); ?>