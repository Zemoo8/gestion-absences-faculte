<?php
/**
 * User Model
 * 
 * This model handles user-related database operations.
 * Currently, database operations are in view files (as per original structure).
 */

// Note: CONFIG_PATH is now defined in bootstrap.php which is loaded by index.php

class User {
    
    public function __construct() {
        // Constructor
    }
    
    // Find a user by email. Returns associative array or null.
    public static function findByEmail($email) {
        global $mysqli;
        if (!isset($mysqli)) return null;
        $stmt = $mysqli->prepare("SELECT id, password, role, nom, prenom, email FROM users WHERE email = ? LIMIT 1");
        if (!$stmt) return null;
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $res->num_rows === 1) {
            return $res->fetch_assoc();
        }
        return null;
    }

    // You can add other user-related DB methods here.
}
?>