<?php
session_start();
session_unset();
session_destroy(); 
echo"<script>location.assign('index.php')</script>";
?>
