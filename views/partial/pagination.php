<nav aria-label="Pagination">
    <ul class="pagination pagination-sm justify-content-center flex-wrap">
        <?php if (isset($data["previous_status"])) { ?>
            <li class="page-item <?= $data["previous_status"]; ?>">
                <a class="page-link" href="<?= $data["previous_link"]; ?>" aria-label="Previous">&laquo;</a>
            </li>
        <?php } ?>

        <?php if (isset($data["page_nr"])) { ?>
            <?php foreach ($data["page_nr"] as $i) { ?>
                <li class="page-item <?= $data["page_status"][$i]; ?>">
                    <a class="page-link" href="<?= $data["page_link"][$i]; ?>"><?= $i; ?></a>
                </li>
            <?php } ?>
        <?php } ?>

        <?php if (isset($data['next_status'])) { ?>
            <li class="page-item <?= $data["next_status"]; ?>">
                <a class="page-link" href="<?= $data["next_link"]; ?>" aria-label="Next">&raquo;</a>
            </li>
        <?php } ?>
    </ul>
</nav>