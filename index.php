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
        if (!in_array($group, $categories)) {
            $categories[] = $group;
        }
    }
    $result->free();
}

// Function to generate a random pastel color
function randomPastelColor() {
    $r = rand(180, 255);
    $g = rand(180, 255);
    $b = rand(180, 255);
    return "rgb($r,$g,$b)";
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
    background-color: #f9fafb; 
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
    margin: 0;
    color: #333;
}

/* Sticky Header */
header {
    position: sticky;
    top: 0;
    z-index: 1000;
    background-color: #1700c7ff;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    padding: 12px 25px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
header h1 {
    font-size: 1.5rem;
    color: #fff;
    margin: 0;
    font-weight: 700;
}
header .nav-buttons a {
    text-decoration: none;
    color: #0d6efd;
    background: #eef4ff;
    padding: 8px 14px;
    border-radius: 6px;
    margin-left: 8px;
    font-weight: 500;
    transition: all 0.2s;
}
header .nav-buttons a:hover {
    background: #0d6efd;
    color: #fff;
}

/* Sticky Filters */
.sticky-filters {
    position: sticky;
    top: 70px;
    background-color: #f9fafb;
    z-index: 999;
    padding: 10px 0;
    border-bottom: 1px solid #e0e0e0;
}

/* Category Navigation */
.category-nav {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 10px;
}
.category-nav button {
    border: none;
    background: #eaefe9ff;
    padding: 8px 16px;
    border-radius: 20px;
    font-weight: 500;
    transition: all 0.2s;
    cursor: pointer;
}
.category-nav button.active,
.category-nav button:hover {
    background: #0d6efd;
    color: #fff;
}

/* Product Cards */
.product-card {
    border-radius: 12px;
    transition: transform 0.2s, box-shadow 0.2s;
    color: #333;
}
.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}
.card-body {
    padding: 18px;
}
.card-title {
    font-size: 1.05rem;
    font-weight: 600;
}
.card-price {
    font-size: 1.1rem;
    font-weight: 700;
    color: #198754;
}

/* Group Title */
.group-title {
    font-weight: 700;
    font-size: 1.3rem;
    color: #0d6efd;
    margin-top: 40px;
    margin-bottom: 15px;
}

/* Clock */
#clock {
    font-size: 0.95rem;
    font-weight: 500;
    color: #6c757d;
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

<header>
    <h1>☕COFFEE SHOPPING</h1>
    <div class="nav-buttons">
        <a href="index.php">Home</a>
        <a href="product.php">+ Product</a>
        <a href="category_list.php">+ Category</a>
        <a href="orders.php">Orders</a>
    </div>
</header>

<div class="container my-4">

    <!-- Sticky Filter Bar -->
    <div class="sticky-filters">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 px-1">
            <div id="clock"></div>
            <div class="input-group" style="max-width:400px;">
                <input type="text" id="searchBox" class="form-control" placeholder="Search products...">
            </div>
        </div>
        <div class="category-nav mt-2">
            <button class="active" data-category="all">All</button>
            <?php foreach($categories as $cat): ?>
            <button data-category="<?php echo htmlspecialchars($cat); ?>"><?php echo htmlspecialchars($cat); ?></button>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Product Grid -->
    <div id="productContainer" class="row mt-3 g-3">
        <?php foreach($products as $group => $items): ?>
            <div class="col-12 group" data-group="<?php echo htmlspecialchars($group); ?>">
                <div class="group-title"><?php echo htmlspecialchars($group); ?></div>
                <div class="row g-3">
                    <?php foreach($items as $product): ?>
                    <div class="col-6 col-md-3">
                        <div class="product-card card h-100 text-center" 
                             data-name="<?php echo strtolower($product['name']); ?>" 
                             style="background-color: <?php echo randomPastelColor(); ?>;">
                            <div class="card-body">
                                <div class="card-title"><?php echo htmlspecialchars($product['name']); ?></div>
                                <div class="card-price">$<?php echo number_format($product['price'], 2); ?></div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
// Clock
function updateClock() {
    const now = new Date();
    document.getElementById('clock').innerText = now.toLocaleTimeString();
}
setInterval(updateClock, 1000);
updateClock();

// Clock with Date
function updateClock() {
    const now = new Date();
    const options = { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' };
    const dateStr = now.toLocaleDateString(undefined, options); //"Thu, Oct 30, 2025"
    const timeStr = now.toLocaleTimeString(); //"2:45:30 PM"
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
        const category = btn.getAttribute('data-category');
        productGroups.forEach(group => {
            if (category === 'all' || group.dataset.group === category) {
                group.style.display = '';
            } else {
                group.style.display = 'none';
            }
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
            if (name.includes(query)) {
                card.style.display = '';
                visible = true;
            } else {
                card.style.display = 'none';
            }
        });
        group.style.display = visible ? '' : 'none';
    });
});
</script>

</body>
</html>
