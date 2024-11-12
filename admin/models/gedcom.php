<?php
class GedcomModel
{
    public function get_step()
    {
        $step = '1';
        if (isset($_POST['step']) && is_numeric($_POST['step'])) {
            $step = $_POST['step'];
        }
        if (isset($_GET['step']) && is_numeric($_GET['step'])) {
            $step = $_GET['step'];
        }
        return $step;
    }

    public function get_gedcom_directory()
    {
        $gedcom_directory = "gedcom_files";

        // *** Only needed for Huub's test server ***
        if (@file_exists("../../gedcom-bestanden")) {
            $gedcom_directory = "../../gedcom-bestanden";
        }
        return $gedcom_directory;
    }

    public function upload_gedcom()
    {
        $trees['upload_success'] = '';
        $trees['upload_failed'] = '';

        if (isset($_POST['upload'])) {
            // *** Only needed for Huub's test server ***
            if (file_exists("../../gedcom-bestanden")) {
                $gedcom_directory = "../../gedcom-bestanden";
            } elseif (file_exists("gedcom_files")) {
                $gedcom_directory = "gedcom_files";
            } else {
                $gedcom_directory = ".";
            }

            // *** Only upload .ged or .zip files ***
            if (strtolower(substr($_FILES['upload_file']['name'], -4)) === '.zip' || strtolower(substr($_FILES['upload_file']['name'], -4)) === '.ged') {
                $new_upload = $gedcom_directory . '/' . basename($_FILES['upload_file']['name']);
                // *** Move and check for succesful upload ***
                if (move_uploaded_file($_FILES['upload_file']['tmp_name'], $new_upload)) {
                    $trees['upload_success'] = $new_upload . '<br>' . __('File successfully uploaded.') . '</b>';
                } else {
                    $trees['upload_failed'] = $new_upload . '<br>' . __('Upload has failed.') . '</b>';
                }

                // *** If file is zipped, unzip it ***
                if (strtolower(substr($new_upload, -4)) === '.zip') {
                    $zip = new ZipArchive;
                    $res = $zip->open($new_upload);
                    if ($res === TRUE) {

                        // *** Only unzip .ged files ***
                        $check_gedcom = true;
                        for ($i = 0; $i < $zip->numFiles; $i++) {
                            $filename = $zip->getNameIndex($i);
                            if (strtolower(substr($filename, -4)) !== '.ged') {
                                $check_gedcom = false;
                            }
                        }
                        if ($check_gedcom) {
                            $zip->extractTo($gedcom_directory);
                            $zip->close();
                            $trees['upload_success'] .= '<br>Succesfully unzipped file!';
                        }
                    } else {
                        $trees['upload_failed'] .= '<br>Error in unzipping file!';
                    }
                }
            } else {
                $trees['upload_failed'] = __('Upload has failed.');
            }
        }
        return $trees;
    }
}
