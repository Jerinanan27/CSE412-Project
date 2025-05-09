<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

session_unset();
session_destroy();

redirect('/auth/login.php?logout=1');