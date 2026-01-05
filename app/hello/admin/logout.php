<?php
// Session is handled by bootstrap; destroy session and redirect.
session_destroy();
header("Location: " . PUBLIC_URL . "/index.php/login/login");
exit();