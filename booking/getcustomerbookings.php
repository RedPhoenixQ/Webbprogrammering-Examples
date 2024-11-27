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

  switch (determineResponseType()) {
    case "xml":
      header("Content-Type:text/xml; charset=utf-8");
      echo "<bookings>\n";
      foreach ($stmt as $key => $row) {
        echo "<booking \n";
        echo "    application='" . htmlentities($row['application']) . "'\n";
        echo "    customerID='" . htmlentities($row['customerID']) . "'\n";
        echo "    resourceID='" . htmlentities($row['resourceID']) . "'\n";
        echo "    name='" . htmlentities($row['name']) . "'\n";
        echo "    company='" . htmlentities($row['company']) . "'\n";
        echo "    location='" . htmlentities($row['location']) . "'\n";
        echo "    date='" . $row['date'] . "'\n";
        echo "    dateto='" . $row['dateto'] . "'\n";
        echo "    position='" . $row['position'] . "'\n";
        echo "    cost='" . $row['cost'] . "'\n";
        echo "    category='" . $row['category'] . "'\n";
        echo "    size='" . $row['size'] . "'\n";
        echo "    auxdata='" . $row['auxdata'] . "'\n";
        echo " />\n";
        echo "\n";
      }
      echo "</bookings>\n";
      break;
    case "json":
    default:
      header("Content-Type: application/json; charset=utf-8'");
      echo json_encode($result);
      break;
  }
} catch (PDOException $e) {
  err("Error!: " . $e->getMessage() . "<br/>");
  die();
}


?>