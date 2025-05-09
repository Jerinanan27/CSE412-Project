<?php
require_once 'config.php';
require_once 'db_connect.php';

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function is_admin() {
    return is_logged_in() && $_SESSION['role'] === 'admin';
}

function require_login() {
    if (!is_logged_in()) {
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        header("Location: ".BASE_URL."/auth/login.php");
        exit();
    }
}

function require_admin() {
    require_login();
    if (!is_admin()) {
        header("Location: ".BASE_URL."/user/dashboard.php");
        exit();
    }
}

function get_user_id() {
    return $_SESSION['user_id'] ?? null;
}