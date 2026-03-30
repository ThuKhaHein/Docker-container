<?php

include '../auth.php';

$role = strtolower(trim($_SESSION['role'] ?? ''));
$department = strtolower(trim($_SESSION['department'] ?? ''));

if(!in_array($role, ['admin', 'manager'])){
    header("Location: /admin/login.php");
    exit();
}

if($role === 'manager' && ($department !== 'jetty' && $department !== 'Jetty')){
    echo "Access Denied";
    exit();
}

include '../templates/header.php';
$hasSidebar = ($role === 'admin');
if($hasSidebar){
    include '../templates/sidebar.php';
}

?>

<div class="main-content <?php echo $hasSidebar ? '' : 'no-sidebar'; ?>">

<div class="topbar">
    <h3>Jetty Department Dashboard</h3>
    <?php if(!$hasSidebar): ?>
    <a href="/admin/logout.php" class="btn btn-warning btn-sm">Logout</a>
    <?php endif; ?>
</div>

<div class="content-wrapper">
    <div class="bg-white rounded shadow-sm p-4">
        <h5>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?></h5>
        <p>You are logged in as <strong><?php echo htmlspecialchars($_SESSION['role']); ?></strong> in <strong>Jetty</strong>.</p>
        <p>This dashboard is the default Jetty department landing page. Update content here when ready.</p>
    </div>
</div>

</div>

<?php include '../templates/footer.php'; ?>
