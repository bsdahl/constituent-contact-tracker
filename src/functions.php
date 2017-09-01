<?php 
function is_admin() {
    GLOBAL $admins;
    GLOBAL $sessionuser;
    $success = false;
    
    foreach($admins as $user) {
        if($user === $sessionuser) {
            $success = true;
            break;
        }
    }
    return $success;
}

function geocode($address){
 
    // url encode the address
    $address = urlencode($address);
     
    // google map geocode api url
    $url = "http://maps.google.com/maps/api/geocode/json?address={$address}";
 
    // get the json response
    $resp_json = file_get_contents($url);
     
    // decode the json
    $resp = json_decode($resp_json, true);
 
    // response status will be 'OK', if able to geocode given address 
    if($resp['status']=='OK'){
 
        // get the important data
        $lati = $resp['results'][0]['geometry']['location']['lat'];
        $longi = $resp['results'][0]['geometry']['location']['lng'];
        $formatted_address = $resp['results'][0]['formatted_address'];
         
        // verify if data is complete
        if($lati && $longi && $formatted_address){
         
            // put the data in the array
            $data_arr = array();            
             
            array_push(
                $data_arr, 
                    $lati, 
                    $longi, 
                    $formatted_address
                );
             
            return $data_arr;
             
        }else{
            return false;
        }
         
    }else{
        return false;
    }
}

function checkGeo($result) {
    // Get the GEOCoder Latitude and Longitude if not present
    if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                if ($row['Latitude'] == null && $row['Longitude'] == null){
                    $address = $row["Address"]. " " . $row["Address2"]. " " . $row["City"] . " " . $row["State"] . " " . $row["Zip"];
                    $data = geocode($address);
                    $sql = 'UPDATE locations SET Latitude='.$data[0].', Longitude='.$data[1].' WHERE LocationID='.$row[LocationID].'';
                    $update = $conn->query($sql);
                }
            }
        }
    
    $result->data_seek(0);
}

function addhttp($url) {
    if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
        $url = "http://" . $url;
    }
    return $url;
}

function sanitize($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}
?>