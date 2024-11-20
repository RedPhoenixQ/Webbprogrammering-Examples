<?PHP
include 'dbconnect.php';

$customerID = getpostAJAX("customerID", true);
$type = getpostAJAX("type", true);

reportMissingParams();

try {
  $querystring = "SELECT resource.type as application,booking.customerID,booking.resourceID,resource.name,resource.company,resource.location,DATE_FORMAT(booking.date,'%Y-%m-%d %H:%i') as date,DATE_FORMAT(booking.dateto,'%Y-%m-%d %H:%i') as dateto,booking.cost,booking.rebate,booking.position,booking.status,resource.category,resource.size,booking.auxdata FROM customer,booking,resource WHERE resource.ID=booking.resourceID AND booking.customerID=customer.ID AND customer.ID=:CUSTID AND type=:TYPE order by booking.date";
  $stmt = $pdo->prepare($querystring);
  $stmt->bindParam(':CUSTID', $customerID);
  $stmt->bindParam(':TYPE', $type);
  $stmt->execute();

  $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

  foreach ($result as &$row) {
    $row["key"] = $row['customerID'] . $row['resourceID'] . $row['position'] . $row['date'];
  }

  header("Content-Type: application/json; charset=utf-8'");
  echo json_encode($result);
} catch (PDOException $e) {
  err("Error!: " . $e->getMessage() . "<br/>");
  die();
}


?>