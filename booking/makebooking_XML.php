<?PHP
include 'dbconnect.php';

// Get and escape the variables from post
$resource = getpostAJAX("resourceID", true);
$date = getpostAJAX("date", true);
$dateto = getpostAJAX("dateto");
$user = getpostAJAX("customerID", true);
$rebate = getpostAJAX("rebate");
$status = getpostAJAX("status", true);
$position = getpostAJAX("position", true);
$auxdata = getpostAJAX("auxdata");
$type = getpostAJAX("type", true);

reportMissingParams();

// Default to 1 day bookings
if (is_null($dateto)) {
	$dateto = $date;
}

// Check if $status is legal option
if ($status < 1 && $status > 2) {
	err("Illegal status: $status (must be either 1 - preliminary booking OR 2 - permanent booking)");
}

// Default rebate to 0
if (is_null($rebate)) {
	$rebate = 0;
}

$querystring = "SELECT count(*) AS cnt FROM resource WHERE ID=:RESID and type=:TYPE;";
$stmts = $pdo->prepare($querystring);
$stmts->bindParam(':RESID', $resource);
$stmts->bindParam(':TYPE', $type);
$stmts->execute();
$row = $stmts->fetch();

if ($row['cnt'] == 0) {
	err("Error!: Resource " . $resource . " does not exist for type " . $type);
} else {
	try {

		// Delete temp bookings for this user
		$querystring = "DELETE FROM booking WHERE status=1 and customerID=:CUSTID;";
		$stmt = $pdo->prepare($querystring);
		$stmt->bindParam(':CUSTID', $user);
		$stmt->execute();


		// Retrieve size and cost from resource
		$size = 0;
		$cost = 0;
		$querystring = "SELECT * FROM resource WHERE ID=:RESID";
		$stmts = $pdo->prepare($querystring);
		$stmts->bindParam(':RESID', $resource);
		$stmts->execute();
		foreach ($stmts as $key => $row) {
			$size = $row['size'];
			$cost = $row['cost'];
		}

		// Save booking.
		$querystring = "INSERT INTO booking(customerID,resourceID,position,date,dateto,cost,rebate,status,auxdata) values (:USER,:RESID,:POSITION,DATE_FORMAT(:DATE,'%Y-%m-%d %H:%i'),DATE_FORMAT(:DATETO,'%Y-%m-%d %H:%i'),:COST,:REBATE,:STATUS,:AUXDATA);";
		$stmts = $pdo->prepare($querystring);
		$stmts->bindParam(':USER', $user);
		$stmts->bindParam(':RESID', $resource);
		$stmts->bindParam(':POSITION', $position);
		$stmts->bindParam(':DATE', $date);
		$stmts->bindParam(':DATETO', $dateto);
		$stmts->bindParam(':COST', $cost);
		$stmts->bindParam(':REBATE', $rebate);
		$stmts->bindParam(':STATUS', $status);
		$stmts->bindParam(':AUXDATA', $auxdata);
		$stmts->execute();

		// Count number of booked resources
		$querystring = "SELECT count(*) as counted FROM booking where resourceid=:RESID and date=:DATE";
		$stmts = $pdo->prepare($querystring);
		$stmts->bindParam(':RESID', $resource);
		$stmts->bindParam(':DATE', $date);
		$stmts->execute();

		// Compute Remaining Resources for Date (equals)
		foreach ($stmts as $kkey => $row) {
			$counted = $row['counted'];
		}
		$remaining = $size - $counted;

		// Make random artificial delay 1.5s - 2s
		usleep(rand(3000, 5000) * 1000);

		switch (determineResponseType()) {
			case "xml":
				// Successfull booking
				header("Content-Type:text/xml; charset=utf-8");
				echo "<result size='" . $size . "' bookingcost='" . $cost . "' remaining='" . $remaining . "'   />";
				break;
			case "json":
			default:
				$result = [
					"size" => $size,
					"bookingcost" => $cost,
					"remaining" => $remaining,
				];
				header("Content-Type:application/json; charset=utf-8");
				echo json_encode($result);
				break;
		}

	} catch (PDOException $e) {
		err("Error!: " . $e->getMessage() . "<br/>");
		die();
	}
}

?>