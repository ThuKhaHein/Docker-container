<?php

include '../auth.php';
include '../../db.php';

if($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'news_editor'){
echo "Access Denied";
exit();
}

$id = $_GET['id'];

$result = $conn->query("SELECT * FROM news WHERE id=$id");
$row = $result->fetch_assoc();

include '../templates/header.php';
$hasSidebar = ($_SESSION['role'] === 'admin');
if($hasSidebar){
    include '../templates/sidebar.php';
}

if(isset($_POST['update'])){

$title = $_POST['title'];
$description = $_POST['description'];
$image_url = $_POST['image_url'];
$video_url = $_POST['video_url'];
$link = $_POST['link'];
$type = $_POST['type'];
$news_date = $_POST['news_date'];

$conn->query("UPDATE news SET
title='$title',
description='$description',
image_url='$image_url',
video_url='$video_url',
link='$link',
type='$type',
news_date='$news_date'
WHERE id=$id");

header("Location: list.php");
exit();

}
?>

<div class="main-content <?php echo $hasSidebar ? '' : 'no-sidebar'; ?>">

<div class="topbar">
<h3>Edit News</h3>
<?php if(!$hasSidebar): ?>
<a href="/admin/logout.php" class="btn btn-warning btn-sm">Logout</a>
<?php endif; ?>
</div>

<div class="content-wrapper">

<form method="POST" class="p-4 bg-white rounded shadow-sm">

<div class="mb-3">
<label class="form-label">Title</label>
<input type="text" name="title" class="form-control" value="<?php echo $row['title']; ?>" required>
</div>

<div class="mb-3">
<label class="form-label">Description</label>
<textarea name="description" class="form-control" rows="4" required><?php echo $row['description']; ?></textarea>
</div>

<div class="mb-3">
<label class="form-label">Image URL</label>
<input type="url" name="image_url" class="form-control" value="<?php echo $row['image_url']; ?>">
</div>

<div class="mb-3">
<label class="form-label">Video URL</label>
<input type="url" name="video_url" class="form-control" value="<?php echo $row['video_url']; ?>">
</div>

<div class="mb-3">
<label class="form-label">News Link</label>
<input type="url" name="link" class="form-control" value="<?php echo $row['link']; ?>" required>
</div>

<div class="mb-3">
<label class="form-label">Type</label>
<select name="type" class="form-control" required>
<option value="global" <?php if($row['type']=='global') echo 'selected'; ?>>Global</option>
<option value="local" <?php if($row['type']=='local') echo 'selected'; ?>>Local</option>
</select>
</div>

<div class="mb-3">
<label class="form-label">News Date</label>
<input type="date" name="news_date" class="form-control" value="<?php echo $row['news_date']; ?>" required>
</div>

<button class="btn btn-primary" name="update">Update News</button>
<a href="list.php" class="btn btn-secondary ms-2">Cancel</a>

</form>

</div>

</div>

<?php include '../templates/footer.php'; ?>
