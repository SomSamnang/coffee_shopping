<?php
session_start();
require_once 'db_connect.php';
header('Content-Type: application/json');

if(!isset($_SESSION['username']) || $_SESSION['role']!=='admin'){
    echo json_encode(['success'=>false,'msg'=>'Unauthorized']); exit;
}

$action = $_POST['action'] ?? '';
$id = intval($_POST['id'] ?? 0);

if($action=='delete'){
    if($id && $id != $_SESSION['user_id']){
        $conn->query("DELETE FROM users WHERE id=$id");
        echo json_encode(['success'=>true,'msg'=>'User deleted']);
    }else echo json_encode(['success'=>false,'msg'=>'Cannot delete this user']);
}
elseif($action=='toggle'){
    $user=$conn->query("SELECT status FROM users WHERE id=$id")->fetch_assoc();
    if($user){
        $new_status = $user['status']=='active'?'inactive':'active';
        $conn->query("UPDATE users SET status='$new_status' WHERE id=$id");
        echo json_encode(['success'=>true,'status'=>$new_status]);
    }else echo json_encode(['success'=>false,'msg'=>'User not found']);
}
elseif($action=='edit'){
    $username=trim($_POST['username'] ?? '');
    $role=$_POST['role'] ?? 'user';
    $status=$_POST['status'] ?? 'inactive';
    $password=$_POST['password'] ?? '';

    if(!$username || !$role || !$status) { echo json_encode(['success'=>false,'msg'=>'Invalid data']); exit; }

    $sql="UPDATE users SET username=?, role=?, status=?";
    $params=[$username,$role,$status];

    if($password){
        $hashed=password_hash($password,PASSWORD_DEFAULT);
        $sql.=", password=?";
        $params[]=$hashed;
    }

    $sql.=" WHERE id=?";
    $params[]=$id;

    $stmt=$conn->prepare($sql);
    $stmt->bind_param(str_repeat('s',count($params)-1).'i', ...$params);

    if($stmt->execute()){
        echo json_encode(['success'=>true,'id'=>$id,'username'=>$username,'role'=>$role,'status'=>$status]);
    }else echo json_encode(['success'=>false,'msg'=>'Failed']);
}
else echo json_encode(['success'=>false,'msg'=>'Invalid action']);
?>
