<?php

namespace Genealogy\Admin\Controller;

use Genealogy\Admin\Models\LanguageEditorModel;

class LanguageEditorController
{
    protected $admin_config;

    public function __construct($admin_config)
    {
        $this->admin_config = $admin_config;
    }

    public function detail(): array
    {
        $language_model = new LanguageEditorModel($this->admin_config);

        $language_editor['language'] = $language_model->getLanguage();
        $language_editor['file'] = '../languages/' . $language_editor['language'] . '/' . $language_editor['language'] . '.po';
        $language_editor['message'] = $language_model->saveFile($language_editor);

        return $language_editor;
    }
}
