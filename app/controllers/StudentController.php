<?php
/**
 * Student Controller
 * 
 * This controller handles all student-related operations.
 * Currently, the logic is embedded in the view files (as per original structure).
 */

// Note: CONFIG_PATH is now defined in bootstrap.php which is loaded by index.php

require_once CONTROLLERS_PATH . '/BaseController.php';

class StudentController extends BaseController {
    public function __construct() {}
    public function dashboard() { $this->render('student/dashstud'); }
    public function profile() { $this->render('student/profile'); }
}

?>