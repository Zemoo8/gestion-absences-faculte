<?php
// Session is handled by bootstrap; destroy and redirect.
session_destroy();
header("Location: " . PUBLIC_URL . "/index.php/login/login");
exit();