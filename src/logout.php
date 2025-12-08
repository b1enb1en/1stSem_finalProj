<?php
session_start();
session_destroy();
// Redirect to index.php located one level above this `src/` folder
header('Location: ../index.php');
exit;
