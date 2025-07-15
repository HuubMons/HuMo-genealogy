<?php

/**
 * June 2024: split into MVC files.
 */

if (isset($_POST['send_mail']) && $mail_data["send_mail"] == true) {
    if (!$mail_data["mail_results"]->send()) {
?>
        <div class="alert alert-danger" role="alert">
            <?= __('Sending e-mail failed!') . ' ' . $mail_data["mail_results"]->ErrorInfo; ?>
        </div>
    <?php } else { ?>
        <div class="alert alert-info" role="alert">
            <?= __('E-mail sent!'); ?>
        </div>
    <?php
    }
} else {
    if (!$selectedFamilyTree->tree_email) {
    ?>
        <div class="alert alert-info" role="alert">
            <?= __('The e-mail function has been switched off!'); ?>
        </div>
    <?php
    } else {
        $path = 'index.php?page=mailform';
        if ($humo_option["url_rewrite"] == "j") $path = 'mailform';
    ?>

        <h1 class="my-4"><?= __('Mail form'); ?></h1>

        <?php if (!$mail_data["correct_spam_answer"]) { ?>
            <div class="alert alert-info" role="alert">
                <?= __('Wrong answer to the block-spam question! Try again...'); ?>
            </div>
        <?php } ?>

        <?php if (!$mail_data["check_mail_address"]) { ?>
            <div class="alert alert-info" role="alert">
                <?= __('The e-mail address you entered doesn\'t seem to be a valid e-mail address!'); ?>
            </div>
        <?php } ?>

        <div class="container">
            <form action="<?= $path; ?>" method="post">
                <div class="mb-2 row">
                    <label for="name" class="col-sm-3 col-form-label"><?= __('Name'); ?></label>
                    <div class="col-sm-5">
                        <input type="text" id="fname" class="form-control" name="mail_name" placeholder="<?= __('Name'); ?>" value="<?= $mail_data["name"]; ?>">
                    </div>
                </div>

                <div class="mb-2 row">
                    <label for="mail_sender" class="col-sm-3 col-form-label"><?= __('E-mail address'); ?></label>
                    <div class="col-sm-5">
                        <input type="email" id="lname" class="form-control" name="mail_sender" placeholder="<?= __('E-mail address'); ?>" value="<?= $mail_data["sender"]; ?>" required>
                    </div>
                </div>

                <div class="mb-2 row">
                    <label for="subject" class="col-sm-3 col-form-label"><?= __('Subject'); ?></label>
                    <div class="col-sm-5">
                        <input type="text" id="lname" class="form-control" name="mail_subject" placeholder="<?= __('Subject'); ?>" value="<?= $mail_data["subject"]; ?>">
                    </div>
                </div>

                <div class="mb-2 row">
                    <label for="message" class="col-sm-3 col-form-label"><?= __('Message'); ?></label>
                    <div class="col-sm-5">
                        <textarea id="message" class="form-control" name="mail_text" placeholder="<?= __('Message'); ?>" style="height:200px"><?= $mail_data["text"]; ?></textarea>
                    </div>
                </div>

                <?php if ($humo_option["use_newsletter_question"] == 'y') { ?>
                    <div class="mb-2 row">
                        <label for="newsletter" class="col-sm-3 col-form-label"><?= __('Receive newsletter'); ?></label>
                        <div class="col-sm-5">
                            <input type="radio" class="form-check-input my-1" name="newsletter" value="Yes"> <?= __('Yes'); ?><br>
                            <input type="radio" class="form-check-input my-1" name="newsletter" value="No" checked> <?= __('No'); ?>
                        </div>
                    </div>
                <?php } ?>

                <?php if ($humo_option["use_spam_question"] == 'y') { ?>
                    <div class="mb-2 row">
                        <label for="mail_block_spam" class="col-sm-3 col-form-label"><?= __('Please answer the block-spam-question:'); ?></label>
                        <div class="col-sm-5">
                            <?= $humo_option["block_spam_question"]; ?>
                            <input type="text" id="lname" class="form-control" name="mail_block_spam">
                        </div>
                    </div>
                <?php } ?>

                <br>
                <div class="mb-2 row">
                    <label for="2fa_code" class="col-sm-3 col-form-label"></label>
                    <div class="col-sm-7">
                        <input type="submit" class="col-sm-2 btn btn-success" name="send_mail" value="<?= __('Send'); ?>">
                    </div>
                </div>
            </form>
        </div>
<?php
    }
}
