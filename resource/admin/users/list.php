<?php
include '../auth.php';
include '../../db.php';

if($_SESSION['role'] != 'admin'){
echo "Access Denied";
exit();
}

$result = $conn->query("SELECT * FROM users ORDER BY id DESC");

include '../templates/header.php';
include '../templates/sidebar.php';
?>

<div class="main-content">

<div class="topbar">
<h3>User Management</h3>
</div>

<div class="content-wrapper">

<a href="add.php" class="btn btn-success mb-3">Add User</a>

<div class="bg-white rounded shadow-sm p-4">

<table class="table table-striped">

<thead class="table-dark">

<tr>
<th>ID</th>
<th>Name</th>
<th>Email</th>
<th>Role</th>
<th>Department</th>
<th>Action</th>
</tr>

</thead>

<tbody>

<?php $rowNumber = 0; while($row=$result->fetch_assoc()){ $rowNumber++; ?>

<tr>

<td><?php echo $rowNumber; ?></td>
<td><?php echo $row['name']; ?></td>
<td><?php echo $row['email']; ?></td>
<td><?php echo $row['role']; ?></td>
<td><?php echo $row['department']; ?></td>

<td>

<a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">
Edit
</a>

<a href="delete.php?id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">
Delete
</a>

</td>
</tr>

<?php } ?>

</tbody>

</table>

</div>

</div>

</div>

<?php include '../templates/footer.php'; ?>

</div>

</body>

</html>
