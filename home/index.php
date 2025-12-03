<?php
session_start();
require_once('../connection/db_connect.php');

// Logged-in user info
$currentUser = $_SESSION['username'] ?? null;
$role = $_SESSION['role'] ?? null;

// Fetch active products grouped by category
$sql = "SELECT p.product_id, p.name, p.price, p.description, p.image, c.name AS category
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.status='active'
        ORDER BY c.name, p.name";
$result = $conn->query($sql);

$products_by_category = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $category = $row['category'] ?? 'Other';
        $products_by_category[$category][] = $row;
    }
}

// Category colors
$categoryColors = [
    'Hot Coffee'   => '#FFE5B4',
    'Frappe'       => '#D1C4E9',
    'Iced Tea'     => '#B3E5FC',
    'Iced Latte'   => '#F8BBD0',
    'Soft Drinks'  => '#C8E6C9',
    'Other'        => '#E0E0E0'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Coffee POS</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="../css/index.css">

</head>
<body>
<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container">
    <a class="navbar-brand fw-bold" href="/home/index.php"><i class="bi bi-cup-hot"></i> Relax Coffee</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
      <ul class="navbar-nav gap-2">
        <li class="nav-item"><a class="nav-link active" href="../home/index.php"><i class="bi bi-house"></i> Home</a></li>
        <li class="nav-item"><a class="nav-link" href="../employees/employee_list.php"><i class="bi bi-people"></i> Employee List</a></li>
        <li class="nav-item"><a class="nav-link" href="../products/product.php"><i class="bi bi-plus-circle"></i> Products</a></li>
        <li class="nav-item"><a class="nav-link" href="../categories/category_list.php"><i class="bi bi-list-ul"></i> Categories</a></li>
        <li class="nav-item"><a class="nav-link" href="../orders/orders.php"><i class="bi bi-basket"></i> Orders</a></li>
        <li class="nav-item"><a class="nav-link" href="../orders/orders_history.php"><i class="bi bi-clock-history"></i> Orders History</a></li>

        <!-- User Dropdown / Login -->
        <?php if($currentUser): ?>
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" style="color:white;">
                <i class="bi bi-person-circle me-1" style="color:yellow;"></i>
                <?= htmlspecialchars($currentUser) ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="../my_profile/my_profile.php"><i class="bi bi-person me-2" style="color:blue;"></i> Profile</a></li>
                <?php if($role === 'admin'): ?>
                <li><a class="dropdown-item" href="../users/user_list.php"><i class="bi bi-people-fill me-2" style="color:green;"></i> Users</a></li>
                <?php endif; ?>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="../users/logout.php"><i class="fa fa-power-off me-2"style="color:red;"></i></i> Logout</a></li>
            </ul>
        </li>
        <?php else: ?>
        <li class="nav-item"><a class="nav-link btn btn-outline-light btn-sm" href="../users/login.php"><i class="bi bi-box-arrow-in-right me-2"></i> Login</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<!-- Sticky filter -->
<div class="container mt-3">
<div class="sticky-filter  shadow-sm p-3 rounded">
    <h3 class="text-center fw-bold text-secondary">☕ Coffee Menu</h3>
    <div id="clock" class="text-center mb-2"></div>

    <!-- Category Buttons -->
    <div class="category-nav mb-2 text-center">
        <button class="btn btn-outline-dark active" data-category="all">All</button>
        <?php foreach($products_by_category as $category => $products): ?>
        <button class="btn btn-outline-dark" data-category="<?= htmlspecialchars($category) ?>">
            <?= htmlspecialchars($category) ?>
        </button>
        <?php endforeach; ?>
    </div>

    <!-- Search -->
    <div class="text-center mb-3">
        <div class="position-relative w-75 mx-auto">
            <input type="text" id="searchBox" class="form-control" placeholder="Search coffee...">
            <i class="bi bi-search position-absolute" style="right:15px;top:50%;transform:translateY(-50%); font-size:18px; color:#777;"></i>
        </div>
    </div>
</div>

<!-- Product Grid -->
<div class="container mt-3">
  <div class="row g-3" id="productContainer">
    <?php foreach($products_by_category as $category => $products): ?>
    <div class="col-12 group" data-category="<?= htmlspecialchars($category) ?>">
      <h5 class="fw-bold mt-3 mb-2 text-primary"><?= htmlspecialchars($category) ?></h5>
      <div class="row g-3">
        <?php foreach($products as $product):
          $color = $categoryColors[$category] ?? '#E0E0E0';
          $imgPath = !empty($product['image']) ? '../uploads/' . htmlspecialchars($product['image']) : '../uploads/default.jpg';
        ?>
        <div class="col-6 col-md-3">
          <div class="product-card" style="background-color: <?= $color ?>;"
               data-name="<?= strtolower($product['name']) ?>"
               data-desc="<?= strtolower($product['description'] ?? '') ?>">
            <img src="<?= $imgPath ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="card-img">
            <div class="card-title"><?= htmlspecialchars($product['name']) ?></div>
            <div class="card-price">$<?= number_format($product['price'],2) ?></div>
            <?php if(!empty($product['description'])): ?>
            <div class="card-desc"><?= htmlspecialchars($product['description']) ?></div>
            <?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Clock
function updateClock() {
  const now = new Date();
  const dateStr = now.toLocaleDateString(undefined, { weekday:'short', year:'numeric', month:'short', day:'numeric' });
  const timeStr = now.toLocaleTimeString();
  document.getElementById('clock').innerText = `${dateStr} | ${timeStr}`;
}
setInterval(updateClock, 1000);
updateClock();

// Category filter
const categoryButtons = document.querySelectorAll('.category-nav button');
const productGroups = document.querySelectorAll('.group');
categoryButtons.forEach(btn => {
  btn.addEventListener('click', () => {
    categoryButtons.forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    const category = btn.dataset.category;
    productGroups.forEach(group => {
      group.style.display = (category === 'all' || group.dataset.category === category) ? '' : 'none';
    });
  });
});

// Search filter
const searchBox = document.getElementById('searchBox');
searchBox.addEventListener('input', () => {
  const query = searchBox.value.toLowerCase();
  productGroups.forEach(group => {
    let visible = false;
    group.querySelectorAll('.product-card').forEach(card => {
      const name = card.dataset.name;
      const desc = card.dataset.desc || '';
      const match = name.includes(query) || desc.includes(query);
      card.style.display = match ? '' : 'none';
      if(match) visible = true;
    });
    group.style.display = visible ? '' : 'none';
  });
});
</script>

</body>
</html>
<?php $conn->close(); ?>
