<?PHP
include 'dbconnect.php';

// Normal Search or ID Search
$resid = getpostAJAX("resid");
$name = getpostAJAX("name");
$location = getpostAJAX("location");
$company = getpostAJAX("company");
$type = getpostAJAX("type", true);

reportMissingParams();

// Full text Search
$fulltext = getpostAJAX("fulltext");
if (!is_null($fulltext)) {
	$company = "%" . $fulltext . "%";
	$location = "%" . $fulltext . "%";
	$name = "%" . $fulltext . "%";
	$resID = "%" . $fulltext . "%";
}

$querystring_start = "SELECT
    DATE_FORMAT(avail.date, '%Y-%m-%d %H:%i') AS date,
    DATE_FORMAT(avail.dateto, '%Y-%m-%d %H:%i') AS dateto,
    avail.resourceID,
    name,
    location,
    company,
    size,
    resource.cost,
    category,
    resource.auxdata,
    count(booking.resourceID) AS bookingcount,
    size - count(booking.resourceID) AS remaining
FROM resourceavailability as avail
JOIN resource ON avail.resourceID = resource.ID
LEFT JOIN booking ON avail.resourceID = booking.resourceID AND booking.date BETWEEN avail.date AND avail.dateto ";
$querystring_end = " GROUP BY
   	avail.resourceID,
   	avail.date,
		avail.dateto
ORDER BY
    resource.ID,
    avail.date";

try {
	// Search either for ID or for resource!
	if (!is_null($fulltext)) {
		$querystring = $querystring_start . "WHERE (resource.company like :COMPANY or resource.name like :NAME or resource.location like :LOCATION or resource.ID=:RESID) and resource.type=:TYPE" . $querystring_end;
		$stmt = $pdo->prepare($querystring);
		$stmt->bindParam(':TYPE', $type);
		$stmt->bindParam(':RESID', $resid);
		$stmt->bindParam(':COMPANY', $company);
		$stmt->bindParam(':NAME', $name);
		$stmt->bindParam(':LOCATION', $location);
		$stmt->execute();
	} else if (!is_null($resid)) {
		$querystring = $querystring_start . "WHERE resource.ID=:RESID and resource.type=:TYPE" . $querystring_end;
		$stmt = $pdo->prepare($querystring);
		$stmt->bindParam(':TYPE', $type);
		$stmt->bindParam(':RESID', $resid);
		$stmt->execute();
	} else if (is_null($resid) && is_null($name) && is_null($location) && is_null($company) && is_null($fulltext)) {
		$querystring = $querystring_start . "WHERE resource.type=:TYPE" . $querystring_end;
		$stmt = $pdo->prepare($querystring);
		$stmt->bindParam(':TYPE', $type);
		$stmt->execute();
	} else {
		$querystring = $querystring_start . "WHERE (resource.company like :COMPANY or resource.name like :NAME or resource.location like :LOCATION) and resource.type=:TYPE" . $querystring_end;
		$stmt = $pdo->prepare($querystring);
		$stmt->bindParam(':TYPE', $type);
		$company = "%" . $fulltext . "%";
		$stmt->bindParam(':COMPANY', $company);
		$name = "%" . $fulltext . "%";
		$stmt->bindParam(':NAME', $name);
		$location = "%" . $fulltext . "%";
		$stmt->bindParam(':LOCATION', $location);
		$stmt->execute();
	}

	switch (determineResponseType()) {
		case "xml":
			$output = "<avail>\n";
			foreach ($stmt as $key => $row) {
				$output .= "<availability \n";

				$output .= "    resourceID='" . presenthtml($row['resourceID']) . "'\n";
				$output .= "    name='" . presenthtml($row['name']) . "'\n";
				$output .= "    location='" . presenthtml($row['location']) . "'\n";
				$output .= "    company='" . presenthtml($row['company']) . "'\n";
				$output .= "    size='" . $row['size'] . "'\n";
				$output .= "    cost='" . $row['cost'] . "'\n";
				$output .= "    category='" . $row['category'] . "'\n";
				$output .= "    date='" . $row['date'] . "'\n";
				$output .= "    dateto='" . $row['dateto'] . "'\n";
				$output .= "    auxdata='" . $row['auxdata'] . "'\n";
				$output .= "    bookingcount='" . $row['bookingcount'] . "'\n";
				$output .= "    remaining='" . $row['remaining'] . "'\n";

				$output .= " />\n";
			}
			$output .= "</avail>\n";
			header("Content-Type:text/xml; charset=utf-8");
			echo $output;
			break;
		case "json":
		default:
			$result = $stmt->fetchAll();
			header("Content-Type:application/json; charset=utf-8");
			echo json_encode($result);
			break;
	}

} catch (PDOException $e) {
	err("Error!: " . $e->getMessage() . "<br/>");
	die();
}
?>