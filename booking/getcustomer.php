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
	$querystring2 = "UPDATE customer SET lastvisit=now() WHERE ID=:ID";
	$stmt2 = $pdo->prepare($querystring2);
	$stmt2->bindParam(':ID', $customerID);
	$stmt2->execute();

	switch (determineResponseType()) {
		case 'xml':
			$output = "<customers>\n";
			foreach ($stmt as $key => $row) {
				// At the moment do nothing!
				$output .= "<customers \n";
				$output .= "    id='" . htmlentities($row['id']) . "'\n";
				$output .= "    firstname='" . htmlentities($row['firstname']) . "'\n";
				$output .= "    lastname='" . htmlentities($row['lastname'] . " ") . "'\n";
				$output .= "    address='" . htmlentities($row['address']) . "'\n";
				$output .= "    lastvisit='" . htmlentities($row['lastvisit']) . "'\n";
				$output .= "    email='" . htmlentities($row['email']) . "'\n";
				$output .= "    auxdata='" . htmlentities($row['auxdata']) . "'\n";
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