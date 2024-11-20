<?php
//---------------------------------------------------------------------------------------------------------------
// Build Search Query!
//---------------------------------------------------------------------------------------------------------------

include 'dbconnect.php';

$company = getpostAJAX("company");
$type = getpostAJAX("type", true);
$location = getpostAJAX("location");
$name = getpostAJAX("name");
$fulltext = getpostAJAX("fulltext");
$resID = getpostAJAX("resID");
$category = getpostAJAX("category");

reportMissingParams();

if (!is_null($fulltext)) {
	$company = "%" . $fulltext . "%";
	$location = "%" . $fulltext . "%";
	$name = "%" . $fulltext . "%";
	$resID = "%" . $fulltext . "%";
}

//---------------------------------------------------------------------------------------------------------------
// Make Result!
//---------------------------------------------------------------------------------------------------------------					

try {
	if (is_null($category) || is_null($company) || is_null($location) || is_null($fulltext) || is_null($name) || is_null($resID)) {
		$querystring = "SELECT * FROM resource WHERE type=:TYPE AND (category like :CATEGORY or name like :NAME or company like :COMPANY or location like :LOCATION or id like :RESID)";
		$stmt = $pdo->prepare($querystring);
		$stmt->bindParam(':TYPE', $type);
		$company = "%" . $company . "%";
		$stmt->bindParam(':COMPANY', $company);
		$category = "%" . $category . "%";
		$stmt->bindParam(':CATEGORY', $category);
		$name = "%" . $name . "%";
		$stmt->bindParam(':NAME', $name);
		$location = "%" . $location . "%";
		$stmt->bindParam(':LOCATION', $location);
		$resID = "%" . $resID . "%";
		$stmt->bindParam(':RESID', $resID);
		$stmt->execute();
	} else {
		$querystring = "SELECT * FROM resource WHERE type=:TYPE";
		$stmt = $pdo->prepare($querystring);
		$stmt->bindParam(':TYPE', $type);
		$stmt->execute();
	}

	header("Content-Type:text/xml; charset=utf-8");
	echo "<resources>\n";
	foreach ($stmt as $key => $row) {
		echo "<resource \n";
		echo "    id='" . presenthtml($row['ID']) . "'\n";
		echo "    name='" . presenthtml($row['name']) . "'\n";
		echo "    company='" . presenthtml($row['company']) . "'\n";
		echo "    location='" . presenthtml($row['location']) . "'\n";
		echo "    size='" . $row['size'] . "'\n";
		echo "    cost='" . $row['cost'] . "'\n";
		echo "    category='" . $row['category'] . "'\n";
		echo "    auxdata='" . $row['auxdata'] . "'\n";
		echo " />\n";
		echo "\n";
	}
	echo "</resources>";

} catch (PDOException $e) {
	err("Error!: " . $e->getMessage() . "<br/>");
	die();
}
?>