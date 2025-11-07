<?php
require_once 'db_connect.php';

$message = "";

// Fetch Menu Data
$menu = [];
$categories = [];
$sql = "SELECT p.product_id, p.name, p.price, c.name AS category_name
        FROM products p
        LEFT JOIN categories c ON p.category_id=c.id
        WHERE p.status='active'
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
            $stmt_item = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, item_price, sugar_level) VALUES (?, ?, ?, ?, ?)");
            if (!$stmt_item) throw new Exception($conn->error);

            foreach ($items_array as $item) {
                $stmt_item->bind_param("iiids", $order_id, $item['id'], $item['qty'], $item['price'], $item['sugar']);
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Coffee Shop Orders</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<style>
body { background: #f8f9fa; font-family: 'Poppins', sans-serif; margin:0; }
h1 { font-size:1.5rem; color:#0d6efd; font-weight:700; margin:0; }

/* Sticky Header */
.sticky-header-wrapper { position: sticky; top:0; z-index:1050; background:#fff; box-shadow:0 2px 8px rgba(0,0,0,0.1); }
.header-content { display:flex; justify-content:space-between; align-items:center; padding:9px 18px; background: linear-gradient(90deg, #0d6efd, #6610f2); }
.header-left { margin: 0; font-size: 1.5rem; font-weight: 700; background: linear-gradient(90deg, #ffffffff, #fdd10dff); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
#clock { font-weight:600; color:blue; }
.search-input { width:220px; height:36px; border-radius:8px; border:1px solid #ced4da; }

/* Category Bar */
.category-bar { background:#fff; padding:8px 16px; border-top:1px solid #e0e0e0; border-bottom:1px solid #e0e0e0; display:flex; justify-content:space-between; align-items:center; }
.category-scroll { display:flex; gap:8px; overflow-x:auto; -webkit-overflow-scrolling:touch; }
.category-scroll::-webkit-scrollbar { display:none; }
.category-btn { border:none; padding:6px 14px; border-radius:20px; background:#e9ecef; font-weight:500; cursor:pointer; transition:0.2s; }
.category-btn.active, .category-btn:hover { background:#0d6efd; color:#fff; }

/* Menu Grid */
.menu-grid { display:grid; grid-template-columns:repeat(auto-fit, minmax(180px,1fr)); gap:20px; margin-top:15px; }
.category-section { grid-column:1/-1; margin-bottom:10px; }
.category-section h2 { background:#0d6efd; color:#fff; padding:8px 12px; border-radius:6px; font-size:1.1rem; }

/* Item Cards */
.item-card { background:#fff; border-radius:8px; padding:12px; box-shadow:0 3px 8px rgba(0,0,0,0.1); display:flex; flex-direction:column; justify-content:space-between; transition:transform 0.2s; }
.item-card:hover { transform:translateY(-3px); }
.item-card h5 { margin:0 0 8px 0; font-size:1rem; color:#343a40; }
.item-card p { margin:0 0 8px 0; color:#198754; font-weight:600; }
.item-controls { display:flex; flex-direction:column; gap:6px; }
.item-quantity { width:60px; padding:5px; text-align:center; border-radius:5px; border:1px solid #ced4da; }
.item-sugar { width:100%; border-radius:6px; border:1px solid #ced4da; background-color:#f8f9fa; font-size:0.95rem; padding:4px 6px; }

/* Order Area */
.order-area { position:sticky; top:160px; background:#fff; padding:20px; border-radius:8px; box-shadow:0 3px 10px rgba(0,0,0,0.1); }
.order-area h4 { margin-bottom:10px; color:#0d6efd; display:flex; justify-content:space-between; align-items:center; }
.order-summary { font-weight:600; font-size:1.1rem; margin-bottom:10px; }
.cart-list { max-height:300px; overflow-y:auto; margin-bottom:10px; }
.cart-item { display:flex; justify-content:space-between; padding:5px 0; border-bottom:1px dashed #ccc; font-size:0.95rem; }
.button-pos { width:100%; background:#198754; color:white; border:none; padding:12px; font-size:16px; border-radius:5px; transition:0.2s; }
.button-pos:hover { background:#157347; }

/* Quantity Buttons Color */
.qty-increase { background:#198754 !important; color:#fff !important; }
.qty-decrease { background:#dc3545 !important; color:#fff !important; }
.qty-clear { background:#ffc107 !important; color:#000 !important; }

/* Responsive */
@media(max-width:768px){
    .header-content { flex-direction:column; align-items:flex-start; gap:8px; }
    .search-input { width:100%; margin-top:8px; }
}
html, body { height:100%; overflow:auto; scrollbar-width:none; }
body::-webkit-scrollbar { display:none; }
</style>
</head>
<body>

<!-- Sticky Header -->
<div class="sticky-header-wrapper">
    <div class="header-content">
        <div class="header-left">
            <h1>Daily Grind Coffee Orders</h1>
        </div>
        <input type="text" id="searchBox" class="form-control search-input" placeholder="Search products...">
    </div>
    <div class="category-bar">
        <div class="category-scroll">
            <button type="button" class="category-btn active" data-category="all">All</button>
            <?php foreach ($categories as $cat): ?>
                <button type="button" class="category-btn" data-category="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></button>
            <?php endforeach; ?>
        </div>
        <div id="clock"></div>
    </div>
</div>

<div class="container my-4">
<?= $message ?>

<form method="POST" action="">
    <div class="row mt-3">
        <!-- Menu Column -->
        <div class="col-lg-8">
            <div class="menu-grid">
                <?php foreach ($menu as $category => $items_arr): ?>
                    <div class="category-section" data-category="<?= strtolower($category) ?>">
                        <h2><?= htmlspecialchars($category) ?></h2>
                    </div>
                    <?php foreach ($items_arr as $item): ?>
                        <div class="item-card" data-id="<?= $item['product_id'] ?>" data-price="<?= $item['price'] ?>" data-category="<?= strtolower($category) ?>">
                            <div class="item-info">
                                <h5><?= htmlspecialchars($item['name']) ?></h5>
                                <p>$<?= number_format($item['price'],2) ?></p>
                            </div>
                            <div class="item-controls d-flex flex-column gap-2">
                                <div class="d-flex gap-1 align-items-center">
                                    <button type="button" class="btn btn-sm qty-decrease">-</button>
                                    <input type="number" min="0" value="0" class="item-quantity text-center" title="Quantity">
                                    <button type="button" class="btn btn-sm qty-increase">+</button>
                                    <button type="button" class="btn btn-sm qty-clear">Clear</button>
                                </div>
                                <select class="item-sugar form-select form-select-sm" title="Sugar Level">
                                    <option value="100%">100% sugar</option>
                                    <option value="70%">70% sugar</option>
                                    <option value="50%">50% sugar</option>
                                    <option value="30%">30% sugar</option>
                                    <option value="0%">No sugar</option>
                                </select>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Order Column -->
        <div class="col-lg-4">
            <div class="order-area">
                <h4>
                    🧾 Cart Summary
                    <button type="button" id="clearAllCart" class="btn btn-sm btn-outline-danger">Clear All</button>
                </h4>
                <div class="cart-list" id="cart_list"></div>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Track quantities per product ID + sugar
const quantities = {}; // key = productID + '|' + sugar

function calculateTotal() {
    let total = 0;
    const items = [];
    const cartList = document.getElementById('cart_list');
    cartList.innerHTML = '';

    // Grouped by product name for visual grouping
    const grouped = {};
    Object.keys(quantities).forEach(key => {
        const [id, sugar] = key.split('|');
        const qty = quantities[key];
        if (qty > 0) {
            const card = document.querySelector(`.item-card[data-id='${id}']`);
            const name = card.querySelector('h5').textContent;
            const price = parseFloat(card.getAttribute('data-price'));
            total += price * qty;
            items.push({id:parseInt(id), qty:qty, price:price, sugar:sugar});

            if (!grouped[name]) grouped[name] = [];
            grouped[name].push({sugar:sugar, qty:qty, price:price});
        }
    });

    // Display grouped items
    Object.keys(grouped).forEach(name => {
        grouped[name].forEach(item => {
            const itemDiv = document.createElement('div');
            itemDiv.className = 'cart-item';
            itemDiv.textContent = `${name} ${item.sugar} x${item.qty} - $${(item.price*item.qty).toFixed(2)}`;
            cartList.appendChild(itemDiv);
        });
    });

    document.getElementById('order_summary').textContent = `Total: $${total.toFixed(2)}`;
    document.getElementById('order_total_input').value = total.toFixed(2);
    document.getElementById('order_items_input').value = JSON.stringify(items);
}

// Initialize cards
document.querySelectorAll('.item-card').forEach(card => {
    const qtyInput = card.querySelector('.item-quantity');
    const sugarSelect = card.querySelector('.item-sugar');
    const id = card.getAttribute('data-id');

    const defaultKey = id + '|100%';
    quantities[defaultKey] = 0;

    qtyInput.addEventListener('input', () => {
        const key = id + '|' + sugarSelect.value;
        quantities[key] = parseInt(qtyInput.value) || 0;
        calculateTotal();
    });

    sugarSelect.addEventListener('change', () => {
        const key = id + '|' + sugarSelect.value;
        if (!(key in quantities)) quantities[key] = 0;
        qtyInput.value = quantities[key];
        calculateTotal();
    });

    card.querySelector('.qty-increase').addEventListener('click', () => {
        const key = id + '|' + sugarSelect.value;
        quantities[key] = (quantities[key] || 0) + 1;
        qtyInput.value = quantities[key];
        calculateTotal();
    });
    card.querySelector('.qty-decrease').addEventListener('click', () => {
        const key = id + '|' + sugarSelect.value;
        quantities[key] = Math.max(0, (quantities[key] || 0) - 1);
        qtyInput.value = quantities[key];
        calculateTotal();
    });
    card.querySelector('.qty-clear').addEventListener('click', () => {
        const key = id + '|' + sugarSelect.value;
        quantities[key] = 0;
        qtyInput.value = 0;
        calculateTotal();
    });
});

// Clear all
document.getElementById('clearAllCart').addEventListener('click', () => {
    Object.keys(quantities).forEach(k => quantities[k]=0);
    document.querySelectorAll('.item-quantity').forEach(input => input.value=0);
    calculateTotal();
});

// Clock
function updateClock() {
    const now = new Date();
    const options = { weekday:'short', year:'numeric', month:'short', day:'numeric', hour:'2-digit', minute:'2-digit', second:'2-digit' };
    document.querySelectorAll('#clock').forEach(el => el.textContent = now.toLocaleDateString('en-US', options));
}
setInterval(updateClock,1000);
updateClock();

// Initial calculation
calculateTotal();
</script>
</body>
</html>

<?php $conn->close(); ?>
