<?php
require_once '../config/config.php';

logoutAdmin();
redirect(APP_URL . '/admin/login.php');
