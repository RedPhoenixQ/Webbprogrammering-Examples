<?PHP
include 'dbconnect.php';

$ID = getpostAJAX("ID", true);
$name = getpostAJAX("name", true);
$type = getpostAJAX("type", true);
$company = getpostAJAX("company", true);
$location = getpostAJAX("location", true);
$category = getpostAJAX("category", true);
$size = getpostAJAX("size", true);
$cost = getpostAJAX("cost", true);
$auxdata = getpostAJAX("auxdata");

reportMissingParams();

try {
	$querystring = "INSERT INTO resource(ID,name, type,company,location,category,size,cost,auxdata) values (:ID,:NAME,:TYPE,:COMPANY,:LOCATION,:CATEGORY,:SIZE,:COST,:AUXDATA);";
	$stmt = $pdo->prepare($querystring);
	$stmt->bindParam(':ID', $ID);
	$stmt->bindParam(':NAME', $name);
	$stmt->bindParam(':TYPE', $type);
	$stmt->bindParam(':COMPANY', $company);
	$stmt->bindParam(':LOCATION', $location);
	$stmt->bindParam(':CATEGORY', $category);
	$stmt->bindParam(':SIZE', $size);
	$stmt->bindParam(':COST', $cost);
	$stmt->bindParam(':AUXDATA', $auxdata);
	$stmt->execute();

	header("Content-Type:text/xml; charset=utf-8");
	echo '<created status="OK"/>';

} catch (PDOException $e) {
	err("Error!: " . $e->getMessage() . "<br/>");
	die();
}

?>