<?php
include '../auth.php';
include '../../db.php';

if($_SESSION['role'] != 'admin'){
echo "Access Denied";
exit();
}

$id = $_GET['id'];

$user = $conn->query("SELECT * FROM users WHERE id=$id")->fetch_assoc();

include '../templates/header.php';
include '../templates/sidebar.php';

if(isset($_POST['update'])){

$name = $_POST['name'];
$email = $_POST['email'];
$role = $_POST['role'];
$department = $_POST['department'];

$conn->query("UPDATE users SET
name='$name',
email='$email',
role='$role',
department='$department'
WHERE id=$id");

header("Location: list.php");
exit();
}
?>

<div class="main-content">

<div class="topbar">
<h3>Edit User</h3>
</div>

<div class="content-wrapper">

<form method="POST" class="p-4 bg-white rounded shadow-sm">

<div class="mb-3">
<label class="form-label">Name</label>
<input name="name" value="<?php echo $user['name']; ?>" class="form-control" required>
</div>

<div class="mb-3">
<label class="form-label">Email</label>
<input name="email" value="<?php echo $user['email']; ?>" class="form-control" required>
</div>

<div class="mb-3">
<label class="form-label">Role</label>
<select name="role" class="form-control" required>
<option value="admin" <?php if($user['role']=='admin') echo 'selected'; ?>>Admin</option>
<option value="news_editor" <?php if($user['role']=='news_editor') echo 'selected'; ?>>Editor</option>
<option value="manager" <?php if($user['role']=='manager') echo 'selected'; ?>>Manager</option>
</select>
</div>

<div class="mb-3">
<label class="form-label">Department</label>
<select name="department" class="form-control" required>
<option value="news" <?php if($user['department']=='news') echo 'selected'; ?>>News</option>
<option value="hr" <?php if($user['department']=='hr') echo 'selected'; ?>>HR & Admin</option>
<option value="operation" <?php if($user['department']=='operation') echo 'selected'; ?>>Operation</option>
<option value="Jetty" <?php if($user['department']=='Jetty') echo 'selected'; ?>>Jetty</option>
<option value="m&e" <?php if($user['department']=='m&e') echo 'selected'; ?>>M&E</option>
<option value="sales&marketing" <?php if($user['department']=='sales&marketing') echo 'selected'; ?>>Sales & Marketing</option>
</select>
</div>

<button name="update" class="btn btn-primary">Update</button>
<a href="list.php" class="btn btn-secondary ms-2">Cancel</a>

</form>

</div>

</div>

<?php include '../templates/footer.php'; ?>

