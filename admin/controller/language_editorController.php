<?php
require_once __DIR__ . "/../models/language_editor.php";

class Language_editorController
{
    /*
    private $editor_cls;

    public function __construct()
    {
        $this->editor_cls = new editor_cls;
    }
    */

    public function detail($dbh)
    {
        $language_model = new LanguageEditorModel($dbh);
        $language_editor['language'] = $language_model->getLanguage();
        $language_editor['file'] = '../languages/' . $language_editor['language'] . '/' . $language_editor['language'] . '.po';
        $language_editor['message'] = $language_model->saveFile($language_editor);

        return $language_editor;
    }
}
