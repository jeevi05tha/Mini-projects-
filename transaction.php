<?php
include('db_connect.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cust_name = trim($_POST['cust_name'] ?? '');
    $city_name = trim($_POST['city_name'] ?? '');
    $rate = (int)($_POST['rate'] ?? 0);
    $a_id = (int)($_POST['a_id'] ?? 0);

    if (empty($cust_name) || empty($city_name) || $rate <= 0 || $a_id <= 0) {
        die("Please provide all required fields correctly.");
        exit;
    }

    // Check if property is still available
    $stmtCheck = $conn->prepare("SELECT * FROM availabitities WHERE a_id = ? AND available = 1");
    $stmtCheck->bind_param("i", $a_id);
    $stmtCheck->execute();
    $result = $stmtCheck->get_result();
    if ($result->num_rows === 0) {
        die("<script>alert('This property is already sold!'); window.location.href='index.php';</script>");
        exit;
    }
    $stmtCheck->close();

    // Insert transaction with a_id
    $stmtInsert = $conn->prepare("INSERT INTO transactions (cust_name, city_name, rate, a_id) VALUES (?, ?, ?, ?)");
    $stmtInsert->bind_param("ssii", $cust_name, $city_name, $rate, $a_id);
    if (!$stmtInsert->execute()) {
        die("Failed to insert transaction.");
    }
    $stmtInsert->close();

    // Mark property as unavailable
    $stmtUpdate = $conn->prepare("UPDATE availabitities SET available = 0 WHERE a_id = ?");
    $stmtUpdate->bind_param("i", $a_id);
    $stmtUpdate->execute();
    $stmtUpdate->close();

    echo "<script>alert('Transaction successful!'); window.location.href = 'index.php';</script>";
    $conn->close();
} else {
    die("Invalid request method.");
}
?>
