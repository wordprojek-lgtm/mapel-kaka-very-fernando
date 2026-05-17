<?php
function onlyAdmin(){
    if($_SESSION['role'] != 'admin'){
        header("Location: ../logout.php");
        exit;
    }
}

function onlyPetugas(){
    if($_SESSION['role'] != 'petugas'){
        header("Location: ../logout.php");
        exit;
    }
}

function onlyOwner(){
    if($_SESSION['role'] != 'owner'){
        header("Location: ../dashboard.php");
        exit;
    }
}
?>