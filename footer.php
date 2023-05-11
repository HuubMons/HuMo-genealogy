<!-- Script for GLightbox -->
<script>
	var lightbox = GLightbox();
	lightbox.on('open', (target) => {
		console.log('lightbox opened');
	});
	var lightboxDescription = GLightbox({
		selector: '.glightbox2'
	});
	var lightboxVideo = GLightbox({
		selector: '.glightbox3'
	});
	lightboxVideo.on('slide_changed', ({ prev, current }) => {
		console.log('Prev slide', prev);
		console.log('Current slide', current);

		const { slideIndex, slideNode, slideConfig, player } = current;

		if (player) {
			if (!player.ready) {
				// If player is not ready
				player.on('ready', (event) => {
					// Do something when video is ready
				});
			}

			player.on('play', (event) => {
				console.log('Started play');
			});

			player.on('volumechange', (event) => {
				console.log('Volume change');
			});

			player.on('ended', (event) => {
				console.log('Video ended');
			});
		}
	});

	var lightboxInlineIframe = GLightbox({
		selector: '.glightbox4'
	});
</script>

<?php
echo '</div>'; // End of div: Content.
echo '</div>'; // End of div: Silverline.

if ($humo_option["text_footer"]) echo "<br>\n".$humo_option["text_footer"];
?>

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

if (!CMS_SPECIFIC){
	echo "</body>\n";
	echo "</html>";
}
