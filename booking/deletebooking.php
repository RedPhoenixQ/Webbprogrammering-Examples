<?PHP
include 'dbconnect.php';

// Get and escape the variables from post
$resource = getpostAJAX("resourceID", true);
$date = getpostAJAX("date", true);
$user = getpostAJAX("customerID", true);

reportMissingParams();

try {
	$querystring = "DELETE FROM booking WHERE customerID=:CUSTID and date=:DDATE and resourceID=:RESOURCEID";
	$stmt = $pdo->prepare($querystring);
	$stmt->bindParam(':CUSTID', $user);
	$stmt->bindParam(':DDATE', $date);
	$stmt->bindParam(':RESOURCEID', $resource);
	$stmt->execute();

	if ($stmt->rowCount() != 1) {
		err("No booking was deleted. The resourceID or date might not exist");
	}

	switch (determineResponseType()) {
		case "xml":
			header("Content-Type:text/xml; charset=utf-8");
			echo '<deleted status="OK"/>';
			break;
		case "json":
		default:
			header("Content-Type:application/json; charset=utf-8");
			$result = ["status" => "ok"];
			echo json_encode($result);
			break;
	}
} catch (PDOException $e) {
	err("Error!: " . $e->getMessage() . "<br/>");
	die();
}
?>