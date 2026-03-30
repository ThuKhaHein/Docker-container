<!DOCTYPE html>
<html>

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>News Portal</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet" href="/assets/style.css">

</head>

<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow">

<div class="container">

<a class="navbar-brand fw-bold" href="/">

News Portal

</a>

<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">

<span class="navbar-toggler-icon"></span>

</button>

<div class="collapse navbar-collapse justify-content-end" id="navbarNav">

<ul class="navbar-nav align-items-center">

<!-- NEWS TYPES -->

<li class="nav-item dropdown">

<a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">

News Types

</a>

<ul class="dropdown-menu">

<li>
<a class="dropdown-item" href="/">
All News
</a>
</li>

<li>
<a class="dropdown-item" href="/?type=global">
Global News
</a>
</li>

<li>
<a class="dropdown-item" href="/?type=local">
Local News
</a>
</li>

</ul>

</li>

<!-- DEPARTMENTS -->

<li class="nav-item dropdown">

<a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">

Departments

</a>

<ul class="dropdown-menu">

<li><a class="dropdown-item" href="/admin/login.php?department=hr">HR & Admin</a></li>
<li><a class="dropdown-item" href="/admin/login.php?department=operation">Operation</a></li>
<li><a class="dropdown-item" href="/admin/login.php?department=jetty">Jetty</a></li>
<li><a class="dropdown-item" href="/admin/login.php?department=me">M&E</a></li>
<li><a class="dropdown-item" href="/admin/login.php?department=sales&marketing">Sales & Marketing</a></li>
<li><a class="dropdown-item" href="/admin/login.php">News Management</a></li>

</ul>

</li>

<!-- LOGIN BUTTON -->

<li class="nav-item ms-3">

<a href="/admin/login.php" class="btn btn-outline-light">

Admin Login

</a>

</li>

</ul>

</div>

</div>

</nav>

<div class="container mt-4">

