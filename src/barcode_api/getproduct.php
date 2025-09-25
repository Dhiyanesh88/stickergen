<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, X-Requested-With");
header("Content-Type: application/json");
$servername = "localhost";
$username   = "root";   // change if different
$password   = "";       // change if you set password
$dbname     = "stickers_db";
// DB connect
$conn = new mysqli($servername, $username, $password, $dbname, 3307);
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "DB Connection failed"]);
    exit;
}
// JSON body read
$input = json_decode(file_get_contents("php://input"), true);
$barcode = $input['barcode'] ?? '';
if (!$barcode) {
    echo json_encode(["success" => false, "message" => "No barcode given"]);
    exit;
}
// Query product by barcode
$sql = "SELECT product_name, category, price FROM products WHERE barcode = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $barcode);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    echo json_encode([
        "success" => true,
        "product" => [
            "name"     => $row["product_name"],
            "category" => $row["category"],
            "price"    => $row["price"]
        ]
    ]);
} else {
    echo json_encode(["success" => false, "message" => "Product not found"]);
}
$stmt->close();
$conn->close();
?>






