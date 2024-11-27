<?PHP
include 'dbconnect.php';

$resourceID = getpostAJAX("resourceID");
$searchresource = getpostAJAX("searchresource");
$date = getpostAJAX("date");
$type = getpostAJAX("type", true);

reportMissingParams();

try {
	// Set up query string
	$querystring = "SELECT resource.size,resource.type as application,booking.customerID,booking.resourceID,resource.name,resource.company,resource.location,DATE_FORMAT(booking.date,'%Y-%m-%d %H:%i') as date,DATE_FORMAT(booking.dateto,'%Y-%m-%d %H:%i') as dateto,booking.cost,booking.rebate,booking.position,booking.status,booking.auxdata FROM booking,resource WHERE resource.ID=booking.resourceID AND type=:TYPE ";

	if (!is_null($date))
		$querystring .= " AND date=:DATE";
	if (!is_null($searchresource)) {
		$querystring .= " AND resourceID like :RESID";
	} else if (!is_null($resourceID)) {
		$querystring .= " AND resourceID=:RESID";
	}
	$querystring .= " ORDER BY resourceid,position";

	$stmt = $pdo->prepare($querystring);

	// Bind parameters
	if (!is_null($date))
		$stmt->bindParam(':DATE', $date);
	if (!is_null($resourceID))
		$stmt->bindParam(':RESID', $resourceID);
	$stmt->bindParam(':TYPE', $type);

	// Execute
	$stmt->execute();

	switch (determineResponseType()) {
		case "xml":
			$output = "<bookings>\n";
			foreach ($stmt as $key => $row) {
				$output .= "<booking \n";
				$output .= "    application='" . htmlentities($row['application']) . "'\n";
				$output .= "    customerID='" . htmlentities($row['customerID']) . "'\n";
				$output .= "    resourceID='" . htmlentities($row['resourceID']) . "'\n";
				$output .= "    name='" . htmlentities($row['name']) . "'\n";
				$output .= "    company='" . htmlentities($row['company']) . "'\n";
				$output .= "    location='" . htmlentities($row['location']) . "'\n";
				$output .= "    date='" . $row['date'] . "'\n";
				$output .= "    dateto='" . $row['dateto'] . "'\n";
				$output .= "    position='" . $row['position'] . "'\n";
				$output .= "    status='" . $row['status'] . "'\n";
				$output .= "    cost='" . $row['cost'] . "'\n";
				$output .= "    size='" . $row['size'] . "'\n";
				$output .= "    auxdata='" . htmlentities($row['auxdata']) . "'\n";
				$output .= " />\n";
			}
			$output .= "</bookings>\n";

			header("Content-Type:text/xml; charset=utf-8");
			echo $output;
			break;
		case "json":
		default:
			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
			header("Content-Type:application/json; charset=utf-8");
			echo json_encode($result);
			break;
	}
} catch (PDOException $e) {
	err("Error!: " . $e->getMessage() . "<br/>");
	die();
}
?>