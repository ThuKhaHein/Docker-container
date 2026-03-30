<?php
include '../db.php';
include 'auth.php';

if($_SESSION['role'] != 'admin'){
echo "Access Denied";
exit();
}

include 'templates/header.php';
include 'templates/sidebar.php';

/* TOTAL NEWS */

$total_news = 0;

$result = $conn->query("SELECT COUNT(*) AS total FROM news");

if($result){
$row = $result->fetch_assoc();
$total_news = $row['total'];
}

?>

<div class="main-content">

<div class="topbar">
<h3>Admin Dashboard</h3>
</div>

<div class="container-fluid">

<div class="row g-4">

<div class="col-md-4">

<div class="dashboard-card">

<h5>Total News</h5>

<h2><?php echo $total_news; ?></h2>

</div>

</div>

<div class="col-md-4">

<div class="dashboard-card">

<h5>Logged User</h5>

<h2><?php echo $_SESSION['name']; ?></h2>

</div>

</div>

<div class="col-md-4">

<div class="dashboard-card">

<h5>Website</h5>

<a href="/" class="btn btn-primary">
Open Website
</a>

</div>

</div>

</div>

</div>

</div>

<?php include 'templates/footer.php'; ?>

