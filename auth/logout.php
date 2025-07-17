<?php
session_start();
session_unset();
session_destroy();
header("Location: /vehicle_marketplace/index.php");
exit();
?>