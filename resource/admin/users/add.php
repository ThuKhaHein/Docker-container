<?php
include '../auth.php';
include '../../db.php';

if($_SESSION['role'] != 'admin'){
echo "Access Denied";
exit();
}

include '../templates/header.php';
include '../templates/sidebar.php';

if(isset($_POST['save'])){

$name = $_POST['name'];
$email = $_POST['email'];
$password = $_POST['password'];
$role = $_POST['role'];
$department = $_POST['department'];
$created_at = date('Y-m-d H:i:s');

$conn->query("INSERT INTO users (name,email,password,role,department,created_at)
VALUES ('$name','$email','$password','$role','$department','$created_at')");


header("Location: list.php");
exit();
}
?>

<div class="main-content">

<div class="topbar">
<h3>Add User</h3>
</div>

<div class="content-wrapper">

<form method="POST" class="p-4 bg-white rounded shadow-sm">

<div class="mb-3">
<label class="form-label">Name</label>
<input name="name" placeholder="Name" class="form-control" required>
</div>

<div class="mb-3">
<label class="form-label">Email</label>
<input name="email" type="email" placeholder="Email" class="form-control" required>
</div>

<div class="mb-3">
<label class="form-label">Password</label>
<input name="password" type="password" placeholder="Password" class="form-control" required>
</div>

<div class="mb-3">
<label class="form-label">Role</label>
<select name="role" class="form-control" required>
<option value="admin">Admin</option>
<option value="news_editor">Editor</option>
<option value="manager">Manager</option>
</select>
</div>

<div class="mb-3">
<label class="form-label">Department</label>
<select name="department" class="form-control" required>
<option value="news">News</option>
<option value="hr">HR & Admin</option>
<option value="operation">Operation</option>
<option value="Jetty">Jetty</option>
<option value="m&e">M&E</option>
<option value="sales&marketing">Sales & Marketing</option>
</select>
</div>

<button name="save" class="btn btn-success">Save</button>
<a href="list.php" class="btn btn-secondary ms-2">Cancel</a>

</form>

</div>

</div>

<?php include '../templates/footer.php'; ?>