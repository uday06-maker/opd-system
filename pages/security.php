<?php
session_start();
require '../config/db.php';
require '../includes/auth.php';
// then requireRole('patient') or requireRole('doctor') or requireRole('admin')