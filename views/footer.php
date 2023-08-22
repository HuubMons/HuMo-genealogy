<script src="<?= CMS_ROOTPATH; ?>include/glightbox/glightbox_footer.js"></script>

</div> <!-- End of div: Content -->
</div> <!-- End of div: Silverline -->

<?php if ($humo_option["text_footer"]); ?>
<br><br><?= $humo_option["text_footer"]; ?>

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