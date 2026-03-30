<?php

if(session_status() !== PHP_SESSION_ACTIVE){
session_start();
}

if(!isset($_SESSION['user_id'])){
header("Location: /admin/login.php");
exit();
}

?>

<!DOCTYPE html>
<html>

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Admin Panel</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet" href="/admin/assets/admin.css">
<link rel="stylesheet" href="/admin/assets/me-dashboard.css">

</head>

<body>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script src="/admin/assets/me-dashboard.js"></script>
