<?php

include '../auth.php';
include '../../db.php';

if($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'news_editor'){
echo "Access Denied";
exit();
}

include '../templates/header.php';
$hasSidebar = ($_SESSION['role'] === 'admin');
if($hasSidebar){
    include '../templates/sidebar.php';
}

if(isset($_POST['submit'])){

$title = $_POST['title'];
$description = $_POST['description'];
$image_url = $_POST['image_url'];
$video_url = $_POST['video_url'];
$link = $_POST['link'];
$type = $_POST['type'];
$news_date = $_POST['news_date'];

$conn->query("INSERT INTO news 
(title,description,image_url,video_url,link,type,news_date)
VALUES
('$title','$description','$image_url','$video_url','$link','$type','$news_date')");

header("Location: list.php");
exit();

}
?>

<div class="main-content <?php echo $hasSidebar ? '' : 'no-sidebar'; ?>">

<div class="topbar">
<h3>Add News</h3>
<?php if(!$hasSidebar): ?>
<a href="/admin/logout.php" class="btn btn-warning btn-sm">Logout</a>
<?php endif; ?>
</div>

<div class="content-wrapper">

<form method="POST" class="p-4 bg-white rounded shadow-sm">

<div class="mb-3">
<label class="form-label">Title</label>
<input type="text" name="title" class="form-control" placeholder="Title" required>
</div>

<div class="mb-3">
<label class="form-label">Description</label>
<textarea name="description" class="form-control" placeholder="Description" rows="4" required></textarea>
</div>

<div class="mb-3">
<label class="form-label">Image URL</label>
<input type="url" name="image_url" class="form-control" placeholder="Image URL">
</div>

<div class="mb-3">
<label class="form-label">Video URL</label>
<input type="url" name="video_url" class="form-control" placeholder="Video URL (YouTube)">
</div>

<div class="mb-3">
<label class="form-label">News Link</label>
<input type="url" name="link" class="form-control" placeholder="News Link" required>
</div>

<div class="mb-3">
<label class="form-label">Type</label>
<select name="type" class="form-control" required>
<option value="global">Global</option>
<option value="local">Local</option>
</select>
</div>

<div class="mb-3">
<label class="form-label">News Date</label>
<input type="date" name="news_date" class="form-control" required>
</div>

<button class="btn btn-success" name="submit">Save News</button>
<a href="list.php" class="btn btn-secondary ms-2">Cancel</a>

</form>

</div>

</div>

<?php include '../templates/footer.php'; ?>

