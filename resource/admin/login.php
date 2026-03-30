<?php
session_start();
include '../db.php';

if(isset($_POST['login'])){

$email = trim($_POST['email']);
$password = trim($_POST['password']);

$sql = "SELECT * FROM users WHERE email='$email' LIMIT 1";
$result = $conn->query($sql);

if($result && $result->num_rows > 0){

$user = $result->fetch_assoc();

if($user['password'] === $password){

$_SESSION['user_id'] = $user['id'];
$_SESSION['name'] = $user['name'];
$_SESSION['role'] = $user['role'];
$_SESSION['department'] = $user['department'];

$departmentQuery = isset($_GET['department']) ? strtolower(trim($_GET['department'])) : '';
$userDepartment = strtolower(trim($user['department']));
$userRole = strtolower(trim($user['role']));

/* ROLE REDIRECT */
if($userRole == 'admin'){
header("Location: /admin/dashboard.php");
exit();
}

if($userRole == 'news_editor'){
header("Location: /admin/news/list.php");
exit();
}

if($userRole == 'manager'){
    $departmentMatch = false;
    if(!$departmentQuery) {
        $departmentMatch = true; // No department query means allow any department
    } else {
        // Check if department query matches user's department (with variations)
        $normalizedQuery = strtolower(trim($departmentQuery));
        $normalizedUserDept = strtolower(trim($userDepartment));
        
        if($normalizedQuery === $normalizedUserDept) {
            $departmentMatch = true;
        } else {
            // Check for department variations
            if(($normalizedUserDept == 'hr' && $normalizedQuery == 'hr') ||
               ($normalizedUserDept == 'hr' && $normalizedQuery == 'HR') ||
               ($normalizedUserDept == 'operation' && $normalizedQuery == 'operation') ||
               ($normalizedUserDept == 'operation' && $normalizedQuery == 'Operation') ||
               ($normalizedUserDept == 'jetty' && $normalizedQuery == 'jetty') ||
               ($normalizedUserDept == 'jetty' && $normalizedQuery == 'Jetty') ||
               (($normalizedUserDept == 'm&e' || $normalizedUserDept == 'me') && ($normalizedQuery == 'm&e' || $normalizedQuery == 'me')) ||
               (($normalizedUserDept == 'sales&marketing' || $normalizedUserDept == 'sales') && ($normalizedQuery == 'sales&marketing' || $normalizedQuery == 'sales'))) {
                $departmentMatch = true;
            }
        }
    }
    
    if(!$departmentMatch){
        $error = "Access denied for selected department.\n";
    } else {
if($userDepartment == 'hr' || $userDepartment == 'HR'){
header("Location: /admin/departments/hr.php");
exit();
}
if($userDepartment == 'operation' || $userDepartment == 'Operation'){
header("Location: /admin/departments/operation.php");
exit();
}
if($userDepartment == 'jetty' || $userDepartment == 'Jetty'){
header("Location: /admin/departments/jetty.php");
exit();
}
if($userDepartment == 'm&e' || $userDepartment == 'me'){
header("Location: /admin/departments/me.php");
exit();
}
if($userDepartment == 'sales&marketing' || $userDepartment == 'sales'){
header("Location: /admin/departments/sales.php");
exit();
}

// generic fallback
header("Location: /admin/dashboard.php");
exit();
}
}

}else{

$error = "Incorrect password";

}

}else{

$error = "User not found";

}

}
?>

<!DOCTYPE html>
<html>

<head>

<title>Login</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
height:100vh;
display:flex;
align-items:center;
justify-content:center;
background:#f5f7fb;
}

.login-box{
width:380px;
background:white;
padding:30px;
border-radius:10px;
box-shadow:0 5px 15px rgba(0,0,0,0.15);
}

</style>

</head>

<body>

<div class="login-box">

<h4 class="text-center mb-4">User Login</h4>

<?php if(isset($error)){ ?>

<div class="alert alert-danger">
<?php echo $error; ?>
</div>

<?php } ?>

<form method="POST">

<input type="email" name="email" class="form-control mb-3" placeholder="Email" required>

<input type="password" name="password" class="form-control mb-3" placeholder="Password" required>

<button class="btn btn-primary w-100" name="login">Login</button>

</form>

</div>

</body>
</html>

