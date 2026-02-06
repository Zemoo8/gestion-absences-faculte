<?php
/**
 * Admin Controller
 * 
 * This controller handles all admin-related operations.
 * Currently, the logic is embedded in the view files (as per original structure).
 * In a full MVC implementation, this would contain the business logic.
 */

// Note: CONFIG_PATH is now defined in bootstrap.php which is loaded by index.php

require_once CONTROLLERS_PATH . '/BaseController.php';

class AdminController extends BaseController {
    public function __construct() {}

    public function dashboard() { $this->render('admin/dashboard'); }
    public function addUser() { $this->render('admin/adduser'); }
    public function addModule() { $this->render('admin/addmodule'); }
    public function assignStudents() { $this->render('admin/assign_students'); }
    public function attendanceRecord() { $this->render('admin/attendancerecord'); }
    public function classes() { $this->render('admin/classes'); }
    public function moduleList() { $this->render('admin/modulelist'); }
    public function notifications() { $this->render('admin/notif'); }
    public function userList() { $this->render('admin/userlist'); }
    public function profile() { $this->render('admin/profile'); }
}
?>