<?php
session_start();
require_once('../connection/db_connect.php');

$id = intval($_GET['id'] ?? 0);
if (!$id) die("Invalid ID");

function getProduct($conn, $id){
    $stmt = $conn->prepare("SELECT * FROM products WHERE product_id=?");
    $stmt->bind_param("i",$id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

$product = getProduct($conn,$id) or die("Product not found");
$categories = $conn->query("SELECT * FROM categories ORDER BY name");

$error = ''; $success = false;

if($_SERVER['REQUEST_METHOD']==='POST'){
    $name = trim($_POST['name']??'');
    $price = floatval($_POST['price']??-1);
    $cat = intval($_POST['category_id']??0);
    $status = $_POST['status']??'';
    $desc = trim($_POST['description']??'');
    $img = $product['image'];

    if(!$name || $price<0 || !$cat || !in_array($status,['active','inactive'])) $error="⚠ Fill all required fields";
    else{
        if(isset($_FILES['image']) && $_FILES['image']['error']==0){
            $ext = strtolower(pathinfo($_FILES['image']['name'],PATHINFO_EXTENSION));
            if(!in_array($ext,['png','jpg','jpeg'])) $error="⚠ Invalid image type";
            else{
                $new = 'product_'.time().'.'.$ext;
                if(!is_dir('uploads')) mkdir('uploads',0777,true);
                if(move_uploaded_file($_FILES['image']['tmp_name'],'uploads/'.$new)){
                    if($img && file_exists('uploads/'.$img)) unlink('uploads/'.$img);
                    $img = $new;
                } else $error="⚠ Image upload failed";
            }
        }
        if(!$error){
            $stmt=$conn->prepare("UPDATE products SET name=?,price=?,category_id=?,status=?,description=?,image=? WHERE product_id=?");
            $stmt->bind_param("sdisssi",$name,$price,$cat,$status,$desc,$img,$id);
            $success=$stmt->execute();
            $stmt->close();
            if($success) $product=getProduct($conn,$id);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Update Product</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../css/update_product.css">
</head>
<body>
<div class="card">
<h2>Update Product</h2>

<?php if($error): ?>
<div class="alert alert-danger"><?=htmlspecialchars($error)?></div>
<?php elseif($success): ?>
<div class="alert alert-success">
  <div class="spinner-border mb-1" role="status"></div>
  <p>Updated successfully! <span id="count">3</span> sec...</p>
</div>
<script>
let sec=3; setInterval(()=>{sec--;document.getElementById('count').textContent=sec;if(sec<=0) location.href='../products/product.php';},1000);
</script>
<?php else: ?>
<form method="post" enctype="multipart/form-data">
<div class="mb-2"><label>Name *</label><input type="text" name="name" class="form-control" value="<?=htmlspecialchars($product['name'])?>" required></div>
<div class="mb-2"><label>Price *</label><input type="number" step="0.01" name="price" class="form-control" value="<?=htmlspecialchars($product['price'])?>" required></div>
<div class="mb-2"><label>Category *</label>
<select name="category_id" class="form-select" required>
<?php while($c=$categories->fetch_assoc()): ?>
<option value="<?=$c['id']?>" <?=$c['id']==$product['category_id']?'selected':''?>><?=htmlspecialchars($c['name'])?></option>
<?php endwhile; ?>
</select></div>
<div class="mb-2"><label>Description</label><textarea name="description" class="form-control" rows="2"><?=htmlspecialchars($product['description'])?></textarea></div>
<div class="mb-2"><label>Image</label><input type="file" name="image" class="form-control" accept=".png,.jpg,.jpeg">
<?php if($product['image'] && file_exists('uploads/'.$product['image'])): ?>
<img src="uploads/<?=htmlspecialchars($product['image'])?>?t=<?=time()?>" class="preview">
<?php endif; ?>
</div>
<div class="mb-2"><label>Status *</label>
<select name="status" class="form-select" required>
<option value="active" <?=$product['status']=='active'?'selected':''?>>Active</option>
<option value="inactive" <?=$product['status']=='inactive'?'selected':''?>>Inactive</option>
</select></div>
<div class="d-flex gap-2"><button class="btn btn-primary flex-fill">Update</button>
<a href="../products/product.php" class="btn btn-secondary flex-fill">Cancel</a></div>
</form>
<?php endif; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>
