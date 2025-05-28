<?php include('db_connect.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Real Estate Explorer</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    body {
      background: linear-gradient(to right, #dfe9f3, #ffffff);
      font-family: 'Segoe UI', sans-serif;
    }
    .hero {
      background: url('background.webp') no-repeat center center/cover;
      height: 60vh;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      text-shadow: 2px 2px 6px #000;
    }
    .hero h1 {
      font-size: 3rem;
      font-weight: bold;
    }
    .section {
      padding: 50px 0;
    }
    .card-city img {
      height: 180px;
      object-fit: cover;
      border-radius: 10px;
      transition: transform 0.3s ease;
    }
    .card-city:hover img {
      transform: scale(1.05);
    }
    .form-card {
      background-color: #f8f9fa;
      border-radius: 12px;
      padding: 25px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    h2.section-title {
      margin-bottom: 30px;
      font-weight: 600;
      color: #333;
    }
    .card h5 {
      font-weight: 600;
    }
    .card p {
      margin: 0;
      font-size: 0.95rem;
    }
    .property-card {
      cursor: pointer;
      transition: box-shadow 0.3s ease;
    }
    .property-card:hover {
      box-shadow: 0 0 15px rgba(138, 190, 246, 0.36);
    }
    /* Navbar brand font weight */
    .navbar-brand {
      font-weight: 700;
    }
    .form-card {
  background-color: #ffffff; /* lighter background for focus */
  border-radius: 12px;
  padding: 30px 35px;
  box-shadow: 0 6px 18px rgba(0, 0, 0, 0.12);
  transition: box-shadow 0.3s ease;
  max-width: 100%;
  margin: auto;
}

.form-card:hover {
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.18);
}

.form-card label {
  font-weight: 600;
  color: #444;
  margin-bottom: 8px;
  display: block;
}

.form-card input[type="text"],
.form-card input[type="number"],
.form-card select {
  width: 100%;
  padding: 10px 15px;
  border: 1.8px solid #ced4da;
  border-radius: 8px;
  font-size: 1rem;
  transition: border-color 0.3s ease;
  box-sizing: border-box;
}

.form-card input[type="text"]:focus,
.form-card input[type="number"]:focus,
.form-card select:focus {
  outline: none;
  border-color:rgb(126, 174, 246); /* Bootstrap primary blue */
  box-shadow: 0 0 8px rgba(13, 110, 253, 0.25);
}

.form-card button[type="submit"] {
  background-color:rgb(235, 166, 139);
  border: none;
  color: white;
  font-weight: 700;
  font-size: 1.1rem;
  padding: 12px 0;
  border-radius: 10px;
  cursor: pointer;
  transition: background-color 0.3s ease;
  width: 100%;
}

.form-card button[type="submit"]:hover {
  background-color:rgb(149, 222, 173);
}

@media (max-width: 576px) {
  .form-card {
    padding: 20px 20px;
  }
}
#transaction-section {
  background: linear-gradient(rgba(255, 255, 255, 0.63), rgba(255, 255, 255, 0.32)),
              url('background.jpg') no-repeat center center/cover;
  background-attachment: fixed;
  padding: 60px 0;
}

  </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm sticky-top">
  <div class="container">
    <a class="navbar-brand" href="#hero-section">Real Estate Explorer</a>
    <button
      class="navbar-toggler"
      type="button"
      data-bs-toggle="collapse"
      data-bs-target="#navbarNav"
      aria-controls="navbarNav"
      aria-expanded="false"
      aria-label="Toggle navigation"
    >
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item">
          <a class="nav-link active" aria-current="page" href="#hero-section">Home</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#cities-section">Cities</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#transaction-section">Transactions</a>
        </li>
        <li class="nav-item">
  <a class="nav-link text-danger fw-bold" href="admin.php" target="_blank">Admin Dashboard</a>
</li>

      </ul>
    </div>
  </div>
</nav>



<!-- Hero Section -->
<div id="hero-section" class="hero">
  <h1>Find Your Dream Property</h1>
</div>

