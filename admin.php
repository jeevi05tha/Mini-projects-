<?php
include('db_connect.php');

// Handle City Insert
if (isset($_POST['add_city'])) {
    $city_name = $conn->real_escape_string($_POST['city_name']);
    $no_of_availabilities = intval($_POST['no_of_availabilities']);

    $conn->query("INSERT INTO city (city_name, no_of_availabilities) VALUES ('$city_name', $no_of_availabilities)");
    echo "<div class='alert alert-success'>City added successfully.</div>";
}

// Handle Availability Insert
if (isset($_POST['add_availability'])) {
    $city_id = intval($_POST['city_id']);
    $type = $conn->real_escape_string($_POST['type']);
    $mode = $conn->real_escape_string($_POST['mode']);
    $rate = floatval($_POST['rate']);
    $available = isset($_POST['available']) ? 1 : 0;

    $conn->query("INSERT INTO availabitities (id, type, mode, rate, available) VALUES ($city_id, '$type', '$mode', $rate, $available)");

    // Update the no_of_availabilities count for the city
    $conn->query("UPDATE city SET no_of_availabilities = no_of_availabilities + 1 WHERE id = $city_id");

    echo "<div class='alert alert-success'>Availability added successfully.</div>";
}

// Fetch cities and availabilities for display and dropdown
$cities = $conn->query("SELECT * FROM city ORDER BY city_name");
$availabilities = $conn->query("
    SELECT a.a_id, c.city_name, a.type, a.mode, a.rate, a.available 
    FROM availabitities a
    JOIN city c ON a.id = c.id
    ORDER BY c.city_name, a.type
");

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <title>Admin Panel - Manage Cities and Properties</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>

<div class="container mt-4">

  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Admin Panel</h1>
    <a href="index.php" class="btn btn-secondary">Back to Home</a>
  </div>

  <!-- Display Cities Table -->
  <h2>Existing Cities</h2>
  <table class="table table-striped table-bordered">
    <thead class="table-dark">
      <tr>
        <th>ID</th>
        <th>City Name</th>
        <th>Number of Availabilities</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($city = $cities->fetch_assoc()): ?>
      <tr>
        <td><?= $city['id'] ?></td>
        <td><?= htmlspecialchars($city['city_name']) ?></td>
        <td><?= $city['no_of_availabilities'] ?></td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>

  <hr />

  <!-- Display Availabilities Table -->
  <h2>Existing Property Listings (Availabilities)</h2>
  <table class="table table-striped table-bordered">
    <thead class="table-dark">
      <tr>
        <th>Property ID (a_id)</th>
        <th>City Name</th>
        <th>Type</th>
        <th>Mode</th>
        <th>Rate (₹)</th>
        <th>Available</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($avail = $availabilities->fetch_assoc()): ?>
      <tr>
        <td><?= $avail['a_id'] ?></td>
        <td><?= htmlspecialchars($avail['city_name']) ?></td>
        <td><?= htmlspecialchars($avail['type']) ?></td>
        <td><?= htmlspecialchars($avail['mode']) ?></td>
        <td><?= number_format($avail['rate']) ?></td>
        <td><?= $avail['available'] ? 'Yes' : 'No' ?></td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>

  <hr />

  <!-- Add New City Form -->
  <h2>Add New City</h2>
  <form method="POST" class="mb-5">
    <div class="mb-3">
      <label for="city_name" class="form-label">City Name</label>
      <input type="text" class="form-control" id="city_name" name="city_name" required />
    </div>
    <div class="mb-3">
      <label for="no_of_availabilities" class="form-label">Number of Availabilities</label>
      <input type="number" class="form-control" id="no_of_availabilities" name="no_of_availabilities" min="0" value="0" required />
    </div>
    <button type="submit" name="add_city" class="btn btn-primary">Add City</button>
  </form>

  <hr />

  <!-- Add New Availability Form -->
  <h2>Add New Availability (Property)</h2>
  <form method="POST">
    <div class="mb-3">
      <label for="city_id" class="form-label">Select City</label>
      <select id="city_id" name="city_id" class="form-select" required>
        <option value="">Select City</option>
        <?php
          // Re-fetch cities for dropdown (in case new city was added)
          $cityOptions = $conn->query("SELECT id, city_name FROM city ORDER BY city_name");
          while ($city = $cityOptions->fetch_assoc()):
        ?>
          <option value="<?= $city['id'] ?>"><?= htmlspecialchars($city['city_name']) ?></option>
        <?php endwhile; ?>
      </select>
    </div>
    <div class="mb-3">
      <label for="type" class="form-label">Property Type</label>
      <input type="text" id="type" name="type" class="form-control" placeholder="Apartment, Villa, etc." required />
    </div>
    <div class="mb-3">
      <label for="mode" class="form-label">Mode</label>
      <select id="mode" name="mode" class="form-select" required>
        <option value="">Select Mode</option>
        <option value="Sale">Sale</option>
        <option value="Rent">Rent</option>
      </select>
    </div>
    <div class="mb-3">
      <label for="rate" class="form-label">Rate (₹)</label>
      <input type="number" id="rate" name="rate" class="form-control" min="0" required />
    </div>
    <div class="form-check mb-3">
      <input class="form-check-input" type="checkbox" value="1" id="available" name="available" checked>
      <label class="form-check-label" for="available">Available</label>
    </div>
    <button type="submit" name="add_availability" class="btn btn-success">Add Availability</button>
  </form>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
