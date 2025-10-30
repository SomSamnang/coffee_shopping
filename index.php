<?php
require_once('db_connect.php');

// Fetch products with category names
$sql = "SELECT p.product_id, p.name, p.price, c.name AS category
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        ORDER BY c.name, p.name";
$result = $conn->query($sql);

$products = [];
$categories = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $group = $row['category'] ?? 'Other';
        $products[$group][] = $row;
        if (!in_array($group, $categories)) $categories[] = $group;
    }
    $result->free();
}

// Assign pastel color per category
$categoryColors = [];
foreach($categories as $cat){
    $r = rand(230, 255); $g = rand(230, 255); $b = rand(230, 255);
    $categoryColors[$cat] = "rgb($r,$g,$b)";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>☕ Coffee Shop POS</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<style>
body { 
    background: #f5f7fa; 
    font-family: 'Poppins', sans-serif; 
    color: #333; 
    margin: 0; 
}

/* Navbar */
.navbar { 
    background: linear-gradient(90deg, #0d6efd, #6610f2);
    box-shadow: 0 3px 8px rgba(0,0,0,0.1); 
}
.navbar-brand { font-weight: 700; font-size: 1.4rem; color: #fff !important; }
.navbar-nav .nav-link { 
    color: #fff; font-weight: 500; border-radius: 6px; padding: 6px 12px; 
}
.navbar-nav .nav-link:hover { background: rgba(255,255,255,0.25); }

/* Sticky Filters (Category & Search) */
.sticky-filters {
    position: sticky; top: 59px; z-index: 998;
    background: #ffffff;
    padding: 10px 15px;
    solid #e0e0e0;
    display: flex; justify-content: space-between; align-items: center;
    flex-wrap: wrap; gap: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

/* Category Buttons */
.category-nav { display: flex; gap: 10px; overflow-x: auto; padding: 5px 0; }
.category-nav::-webkit-scrollbar { height: 4px; }
.category-nav::-webkit-scrollbar-thumb { background: #ccc; border-radius: 10px; }

.category-nav button {
    border: none; background: #f1f3f5; 
    padding: 6px 14px; border-radius: 20px; 
    font-weight: 500; cursor: pointer; 
    transition: all 0.2s;
}
.category-nav button.active, .category-nav button:hover {
    background: #0d6efd; color: #fff;
}

/* Secondary Sticky (Clock) */
.sticky-filters1 {
    position: sticky; top: 125px; z-index: 997;
    background: #ffffff;
    padding: 6px 15px;
    display: flex; justify-content:left; align-items: center;
    border-bottom: 1px solid #e0e0e0;
    font-weight: 500; color: #555;
}

/* Product Cards */
.product-card { 
    border-radius: 12px;
    text-align: center;
    padding: 25px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    transition: transform 0.2s, box-shadow 0.2s; 
}
.product-card:hover { transform: translateY(-5px); box-shadow: 0 5px 12px rgba(0,0,0,0.12); }
.card-title { font-weight: 600; font-size: 1rem; margin-bottom: 5px; }
.card-price { font-weight: 700; color: #198754; }

/* Group Title */
.group-title { 
    font-weight: 700; font-size: 1.3rem; 
    color: #0d6efd; margin: 30px 0 15px; 
}

/* Search Box */
.input-group { max-width: 250px; }
.input-group input { border-radius: 20px 0 0 20px; }
.input-group-text { border-radius: 0 20px 20px 0; }

/* Hide Scrollbar on Body */
html, body { scrollbar-width: thin; }
body::-webkit-scrollbar { width: 8px; }
body::-webkit-scrollbar-thumb { background: #ccc; border-radius: 10px; }
/* Hide horizontal scroll, keep content responsive */
.table-responsive {
    overflow-x: hidden !important; /* hides horizontal scrollbar */
    -webkit-overflow-scrolling: touch;
}
/* Hide scrollbar but allow scroll */
html, body {
    height: 100%;
    overflow: auto;
    scrollbar-width: none;
}
body::-webkit-scrollbar {
    display: none;
}
</style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark sticky-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">☕ Coffee Shop</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
            <ul class="navbar-nav gap-2">
                <li class="nav-item"><a class="nav-link" href="index.php"><i class="bi bi-house"></i> Home</a></li>
                <li class="nav-item"><a class="nav-link" href="product.php"><i class="bi bi-plus-circle"></i> Add Product</a></li>
                <li class="nav-item"><a class="nav-link" href="category_list.php"><i class="bi bi-list-ul"></i> Categories</a></li>
                <li class="nav-item"><a class="nav-link" href="orders.php"><i class="bi bi-basket"></i> Orders</a></li>
            </ul>
        </div>
    </div>
</nav>

<!-- Category & Search -->
<div class="sticky-filters">
    <div class="category-nav">
        <button class="active" data-category="all">All</button>
        <?php foreach($categories as $cat): ?>
            <button data-category="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></button>
        <?php endforeach; ?>
    </div>
    <div class="input-group">
        <input type="text" id="searchBox" class="form-control" placeholder="Search products...">
        <span class="input-group-text"><i class="bi bi-search"></i></span>
    </div>
</div>

<!-- Clock Bar -->
<div class="sticky-filters1">
    <div id="clock"></div>
</div>

<!-- Product Grid -->
<div class="container my-4">
    <div id="productContainer" class="row mt-0 g-3">
        <?php foreach($products as $group => $items): ?>
            <div class="col-12 group" data-group="<?= htmlspecialchars($group) ?>">
                <div class="group-title"><?= htmlspecialchars($group) ?></div>
                <div class="row g-3">
                    <?php foreach($items as $product): ?>
                        <div class="col-6 col-md-3">
                            <div class="product-card" 
                                 data-name="<?= strtolower($product['name']) ?>" 
                                 style="background-color: <?= $categoryColors[$group] ?>;">
                                <div class="card-title"><?= htmlspecialchars($product['name']) ?></div>
                                <div class="card-price">$<?= number_format($product['price'],2) ?></div>
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

// Category Filter
const categoryButtons = document.querySelectorAll('.category-nav button');
const productGroups = document.querySelectorAll('.group');
categoryButtons.forEach(btn => {
    btn.addEventListener('click', () => {
        categoryButtons.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        const category = btn.dataset.category;
        productGroups.forEach(group => {
            group.style.display = (category === 'all' || group.dataset.group === category) ? '' : 'none';
        });
    });
});

// Search Filter
const searchBox = document.getElementById('searchBox');
searchBox.addEventListener('input', () => {
    const query = searchBox.value.toLowerCase();
    productGroups.forEach(group => {
        let visible = false;
        group.querySelectorAll('.product-card').forEach(card => {
            const name = card.dataset.name;
            card.style.display = name.includes(query) ? '' : 'none';
            if(name.includes(query)) visible = true;
        });
        group.style.display = visible ? '' : 'none';
    });
});
</script>

</body>
</html>
<?php $conn->close(); ?>
