/**
 * @license Copyright (c) 2003-2015, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	// config.uiColor = '#AADC6E';

	config.removePlugins = 'save,newpage,preview,print';

	config.enterMode = CKEDITOR.ENTER_BR;

	// KCFinder
	config.filebrowserBrowseUrl = 'include/kcfinder/browse.php?opener=ckeditor&type=files';
	config.filebrowserImageBrowseUrl = 'include/kcfinder/browse.php?opener=ckeditor&type=images';
	config.filebrowserFlashBrowseUrl = 'include/kcfinder/browse.php?opener=ckeditor&type=flash';
	config.filebrowserUploadUrl = 'include/kcfinder/upload.php?opener=ckeditor&type=files';
	config.filebrowserImageUploadUrl = 'include/kcfinder/upload.php?opener=ckeditor&type=images';
	config.filebrowserFlashUploadUrl = 'include/kcfinder/upload.php?opener=ckeditor&type=flash';
};
