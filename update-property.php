<?php
require "db.php";


$id        = (int) $_POST['id'];
$type      = $_POST['type'];
$price     = (int) $_POST['price'];
$province  = $_POST['province'];
$city      = $_POST['city'];
$size      = (int) $_POST['size'];
$lat       = (float) $_POST['lat'];
$lng       = (float) $_POST['lng'];
$desc      = $_POST['description'];
$owner     = $_POST['owner'];
$phone     = $_POST['phone'];
$payment   = $_POST['payment'];
$video     = $_POST['video'];
$address   = $_POST['address'];

$u = $conn->prepare("
UPDATE properties SET
    type=?,
    price=?,
    province=?,
    city=?,
    size=?,
    lat=?,
    lng=?,
    description=?,
    owner=?,
    phone=?,
    payment=?,
    video=?,
    address=?
WHERE id=?
");

$u->bind_param(
    "sissiddssssssi",
    $type,     
    $price,    
    $province, 
    $city,     
    $size,     
    $lat,      
    $lng,      
    $desc,     
    $owner,    
    $phone,    
    $payment,  
    $video,    
    $address,  
    $id        
);


$u->execute();

header("Location: dashboard.php");
exit;
