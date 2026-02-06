<?php
/**
 * Professor Controller
 * 
 * This controller handles all professor-related operations.
 * Currently, the logic is embedded in the view files (as per original structure).
 */

// Note: CONFIG_PATH is now defined in bootstrap.php which is loaded by index.php

require_once CONTROLLERS_PATH . '/BaseController.php';

class ProfessorController extends BaseController {
    public function __construct() {}
    public function dashboard() { $this->render('professor/prof_dashboard'); }
    public function myModules() { $this->render('professor/my_modules'); }
    public function reports() { $this->render('professor/reports'); }
    public function students() { $this->render('professor/students'); }
    public function takeAttendance() { $this->render('professor/take_attendance'); }
    public function profile() { $this->render('professor/profile'); }
}

?>
