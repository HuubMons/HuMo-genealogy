<?php 
include_once __DIR__ . '/include/mainindex_cls.php';
$mainindex = new mainindex_cls($dbh);

// *** Show HuMo-genealogy footer ***
echo $mainindex->show_footer();
?>


		</div> <!-- End of div: Content. -->
	</div> <!-- End of div: Silverline. -->
	
<?php if (!empty($humo_option["text_footer"])) { ?>
	<br> <?= $humo_option["text_footer"]; ?>
<?php } ?>

<!-- YOU CAN ADD YOUR OWN HTML CODE IN THE BLOCK BELOW -->


<!-- END OF OWN HTML CODE BLOCK -->

<?php
/*
// *** Show a seperate text for every user group ***
if (isset($_SESSION['user_group_id']) AND $_SESSION['user_group_id']=='1') { ?>
	TEXT: Group 1 (admin)
<?php } elseif (isset($_SESSION['user_group_id']) AND $_SESSION['user_group_id']=='2') { ?>
	TEXT: Group 2 (family)
<?php } else { ?>
	TEXT: Guest
<?php } ?>
*/
 ?>
 <!-- Script for GLightbox -->
<!-- <script>
	var lightbox = GLightbox();
	lightbox.on('open', (target) => {
		// console.log('lightbox opened');
	});
	var lightboxDescription = GLightbox({
		selector: '.glightbox2'
	});
	var lightboxVideo = GLightbox({
		selector: '.glightbox3'
	});
	lightboxVideo.on('slide_changed', ({
		prev,
		current
	}) => {
		// console.log('Prev slide', prev);
		// console.log('Current slide', current);

		const {
			slideIndex,
			slideNode,
			slideConfig,
			player
		} = current;

		if (player) {
			if (!player.ready) {
				// If player is not ready
				player.on('ready', (event) => {
					// Do something when video is ready
				});
			}

			player.on('play', (event) => {
				// console.log('Started play');
			});

			player.on('volumechange', (event) => {
				// console.log('Volume change');
			});

			player.on('ended', (event) => {
				// console.log('Video ended');
			});
		}
	});

	var lightboxInlineIframe = GLightbox({
		selector: '.glightbox4'
	});
</script> -->

	</body>
</html>

