<?PHP
include 'dbconnect.php';

$customerID = getpostAJAX("customerID", true);

reportMissingParams();

try {
	$querystring = "SELECT * FROM customer WHERE ID=:ID";
	$stmt = $pdo->prepare($querystring);
	$stmt->bindParam(':ID', $customerID);
	$stmt->execute();

	$output = "<customers>\n";
	foreach ($stmt as $key => $row) {
		// At the moment do nothing!
		$output .= "<customer \n";
		$output .= "    id='" . htmlentities($row['ID']) . "'\n";
		$output .= "    firstname='" . htmlentities($row['firstname']) . "'\n";
		$output .= "    lastname='" . htmlentities($row['lastname'] . " ") . "'\n";
		$output .= "    address='" . htmlentities($row['address']) . "'\n";
		$output .= "    lastvisit='" . htmlentities($row['lastvisit']) . "'\n";
		$output .= "    email='" . htmlentities($row['email']) . "'\n";
		$output .= "    auxdata='" . htmlentities($row['auxdata']) . "'\n";
		$output .= " />\n";
	}
	$output .= "</customers>";

	// Update first so if it crashes we have not printed the data first
	$querystring = "UPDATE customer SET lastvisit=now() WHERE ID=:ID";
	$stmt = $pdo->prepare($querystring);
	$stmt->bindParam(':ID', $customerID);
	$stmt->execute();

	header("Content-Type:text/xml; charset=utf-8");
	echo $output;

} catch (PDOException $e) {
	err("Error!: " . $e->getMessage() . "<br/>");
	die();
}



?>