<?php
require_once 'db_connect.php';

$message = "";

// Handle Order Submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['place_order'])) {
    $order_total = isset($_POST['order_total']) ? floatval($_POST['order_total']) : 0.00;
    $order_items_raw = isset($_POST['order_items']) ? $_POST['order_items'] : '';

    if ($order_total > 0 && !empty($order_items_raw)) {
        $conn->begin_transaction();
        try {
            $stmt = $conn->prepare("INSERT INTO orders (total_amount, created_at) VALUES (?, NOW())");
            if (!$stmt) throw new Exception($conn->error);
            $stmt->bind_param("d", $order_total);
            $stmt->execute();
            $order_id = $conn->insert_id;
            $stmt->close();

            $items_array = json_decode($order_items_raw, true);
            $stmt_item = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, item_price) VALUES (?, ?, ?, ?)");
            if (!$stmt_item) throw new Exception($conn->error);

            foreach ($items_array as $item) {
                $stmt_item->bind_param("iiid", $order_id, $item['id'], $item['qty'], $item['price']);
                $stmt_item->execute();
            }
            $stmt_item->close();
            $conn->commit();

            header("Location: invoice.php?id=" . $order_id);
            exit;
        } catch (Exception $e) {
            $conn->rollback();
            $message = "<div class='alert alert-danger'>❌ Failed to place order: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    } else {
        $message = "<div class='alert alert-warning'>⚠️ Please select at least one item.</div>";
    }
}

