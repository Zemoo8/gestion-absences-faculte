







<?php
/**
 * Avatar Helper - Generate avatar HTML for users
 * Displays user photo if available, otherwise shows initials
 */

function getAvatarHTML($user_id, $first_name, $last_name, $photo_path = null, $size = 'medium') {
    $initials = substr($first_name, 0, 1) . substr($last_name, 0, 1);
    $initials = strtoupper($initials);
    
    $size_classes = [
        'small' => 'width: 32px; height: 32px; font-size: 0.8rem;',
        'medium' => 'width: 50px; height: 50px; font-size: 1.2rem;',
        'large' => 'width: 80px; height: 80px; font-size: 1.8rem;',
        'xl' => 'width: 120px; height: 120px; font-size: 2.5rem;'
    ];
    
    $style = $size_classes[$size] ?? $size_classes['medium'];
    
    if($photo_path && file_exists(__DIR__ . '/../../public/' . $photo_path)) {
        $public_url = defined('PUBLIC_URL') ? PUBLIC_URL : 'http://localhost';
        return '<img src="' . $public_url . '/' . htmlspecialchars($photo_path) . '" 
                    alt="' . htmlspecialchars($first_name . ' ' . $last_name) . '" 
                    style="' . $style . ' border-radius: 50%; object-fit: cover; object-position: center;" 
                    class="user-avatar-photo">';
    }
    
    // Fallback to initials with gradient
    return '<div class="user-avatar-initials" style="' . $style . ' 
            border-radius: 50%; 
            background: linear-gradient(135deg, #00f5ff, #f72b7b); 
            display: grid; 
            place-items: center; 
            font-weight: 700;
            color: white;">' . $initials . '</div>';
}

function getAvatarURL($photo_path) {
    if($photo_path && file_exists(__DIR__ . '/../../public/' . $photo_path)) {
        $public_url = defined('PUBLIC_URL') ? PUBLIC_URL : 'http://localhost';
        return $public_url . '/' . $photo_path;
    }
    return null;
}
?>
