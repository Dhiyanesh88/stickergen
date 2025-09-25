<?php
// ---------------------------
// Sticker/PRN Generator Script
// ---------------------------
session_start();
// Enable full error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

include __DIR__ . "/barcode.php";

// --- Database Connection ---
$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "stickers_db";
$port       = 3307;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, X-Requested-With");

// Handle CORS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$conn = new mysqli($servername, $username, $password, $dbname, $port);
if ($conn->connect_error) {
    http_response_code(500);
    die("Database connection failed: " . $conn->connect_error);
}

// --- Fetch PRN Template ---
$templateSql = "SELECT template FROM sticker_templates LIMIT 1";
$templateResult = $conn->query($templateSql);

if (!$templateResult || $templateResult->num_rows === 0) {
    http_response_code(500);
    die("No PRN template found in database.");
}

$templateRow = $templateResult->fetch_assoc();
$template = $templateRow['template'];

// --- Helper Functions ---
function generatePrn($template, $row) {
    $date = date('Y-m-d');
    // Ensure barcode is 13 digits
    $barcodeData = $row['barcode'] ?: str_pad(preg_replace('/\D/', '', $row['product_name']), 13, '0');

    return str_replace(
        ["{{PRODUCT_NAME}}", "{{CATEGORY}}", "{{PRICE}}", "{{BARCODE_DATA}}", "{{DATE}}"],
        [$row['product_name'], $row['category'], $row['price'], $barcodeData, $date],
        $template
    );
}

function sanitizeFileName($name) {
    return preg_replace('/[^A-Za-z0-9_\-]/', '_', $name);
}

// Initialize session array if not set
if (!isset($_SESSION['previews']) || !is_array($_SESSION['previews'])) {
    $_SESSION['previews'] = [];
}

// Handle "Clear All"
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset'])) {
    $_SESSION['previews'] = [];
}

// Only show previews if there are any
$previewHtml = !empty($_SESSION['previews']) ? implode('', $_SESSION['previews']) : '';


if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['reset'])) {
    $barcodeInput = trim($_POST['barcode'] ?? '');
    if ($barcodeInput === '') {
        $_SESSION['previews'][] = "";
        $previewHtml = implode('', $_SESSION['previews']);
    } else {
        $barcodeLike  = '%' . $barcodeInput . '%';

        $sql = "SELECT * FROM products WHERE barcode LIKE ? ORDER BY product_name ASC LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $barcodeLike);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc(); // fetch one row

            $prnContent = generatePrn($template, $row);
            if (!is_dir("stickers")) mkdir("stickers", 0755, true);

            $fileName = "stickers/" . sanitizeFileName($row['product_name']) . ".prn";
            file_put_contents($fileName, $prnContent);

            $date = date('Y-m-d');
            $barcodeData = $row['barcode'] ?: str_pad(substr(preg_replace('/\D/', '', $row['product_name']) . "0000000000000", 0, 13), 13, '0');

            $newPreview  = "<div class='preview' style='border:1px solid #ccc; margin:10px; padding:10px'>";
            $newPreview .= "<h3>Preview for: " . htmlspecialchars($row['product_name']) . "</h3>";
            $newPreview .= "<p><b>Category:</b> " . htmlspecialchars($row['category']) . "<br>";
            $newPreview .= "<b>Price:</b> ₹" . htmlspecialchars($row['price']) . "<br>";
            $newPreview .= "<b>Date:</b> $date</p>";

            if (function_exists('drawEAN13')) {
                $newPreview .= drawEAN13($barcodeData);
            } elseif (function_exists('generateBarcode')) {
                $newPreview .= generateBarcode($barcodeData);
            } else {
                $newPreview .= "<p><b>Barcode:</b> $barcodeData</p>";
            }

            $newPreview .= "<p><i>Saved as:</i> " . htmlspecialchars($fileName) . "</p>";
            $newPreview .= "</div>";

            // Append and save in session
            $_SESSION['previews'][] = $newPreview;
            $previewHtml = implode('', $_SESSION['previews']);
        } else {
            $_SESSION['previews'][] = "<p>No matching product found in database.</p>";
            $previewHtml = implode('', $_SESSION['previews']);
        }

        $stmt->close();
    }
}

$conn->close();
?>
<form method="POST" action="prnd.php">
  <!-- <input type="text" name="product_name" placeholder="Product name"> -->
  <input type="text" name="barcode" placeholder="Barcode">
  <button type="submit">Generate</button>
</form>
<form method="POST" action="prnd.php">
  <input type="hidden" name="reset" value="1">
  <button type="submit">Clear All</button>
</form>

<!-- Output Preview -->
<?php if ($previewHtml): ?>
    <?= $previewHtml ?>
<?php endif; ?>
