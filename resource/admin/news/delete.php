<?php

include '../auth.php';
include '../../db.php';

if($_SESSION['role'] != 'admin'){
echo "Access Denied";
exit();
}

$id = $_GET['id'];

$conn->query("DELETE FROM news WHERE id=$id");

header("Location: list.php");
exit();