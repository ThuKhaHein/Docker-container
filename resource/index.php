<?php
include 'db.php';
include 'templates/header.php';

$limit = 6;

$page = isset($_GET['page']) ? $_GET['page'] : 1;

$start = ($page - 1) * $limit;

$type = isset($_GET['type']) ? $_GET['type'] : '';

$query = "SELECT * FROM news WHERE 1";

if($type != ""){
$query .= " AND type='$type'";
}

$count_result = $conn->query($query);

$total_records = $count_result->num_rows;

$total_pages = ceil($total_records / $limit);

$query .= " ORDER BY news_date DESC LIMIT $start,$limit";

$result = $conn->query($query);
?>

<h3 class="mb-4">Latest News</h3>

<div class="row g-4">

<?php while($row=$result->fetch_assoc()){ ?>

<div class="col-md-4">

<div class="card news-card h-100">

<div class="media-box">

<?php
if(!empty($row['image_url'])){
?>

<img src="<?php echo $row['image_url']; ?>">

<?php
}elseif(!empty($row['video_url'])){

$video = str_replace("watch?v=","embed/",$row['video_url']);
?>

<iframe src="<?php echo $video; ?>" allowfullscreen></iframe>

<?php } ?>

</div>

<div class="card-body d-flex flex-column">

<h5><?php echo $row['title']; ?></h5>

<p class="flex-grow-1">
<?php echo substr($row['description'],0,120); ?>...
</p>

<p class="text-muted">
<?php echo $row['news_date']; ?>
</p>

<a href="<?php echo $row['link']; ?>" target="_blank" class="btn btn-primary mt-auto">
Read More
</a>

</div>

</div>

</div>

<?php } ?>

</div>


<!-- PAGINATION -->

<div class="pagination-area">

<ul class="pagination-custom">

<?php for($i=1;$i<=$total_pages;$i++){ ?>

<li class="<?php if($i==$page) echo 'active'; ?>">

<a href="?page=<?php echo $i; ?><?php if($type!='') echo '&type='.$type; ?>">
<?php echo $i; ?>
</a>

</li>

<?php } ?>

</ul>

</div>

<?php include 'templates/footer.php'; ?>
