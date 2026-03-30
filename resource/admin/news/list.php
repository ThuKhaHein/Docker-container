<?php
include '../auth.php';
include '../../db.php';

if($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'news_editor'){
echo "Access Denied";
exit();
}

$result = $conn->query("SELECT * FROM news ORDER BY id DESC");

include '../templates/header.php';
$hasSidebar = ($_SESSION['role'] === 'admin');
if($hasSidebar){
    include '../templates/sidebar.php';
}

?>

<div class="main-content <?php echo $hasSidebar ? '' : 'no-sidebar'; ?>">

<div class="topbar">
<h3>News Management</h3>
<?php if(!$hasSidebar): ?>
<a href="/admin/logout.php" class="btn btn-warning btn-sm">Logout</a>
<?php endif; ?>
</div>

<div class="content-wrapper">

<a href="add.php" class="btn btn-success mb-3">Add News</a>

<div class="bg-white rounded shadow-sm p-4">

<table class="table table-striped">

<thead class="table-dark">

<tr>
<th>ID</th>
<th>Title</th>
<th>Type</th>
<th>Date</th>
<th>Action</th>
</tr>

</thead>

<tbody>

<?php $rowNumber = 0; while($row = $result->fetch_assoc()){ $rowNumber++; ?>

<tr>

<td><?php echo $rowNumber; ?></td>
<td><?php echo $row['title']; ?></td>
<td><?php echo $row['type']; ?></td>
<td><?php echo $row['news_date']; ?></td>

<td>

<a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">
Edit
</a>

<?php if($_SESSION['role']=='admin'){ ?>

<a href="delete.php?id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">
Delete
</a>

<?php } ?>

</td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

</div>

</div>

<?php include '../templates/footer.php'; ?>