<div class="container">

  <!-- Cities Section -->
  <section id="cities-section" class="section">
    <h2 class="section-title text-center">Popular Cities</h2>
    <div class="row">
      <?php
        $cityResult = $conn->query("SELECT * FROM city");
        while ($row = $cityResult->fetch_assoc()) {
          $cityName = $row['city_name'];
          $img = strtolower($cityName) . ".jpg";
          echo "
          <div class='col-md-3 col-6 mb-4 text-center'>
            <div class='card card-city border-0' data-bs-toggle='modal' data-bs-target='#modal-" . htmlspecialchars($cityName) . "' style='cursor:pointer;'>
              <img src='$img' class='w-100' alt='$cityName' />
              <div class='card-body p-2'>
                <h6 class='mb-0'>$cityName</h6>
                <small class='text-muted'>{$row['no_of_availabilities']} listings</small>
              </div>
            </div>
          </div>";

          $cityID = $row['id'];
          // Removed availability filter here
          $availResult = $conn->query("SELECT * FROM availabitities WHERE id = $cityID");

          echo "
          <!-- Modal for $cityName -->
          <div class='modal fade' id='modal-" . htmlspecialchars($cityName) . "' tabindex='-1'>
            <div class='modal-dialog modal-lg'>
              <div class='modal-content'>
                <div class='modal-header'>
                  <h5 class='modal-title'>Available Properties in $cityName</h5>
                  <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                </div>
                <div class='modal-body'>";

          if ($availResult->num_rows > 0) {
            echo "<div class='row'>";
            while ($avail = $availResult->fetch_assoc()) {
              echo "
              <div class='col-md-4 mb-4'>
                <div class='card h-100 shadow-sm border-0 property-card' 
                     data-city='" . htmlspecialchars($cityName, ENT_QUOTES) . "' 
                     data-rate='{$avail['rate']}'
                     data-aid='{$avail['a_id']}'
                     style='cursor:pointer;'>
                  <div class='card-body'>
                    <h5 class='card-title text-primary'>{$avail['type']}</h5>
                    <p class='card-text'>
                      <strong>Mode:</strong> {$avail['mode']}<br />
                      <strong>Rate:</strong> ₹" . number_format($avail['rate']) . "
                    </p>
                  </div>
                </div>
              </div>";
            }
            echo "</div>";
          } else {
            echo "<p class='text-muted'>No properties available in this city yet.</p>";
          }

          echo "
                </div>
              </div>
            </div>
          </div>";
        }
      ?>
    </div>
  </section>

  <!-- Add Transaction Form -->
  <section id="transaction-section" class="section bg-light">
    <h2 class="section-title text-center">Buy Yours</h2>
    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="form-card">
          <form action="transaction.php" method="post" id="transactionForm">
            <div class="mb-3">
              <label class="form-label">Customer Name</label>
              <input type="text" name="cust_name" class="form-control" required />
            </div>
            <div class="mb-3">
              <label class="form-label">City</label>
              <select name="city_name" class="form-select" required>
                <?php
                  $cities = $conn->query("SELECT DISTINCT city_name FROM city");
                  while ($city = $cities->fetch_assoc()) {
                    echo "<option value='{$city['city_name']}'>{$city['city_name']}</option>";
                  }
                ?>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Rate</label>
              <input type="number" name="rate" class="form-control" required />
            </div>
            <!-- Hidden input for availability ID -->
            <input type="hidden" name="a_id" id="a_id" />
            <button type="submit" class="btn btn-primary w-100">Submit Transaction</button>
          </form>
        </div>
      </div>
    </div>
  </section>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
  document.querySelectorAll(".property-card").forEach((card) => {
    card.addEventListener("click", () => {
      const city = card.getAttribute("data-city");
      const rate = card.getAttribute("data-rate");
      const aid = card.getAttribute("data-aid");

      // Set city dropdown value
      const citySelect = document.querySelector('select[name="city_name"]');
      if (citySelect) {
        citySelect.value = city;
      }

      // Set rate input value
      const rateInput = document.querySelector('input[name="rate"]');
      if (rateInput) {
        rateInput.value = rate;
      }

      // Set hidden a_id input value
      const aIdInput = document.querySelector('input[name="a_id"]');
      if (aIdInput) {
        aIdInput.value = aid;
      }

      // Scroll to transaction form
      const formSection = document.querySelector("#transaction-section");
      if (formSection) {
        formSection.scrollIntoView({ behavior: "smooth" });
      }

      // Close the modal
      const modal = card.closest(".modal");
      if (modal) {
        const modalInstance = bootstrap.Modal.getInstance(modal);
        if (modalInstance) {
          modalInstance.hide();
        }
      }
    });
  });
</script>

</body>
</html>
