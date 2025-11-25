<?php
session_start();
require_once('../connection/db_connect.php');

$message = "";

// Fetch menu and categories
$menu = [];
$categories = [];
$sql = "SELECT p.product_id, p.name, p.price, p.description, p.image, c.name AS category_name
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

// Handle order submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['place_order'])) {
    $order_total = floatval($_POST['order_total'] ?? 0);
    $order_items_raw = $_POST['order_items'] ?? '';

    if ($order_total > 0 && $order_items_raw) {
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

             header("Location: ../invoice/invoice.php?id=" . $order_id);
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
<title>Coffee Shop POS</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="../css/orders.css">
</head>
<body>

<div class="sticky-header-wrapper">
    <div class="header-content">
        <h1>Relax Coffee Orders</h1>
        <div class="d-flex gap-3 align-items-center">

            <!-- 🔍 Search Box with Icon on Right -->
            <div class="position-relative">
                <input type="text" id="searchBox" class="form-control form-control-sm" 
                       placeholder="Search...">
                <i class="bi bi-search position-absolute"
                   style="right: 10px; top: 50%; transform: translateY(-50%); font-size: 16px; color: #05defbff;"></i>
            </div>

<div class="dropdown">
  <?php if(isset($_SESSION['username'])): ?>
    <button class="btn btn-outline-light btn-sm dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="bi bi-person-circle text-primary"></i> <?= htmlspecialchars($_SESSION['username']) ?>
    </button>
<ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">

    <!-- Profile -->
    <li>
        <a class="dropdown-item" href="profile.php">
            <i class="bi bi-person me-2 text-primary"></i> Profile
        </a>
    </li>

    <!-- Order History -->
    <li>
        <a class="dropdown-item" href="../orders/orders_history.php">
            <i class="bi bi-clock-history me-2 text-warning"></i> History Orders
        </a>
    </li>

    <!-- Only for Admin -->
    <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
        <li>
            <a class="dropdown-item" href="../home/index.php">
                <i class="bi bi-house-door me-2 text-success"></i> Home
            </a>
        </li>
    <?php endif; ?>

    <li><hr class="dropdown-divider"></li>

    <!-- Logout -->
    <li>
        <a class="dropdown-item text-danger" href="../users/logout.php">
            <i class="bi bi-box-arrow-right me-2"></i> Logout
        </a>
    </li>

</ul>

  <?php else: ?>
    <a class="btn btn-outline-light btn-sm" href="../users/login.php"><i class="bi bi-box-arrow-in-right me-1"></i> Login</a>
  <?php endif; ?>
</div>



        </div>
    </div>

    <div class="category-bar">
        <div class="category-scroll">
            <button type="button" class="category-btn active" data-category="all">All</button>
            <?php foreach ($categories as $cat): ?>
                <button type="button" class="category-btn" data-category="<?= strtolower($cat) ?>"><?= htmlspecialchars($cat) ?></button>
            <?php endforeach; ?>
        </div>
        <div id="clock"></div>
    </div>
</div>

<div class="container my-4">
<?= $message ?>
<form method="POST">
<div class="row">
    <div class="col-lg-8">
        <div class="menu-grid">
            <?php foreach ($menu as $category => $items_arr): ?>
                <div class="category-section" data-category="<?= strtolower($category) ?>">
                    <h2><?= htmlspecialchars($category) ?></h2>
                </div>
                <?php foreach ($items_arr as $item):
                    $imgPath = $item['image'] ?: '../uploads/default.jpg';
                    $imgPath = (strpos($imgPath,'../uploads/')===0) ? htmlspecialchars($imgPath) : '../uploads/'.htmlspecialchars($imgPath);
                ?>
                <div class="item-card" data-id="<?= $item['product_id'] ?>" data-price="<?= $item['price'] ?>" data-category="<?= strtolower($category) ?>">
                    <img src="<?= $imgPath ?>" class="item-img" alt="<?= htmlspecialchars($item['name']) ?>">
                    <h5><?= htmlspecialchars($item['name']) ?></h5>
                    <p>$<?= number_format($item['price'],2) ?></p>
                    <div class="item-controls">
                        <div class="d-flex gap-1 justify-content-center align-items-center">
                            <button type="button" class="btn btn-sm qty-decrease">-</button>
                            <input type="number" min="0" value="0" class="item-quantity text-center">
                            <button type="button" class="btn btn-sm qty-increase">+</button>
                            <button type="button" class="btn btn-sm qty-clear">Clear</button>
                        </div>
                        <select class="item-sugar form-select form-select-sm mt-1">
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

    <div class="col-lg-4">
        <div class="order-area">
            <h4>🧾 Cart Summary <button type="button" id="clearAllCart" class="btn btn-sm btn-outline-danger">Clear All</button></h4>
            <div class="cart-list" id="cart_list"></div>
            <p class="order-summary" id="order_summary">Total: $0.00</p>
            <input type="hidden" name="order_total" id="order_total_input" value="0.00">
            <input type="hidden" name="order_items" id="order_items_input" value="">
            <button type="submit" name="place_order" class="button-pos">🛒 Place Order</button>
        </div>
    </div>
</div>
</form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Clock
function updateClock(){
    const now=new Date();
    const options={weekday:'short',year:'numeric',month:'short',day:'numeric',hour:'2-digit',minute:'2-digit',second:'2-digit'};
    document.getElementById('clock').textContent = now.toLocaleDateString('en-US',options);
}
setInterval(updateClock,1000);
updateClock();

// Cart logic
const quantities={};
function calculateTotal(){
    let total=0;
    const items=[];
    const cartList=document.getElementById('cart_list');
    cartList.innerHTML='';
    Object.keys(quantities).forEach(key=>{
        const [id,sugar]=key.split('|');
        const qty=quantities[key];
        if(qty>0){
            const card=document.querySelector(`.item-card[data-id='${id}']`);
            const name=card.querySelector('h5').textContent;
            const price=parseFloat(card.getAttribute('data-price'));
            total+=price*qty;
            items.push({id:parseInt(id),qty:qty,price:price,sugar:sugar});
            const div=document.createElement('div');
            div.className='cart-item';
            div.textContent=`${name} ${sugar} x${qty} - $${(price*qty).toFixed(2)}`;
            cartList.appendChild(div);
        }
    });
    document.getElementById('order_summary').textContent=`Total: $${total.toFixed(2)}`;
    document.getElementById('order_total_input').value=total.toFixed(2);
    document.getElementById('order_items_input').value=JSON.stringify(items);
}

document.querySelectorAll('.item-card').forEach(card=>{
    const qtyInput=card.querySelector('.item-quantity');
    const sugarSelect=card.querySelector('.item-sugar');
    const id=card.getAttribute('data-id');
    const defaultKey=id+'|'+sugarSelect.value;
    quantities[defaultKey]=0;

    qtyInput.addEventListener('input',()=>{ 
        const key=id+'|'+sugarSelect.value;
        quantities[key]=parseInt(qtyInput.value)||0;
        calculateTotal();
    });
    sugarSelect.addEventListener('change',()=>{
        const key=id+'|'+sugarSelect.value;
        if(!(key in quantities)) quantities[key]=0;
        qtyInput.value=quantities[key];
        calculateTotal();
    });
    card.querySelector('.qty-increase').addEventListener('click',()=>{
        const key=id+'|'+sugarSelect.value;
        quantities[key]=(quantities[key]||0)+1;
        qtyInput.value=quantities[key];
        calculateTotal();
    });
    card.querySelector('.qty-decrease').addEventListener('click',()=>{
        const key=id+'|'+sugarSelect.value;
        quantities[key]=Math.max(0,(quantities[key]||0)-1);
        qtyInput.value=quantities[key];
        calculateTotal();
    });
    card.querySelector('.qty-clear').addEventListener('click',()=>{
        const key=id+'|'+sugarSelect.value;
        quantities[key]=0;
        qtyInput.value=0;
        calculateTotal();
    });
});

document.getElementById('clearAllCart').addEventListener('click',()=>{
    Object.keys(quantities).forEach(k=>quantities[k]=0);
    document.querySelectorAll('.item-quantity').forEach(i=>i.value=0);
    calculateTotal();
});

// Search & Filter
function filterItems(){
    const query=document.getElementById('searchBox').value.toLowerCase();
    const activeCategory=document.querySelector('.category-btn.active').getAttribute('data-category').toLowerCase();
    document.querySelectorAll('.category-section').forEach(section=>{
        const sectionCat=section.getAttribute('data-category').toLowerCase();
        let anyVisible=false;
        document.querySelectorAll(`.item-card[data-category='${sectionCat}']`).forEach(card=>{
            const name=card.querySelector('h5').textContent.toLowerCase();
            const show=(name.includes(query) && (activeCategory==='all'||sectionCat===activeCategory));
            card.style.display=show?'block':'none';
            if(show) anyVisible=true;
        });
        section.style.display=anyVisible?'block':'none';
    });
}
document.getElementById('searchBox').addEventListener('input',filterItems);
document.querySelectorAll('.category-btn').forEach(btn=>{
    btn.addEventListener('click',()=>{
        document.querySelectorAll('.category-btn').forEach(b=>b.classList.remove('active'));
        btn.classList.add('active');
        filterItems();
    });
});
calculateTotal();
</script>
</body>
</html>
<?php $conn->close(); ?>
