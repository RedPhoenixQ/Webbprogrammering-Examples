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

	header("Content-Type:text/xml; charset=utf-8");
	echo '<deleted status="OK"/>';
} catch (PDOException $e) {
	err("Error!: " . $e->getMessage() . "<br/>");
	die();
}
?>