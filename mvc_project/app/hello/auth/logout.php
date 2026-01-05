<?php
// Session is started by bootstrap; keep existing destroy and redirect logic.
session_destroy();
header("Location: ../auth/login.php");
exit();