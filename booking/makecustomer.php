<?PHP
include 'dbconnect.php';

$ID = getpostAJAX("ID", true);
$firstname = getpostAJAX("firstname", true);
$lastname = getpostAJAX("lastname", true);
$address = getpostAJAX("address", true);
$email = getpostAJAX("email", true);
$auxdata = getpostAJAX("auxdata");

reportMissingParams();

try {
	$querystring = "INSERT INTO customer(lastvisit,ID, firstname,lastname,address,email,auxdata) values (NOW(),:ID,:FIRSTNAME,:LASTNAME,:ADDRESS,:EMAIL,:AUXDATA);";

	$stmt = $pdo->prepare($querystring);
	$stmt->bindParam(':ID', $ID);
	$stmt->bindParam(':FIRSTNAME', $firstname);
	$stmt->bindParam(':LASTNAME', $lastname);
	$stmt->bindParam(':ADDRESS', $address);
	$stmt->bindParam(':EMAIL', $email);
	$stmt->bindParam(':AUXDATA', $auxdata);
	$stmt->execute();

	// Make random artificial delay 1.5s - 2s
	usleep(rand(300, 5000) * 1000);

	switch (determineResponseType()) {
		case 'xml':
			header("Content-Type:text/xml; charset=utf-8");
			echo '<created status="OK"/>';
			break;
		case "json":
		default:
			$result = [
				"status" => "OK"
			];
			header("Content-Type:application/json; charset=utf-8");
			echo json_encode($result);
			break;
	}

} catch (PDOException $e) {
	err("Error!: " . $e->getMessage() . "<br/>");
	die();
}

?>