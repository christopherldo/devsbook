<?php
require_once('config.php');
require_once('models/Auth.php');

$auth = new Auth($pdo, $base);
$userInfo = $auth->checkToken();

require_once('./partials/header.php');
require_once('./partials/menu.php');
?>

<section class="feed mt-10">

</section>

<?php require_once('./partials/footer.php'); ?>