// Fetch Menu Data
$menu = [];
$categories = [];
$sql = "SELECT p.product_id, p.name, p.price, c.name AS category_name
        FROM products p
        LEFT JOIN categories c ON p.category_id=c.id
        ORDER BY c.name, p.name";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $group = $row['category_name'] ?? 'Uncategorized';
        $menu[$group][] = $row;
        if (!in_array($group, $categories)) $categories[] = $group;
    }
    $result->free();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ORDER- Coffee Shop</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<style> /* Hide scrollbars but allow scrolling */ html, body { overflow: auto; scrollbar-width: none; height: 100%; } body::-webkit-scrollbar { display: none; } /* General Styles */ body { background: #f4f4f7; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; } h1 { text-align: center; margin-bottom: 20px; color: #0d6efd; font-weight: 700; } /* Sticky Navbar + Search */ .sticky-nav { position: sticky; top: 0; background: #fff; padding: 10px 0; z-index: 1000; box-shadow: 0 2px 8px rgba(0,0,0,0.1); display: flex; flex-wrap: wrap; align-items: center; gap: 10px; margin-bottom: 20px; } .sticky-nav .category-btn { border: none; padding: 7px 12px; border-radius: 5px; cursor: pointer; font-weight: 500; background-color: #e2e6ea; transition: 0.2s; } .sticky-nav .category-btn.active, .sticky-nav .category-btn:hover { background-color: #0d6efd; color: #fff; } .sticky-nav #searchBox { max-width: 250px; } .menu-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px,1fr)); gap: 20px; } .category-section { grid-column: 1/-1; margin-bottom: 15px; } .category-section h2 { background: #0d6efd; color: #fff; padding: 10px; border-radius: 5px; margin-bottom: 10px; font-size: 1.1rem; } /* Item Card */ .item-card { background: #fff; border-radius: 8px; padding: 15px; box-shadow: 0 3px 8px rgba(0,0,0,0.1); display: flex; flex-direction: column; justify-content: space-between; transition: transform 0.2s; } .item-card:hover { transform: translateY(-3px); } .item-card h5 { margin: 0 0 10px 0; font-size: 1rem; color: #343a40; } .item-card p { margin: 0 0 10px 0; color: #198754; font-weight: 600; } .item-card input { width: 70px; padding: 5px; text-align: center; border-radius: 5px; border: 1px solid #ced4da; } /* Sticky Cart/Order Area */ .order-area { position: sticky; top: 20px; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 3px 10px rgba(0,0,0,0.1); margin-top: 20px; } .order-area h4 { margin-bottom: 15px; color: #0d6efd; } .order-summary { font-weight: 600; font-size: 1.2rem; } .button-pos { width: 100%; background: #198754; color: white; border: none; padding: 12px; font-size: 16px; border-radius: 5px; transition: background 0.2s; } .button-pos:hover { background: #157347; } @media(max-width:768px){ .menu-grid{grid-template-columns: repeat(auto-fit, minmax(150px,1fr));} } .sticky-nav { position: sticky; top: 0; background: #fff; padding: 8px 12px; z-index: 1000; box-shadow: 0 2px 8px rgba(0,0,0,0.1); display: flex; justify-content: space-between; align-items: center; flex-wrap: nowrap; /* prevent wrapping */ gap: 10px; } .sticky-nav { position: sticky; /* keeps navbar visible on scroll */ top: 0; /* distance from viewport top */ background: #fff; padding: 8px 12px; z-index: 1000; /* above other content */ box-shadow: 0 2px 8px rgba(0,0,0,0.1); display: flex; justify-content: space-between; align-items: center; flex-wrap: nowrap; gap: 10px; } .sticky-nav #searchBox { max-width: 250px; min-width: 150px; height: 36px; } .sticky-header { position: sticky; top: 0; z-index: 1100; /* above all content */ } .page-title, .sticky-nav, .order-area { position: sticky; top: 0; /* sticky top */ z-index: 1050; /* above other content */ background: #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.1); } .sticky-nav { top: 60px; /* below the page title */ padding: 8px 12px; } .order-area { top: 140px; /* below title + navbar */ } /* Sticky wrapper keeps both elements together */ .sticky-header-wrapper { position: sticky; top: 0; /* stick at the top of viewport */ z-index: 1050; /* above content */ } /* Optional styling */ .page-title h1 { margin: 0; color: #0d6efd; font-size: 2rem; } .sticky-nav { background-color: #08b1a5ff; /* black navbar */ } .sticky-nav .category-btn { background-color: #e2e6ea; border: none; padding: 6px 12px; border-radius: 5px; cursor: pointer; font-weight: 500; } .sticky-nav .category-btn.active, .sticky-nav .category-btn:hover { background-color: #0d6efd; color: #fff; } </style>
</head>
<body>
<div class="container my-4">
<?= $message ?>

<form method="POST" action="">
    <!-- Sticky Header -->
    <div class="sticky-header-wrapper sticky-top bg-white shadow-sm">
        <div class="page-title py-3 px-3">
            <h1>☕ Daily Grind Coffee - Orders</h1>
        </div>

        <div class="sticky-nav d-flex justify-content-between align-items-center px-3 py-2">
            <div class="d-flex flex-wrap gap-2 align-items-center">
                <button type="button" class="category-btn active" data-category="all">All</button>
                <?php foreach ($categories as $cat): ?>
                    <button type="button" class="category-btn" data-category="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></button>
                <?php endforeach; ?>
            </div>
            <input type="text" id="searchBox" class="form-control ms-3" placeholder="Search products..." style="width: 200px; min-width: 150px;">
        </div>
    </div>

    <div class="row mt-3">
        <!-- Menu Column -->
        <div class="col-lg-8">
            <div class="menu-grid">
                <?php foreach ($menu as $category => $items_arr): ?>
                    <div class="category-section" id="group-<?= preg_replace('/\s+/','-', strtolower($category)) ?>">
                        <h2><?= htmlspecialchars($category) ?></h2>
                    </div>
                    <?php foreach ($items_arr as $item): ?>
                        <div class="item-card" data-id="<?= $item['product_id'] ?>" data-price="<?= $item['price'] ?>" data-category="<?= strtolower($category) ?>">
                            <h5><?= htmlspecialchars($item['name']) ?></h5>
                            <p>$<?= number_format($item['price'],2) ?></p>
                            <input type="number" min="0" value="0" class="item-quantity">
                        </div>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Order Column -->
        <div class="col-lg-4">
            <div class="order-area sticky-top mt-3 bg-white shadow-sm p-3 rounded">
                <h4>🧾 Cart Summary</h4>
                <p class="order-summary" id="order_summary">Total: $0.00</p>
                <input type="hidden" name="order_total" id="order_total_input" value="0.00">
                <input type="hidden" name="order_items" id="order_items_input" value="">
                <button type="submit" name="place_order" class="button-pos">🛒 Place Order</button>
                     <a href="index.php" class="btn btn-outline-secondary w-100 mt-3 py-2">Cancel</a>
            </div>
        </div>
    </div>
</form>
</div>

<script>
function calculateTotal() {
    let total = 0;
    const items = [];
    document.querySelectorAll('.item-card').forEach(div => {
        const id = parseInt(div.getAttribute('data-id'));
        const price = parseFloat(div.getAttribute('data-price'));
        const qty = parseInt(div.querySelector('.item-quantity').value) || 0;
        if(qty>0){
            total += price * qty;
            items.push({id:id, qty:qty, price:price});
        }
    });
    document.getElementById('order_summary').textContent = `Total: $${total.toFixed(2)}`;
    document.getElementById('order_total_input').value = total.toFixed(2);
    document.getElementById('order_items_input').value = JSON.stringify(items);
}

document.querySelectorAll('.item-quantity').forEach(input => input.addEventListener('input', calculateTotal));
window.onload = calculateTotal;


const searchBox = document.getElementById('searchBox');
const catButtons = document.querySelectorAll('.category-btn');
const categorySections = document.querySelectorAll('.category-section');
const itemCards = document.querySelectorAll('.item-card');

// Update visibility based on category and search
function updateVisibility() {
    const selectedCategory = document.querySelector('.category-btn.active').getAttribute('data-category').toLowerCase();
    const searchTerm = searchBox.value.toLowerCase();

    categorySections.forEach(section => {
        const catName = section.querySelector('h2').textContent.toLowerCase();
        const items = Array.from(itemCards).filter(card => card.getAttribute('data-category') === catName);

        let anyVisible = false;
        items.forEach(card => {
            const name = card.querySelector('h5').textContent.toLowerCase();
            const matchesCategory = (selectedCategory === 'all' || catName === selectedCategory);
            const matchesSearch = name.includes(searchTerm);
            const visible = matchesCategory && matchesSearch;
            card.style.display = visible ? 'block' : 'none';
            if (visible) anyVisible = true;
        });

        // hide the category title if no visible items
        section.style.display = anyVisible ? 'block' : 'none';
    });
}

// Button click behavior
catButtons.forEach(btn => {
    btn.addEventListener('click', () => {
        catButtons.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        updateVisibility();
    });
});

searchBox.addEventListener('input', updateVisibility);
updateVisibility();


</script>
</body>
</html>
<?php $conn->close(); ?>
