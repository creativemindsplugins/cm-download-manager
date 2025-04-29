<?php
$isAdministrator = false;
$cuser = wp_get_current_user();
if (in_array('administrator', $cuser->roles)) {
    $isAdministrator = true;
}
?>

<?php if ($isAdministrator) : ?>
    <?php require_once CMDM_PATH . '/views/components/admin-downloads-filter.php'; ?>
<?php else: ?>
    <?php require_once CMDM_PATH . '/views/components/user-downloads-filter.php'; ?>
<?php endif; ?>
