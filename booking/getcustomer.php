<?PHP
include 'dbconnect.php';

$customerID = getpostAJAX("customerID", true);

reportMissingParams();

try {
	$querystring = "SELECT ID as id, firstname, lastname, address, lastvisit, email, auxdata FROM customer WHERE ID=:ID";
	$stmt = $pdo->prepare($querystring);
	$stmt->bindParam(':ID', $customerID);
	$stmt->execute();

	// Update first so if it crashes we have not printed the data first
	$querystring = "UPDATE customer SET lastvisit=now() WHERE ID=:ID";
	$stmt = $pdo->prepare($querystring);
	$stmt->bindParam(':ID', $customerID);
	$stmt->execute();

	switch (determineResponseType()) {
		case 'xml':
			$output = "<customers>\n";
			foreach ($stmt as $key => $row) {
				// At the moment do nothing!
				$output .= "<customers \n";
				$output .= "    id='" . presenthtml($row['id']) . "'\n";
				$output .= "    firstname='" . presenthtml($row['firstname']) . "'\n";
				$output .= "    lastname='" . presenthtml($row['lastname'] . " ") . "'\n";
				$output .= "    address='" . presenthtml($row['address']) . "'\n";
				$output .= "    lastvisit='" . presenthtml($row['lastvisit']) . "'\n";
				$output .= "    email='" . presenthtml($row['email']) . "'\n";
				$output .= "    auxdata='" . presenthtml($row['auxdata']) . "'\n";
				$output .= " />\n";
			}
			$output .= "</customers>";

			header("Content-Type:text/xml; charset=utf-8");
			echo $output;
			break;
		case "json":
		default:
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			header("Content-Type:application/json; charset=utf-8");
			echo json_encode($result);
			break;
	}

} catch (PDOException $e) {
	err("Error!: " . $e->getMessage() . "<br/>");
	die();
}



?>