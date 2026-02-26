<?php

require_once '../connection.php';
$query = "SELECT * FROM produk";
$sql = mysqli_query($connection, $query);
$nomer = 0;

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Farm Shop - Items Management</title>
  <link rel="stylesheet" href="index.css">
  <script src="index.js" defer></script>
  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</head>

<body>
  <!-- ===============================
  NAVBAR (HAMBURGER UNTUK MOBILE)
  ================================ -->
  <nav class="navbar navbar-dark d-md-none" style="background: #151515;">
    <div class="container-fluid">
      <a class="navbar-brand ms-0" href="#">🧑‍🌾 Farm Shop</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileMenu">
        <span class="navbar-toggler-icon"></span>
      </button>
    </div>
  </nav>

  <!-- ===============================
      OFFCANVAS MENU (MOBILE HAMBURGER)
    ================================ -->
  <div class="offcanvas offcanvas-end text-bg-danger" id="mobileMenu">
    <div class="offcanvas-header" style="background: #151515;">
      <h5 class="offcanvas-title">🧑‍🌾 Farm Shop</h5>
      <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body burgerBar">
      <a href="#"><i class="bi bi-box me-3"></i>Products</a>
      <a href="#"><i class="bi bi-columns-gap me-3"></i></i>Dashboard</a>
      <a href="#"><i class="bi bi-truck me-3"></i>Suppliers</a>
      <a href="#"><i class="bi bi-diagram-2 me-3"></i>Categories</a>
      <a href="#"><i class="bi bi-currency-dollar me-3"></i>Selling</a>
      <a href="#"><i class="bi bi-box-arrow-right me-3"></i>Logout</a>
    </div>
  </div>

  <div class="container-fluid">
    <div class="row">
      <!-- ===============================
          SIDEBAR (HILANG DI MOBILE)
        ================================ -->
      <nav class="sidebar col-md-2 col-lg-2">
        <h4 class="ms-3 mb-4 fw-bold">🧑‍🌾 Farm Shop</h4>
        <a href="#"><i class="bi bi-box me-3"></i>Products</a>
        <a href="#"><i class="bi bi-columns-gap me-3"></i></i>Dashboard</a>
        <a href="#"><i class="bi bi-truck me-3"></i>Suppliers</a>
        <a href="#"><i class="bi bi-diagram-2 me-3"></i>Categories</a>
        <a href="#"><i class="bi bi-currency-dollar me-3"></i>Selling</a>
        <a href="#"><i class="bi bi-box-arrow-right me-3"></i>Logout</a>
      </nav>

      <!-- ===============================
          MAIN CONTENT
        ================================ -->
      <main class="col-md-10 col-lg-10 col-sm-12 p-4">
        <div class="row g-4">
          <!-- FORM ADD ITEM -->
          <div class="col-lg-4 col-md-6 col-sm-12">
            <div class="card shadow rounded-4">
              <div class="card-header fw-bold rounded-top-3">➕ Add New Product</div>
              <div class="card-body">
                <form method="POST" action="../process.php" enctype="multipart/form-data">
                  <input required type="hidden" name="action" value="add">
                  <div class="mb-4">
                    <label for="code" class="form-label">Code</label>
                    <input required type="text" class="form-control" id="code" name="code" placeholder="Enter product code">
                    <small id="warningCodeAdd" style="color: red; display: none;">⚠️ Maximum 5 characters only</small>
                  </div>
                  <div class="mb-4">
                    <label for="name" class="form-label">Name</label>
                    <input required type="text" class="form-control" id="name" name="name" placeholder="Enter product name">
                    <small id="warningNameAdd" style="color: red; display: none;">⚠️ Maximum 20 characters only</small>
                  </div>
                  <div class="mb-4">
                    <label for="unit" class="form-label">Unit</label>
                    <select class="form-select" aria-label="Default select example" name="unit" id="unit">
                      <option value="pcs">pcs</option>
                      <option value="set">set</option>
                    </select>
                  </div>
                  <div class="mb-4">
                    <label for="price" class="form-label">Price</label>
                    <input required type="number" class="form-control" id="price" name="price" placeholder="Enter product price">
                    <small id="warningPriceAdd" style="color: red; display: none;">⚠️ Maximum 12 characters only</small>
                  </div>
                  <div class="mb-4">
                    <label for="image" class="form-label">Image</label>
                    <input required type="file" class="form-control" id="image" name="image" accept="image/*">
                  </div>
                  <button type="submit" class="btn w-100 fw-semibold" style="background: #151515; color: white;">➕ Add Product</button>
                </form>
              </div>
            </div>
          </div>

          <!-- TABEL ITEMS -->
          <div class="p-0 pb-3 rounded-4 shadow col-lg-8 col-md-6 col-sm-12">
            <div class="card">
              <div class="card-header d-flex justify-content-between align-items-center rounded-top-3">
                <span class="fw-bold">📦 Current Stock Products</span>
              </div>
              <div class="card-body p-0">
                <!-- TABLE FOR DESKTOP -->
                <div class="table-responsive d-none d-md-block mx-3 mt-2">
                  <table class="table table-striped mb-0 align-middle">
                    <thead class="table-light">
                      <tr>
                        <th scope="col">No</th>
                        <th scope="col">Image</th>
                        <th scope="col">Name</th>
                        <th scope="col">Unit</th>
                        <th scope="col">Code</th>
                        <th scope="col">Price</th>
                        <th scope="col">Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php while ($result = mysqli_fetch_assoc($sql)) : ?>
                        <tr>
                          <td><?= ++$nomer ?>.</td>
                          <td>
                            <img src="../../Assets/Images/<?= htmlspecialchars($result['gambar']) ?>" alt="Product Image" style="max-width:150px;">
                          </td>
                          <td><?= $result['nama'] ?></td>
                          <td><?= $result['satuan'] ?></td>
                          <td><?= $result['kode'] ?></td>
                          <td><?= $result['harga'] ?></td>
                          <td>
                            <a href="../edit/edit.php?edit=<?= $result['id'] ?>" type="button" class="btn btn-success">
                              <i class="bi bi-pencil-fill"></i>
                            </a>
                            <a href="../process.php?remove=<?= $result['id'] ?>" type="button" class="btn btn-danger" onclick="return confirm('Are you sure want to delete this item?')">
                              <i class="bi bi-trash3-fill"></i>
                            </a>
                          </td>
                        </tr>
                      <?php endwhile; ?>
                    </tbody>
                  </table>
                </div>
                <!-- CARD LIST FOR MOBILE -->
                <div class="d-block d-md-none">
                  <?php
                  // Ambil ulang data produk untuk tampilan mobile
                  $sql_mobile = mysqli_query($connection, "SELECT * FROM produk");
                  $no_mobile = 0;
                  while ($result = mysqli_fetch_assoc($sql_mobile)) :
                  ?>
                    <div class="mb-4">
                      <div class="border rounded p-3 mb-2 bg-light">
                        <div class="fw-bold">
                          <?= htmlspecialchars($result['nama']) ?>
                          <span class="badge bg-secondary float-end">#<?= ++$no_mobile ?></span>
                        </div>
                        <div class="float-start me-3">
                          <img src="../../Assets/Images/<?= htmlspecialchars($result['gambar']) ?>" alt="Product Image" style="max-width:100px;" class="my-2">
                        </div>
                        <div><strong>Code :</strong> <?= htmlspecialchars($result['kode']) ?></div>
                        <div><strong>Unit :</strong> <?= htmlspecialchars($result['satuan']) ?></div>
                        <div><strong>Price :</strong> <?= htmlspecialchars($result['harga']) ?></div>
                        <div class="mt-3">
                          <a href="../edit/edit.php?edit=<?= $result['id'] ?>" class="btn btn-success btn-sm">
                            <i class="bi bi-pencil-fill"></i> Edit
                          </a>
                          <a href="../process.php?remove=<?= $result['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure want to delete this item?')">
                            <i class="bi bi-trash3-fill"></i> Delete
                          </a>
                        </div>
                      </div>
                    </div>
                  <?php endwhile; ?>
                </div>
              </div>
            </div>
          </div>
      </main>
    </div>
  </div>
</body>

</html>