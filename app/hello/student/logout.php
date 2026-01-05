<?php
// Session started by bootstrap; view remains presentation-only.
session_destroy();
header("Location: " . PUBLIC_URL . "/index.php/login/login");
exit();