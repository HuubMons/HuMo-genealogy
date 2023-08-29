<?php

/**
 * Remark: add these lines in script if footer is in wrong place:
 * <!-- Otherwise footer is at wrong place -->
 * <div style="width:100%; clear:both;"></div>
 */
?>

<script src="<?= CMS_ROOTPATH; ?>include/glightbox/glightbox_footer.js"></script>

</div> <!-- End of div: Content -->
</div> <!-- End of div: Silverline -->

<footer>
    <?php if ($humo_option["text_footer"]); ?>
    <?= $humo_option["text_footer"]; ?>

    <!-- Show HuMo-genealogy footer -->
    <?php if (isset($mainindex)) { ?>
        <?= $mainindex->show_footer(); ?>
    <?php } ?>

    <!--  Links in footer -->
    <div id="footer">
        <br>
        <a href="<?= $menu_path_help; ?>"><?= __('Help'); ?></a>

        <?php if (!$bot_visit) { ?>
            | <a href="<?= $menu_path_cookies;?>"><?php echo ucfirst(str_replace('%s ', '', __('%s cookie information'))); ?></a>
        <?php }; ?>
    </div>
</footer>

<!-- YOU CAN ADD YOUR OWN HTML CODE IN THE BLOCK BELOW -->


<!-- END OF OWN HTML CODE BLOCK -->

<?php
/*
// *** Show a seperate text for every user group ***
if (isset($_SESSION['user_group_id']) AND $_SESSION['user_group_id']=='1'){
    echo 'TEXT: Group 1 (admin)';
}
elseif (isset($_SESSION['user_group_id']) AND $_SESSION['user_group_id']=='2'){
    echo 'TEXT: Group 2 (family)';
}
else{
    echo 'TEXT: Guest';
}
*/

if (!CMS_SPECIFIC) {
    echo "</body>\n";
    echo "</html>";
}
?>