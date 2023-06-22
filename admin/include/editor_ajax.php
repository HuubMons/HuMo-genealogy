<?php
session_start();

/**
 * AT THIS MOMENT THIS IS USED FOR TESTING.
 * Testing Ajax for saving data.
 */

if (isset($_SESSION['admin_tree_id'])) {
    if (!defined("CMS_ROOTPATH")) define("CMS_ROOTPATH", "../../");

    $ADMIN = TRUE; // *** Override "no database" message for admin ***
    include_once(CMS_ROOTPATH . "include/db_login.php"); // *** Database login ***
    include_once(CMS_ROOTPATH . "include/safe.php");

    echo "Data Submitted succesfully";

    echo $_POST['pers_firstname1'].'!';
    echo $_POST['pers_gedcomnumber1'];

    $tree_id=$_POST['tree_id1'];
    $gedcomnumber=$_POST['pers_gedcomnumber1'];

include_once(CMS_ROOTPATH . "include/db_functions_cls.php");
$db_functions = new db_functions();
$db_functions->set_tree_id($tree_id);
//include_once(CMS_ROOTPATH . "include/person_cls.php");

if ($gedcomnumber) {
    $personDb = $db_functions->get_person($gedcomnumber);
    $name = '';
    $name .= $personDb->pers_firstname . ' ';
    if ($personDb->pers_patronym) $name .= $personDb->pers_patronym . ' ';
    $name .= strtolower(str_replace("_", " ", $personDb->pers_prefix)) . $personDb->pers_lastname;
    if (trim($name) == '') $name = '[' . __('NO NAME') . ']';
    $text = $name . "\n";
} else {
    $text = __('N.N.');
}
echo $text;
?>

<!-- This doesn't work. Results must be returned to javascript in editor? -->
<script>
document.getElementById("ajax_pers_fullname").innerHTML = <?= $text;?>;
</script>

<?php
if ($pers_name_text) echo ' <img src="images/text.png" height="16">';

    //$gedcom_date = strtoupper(date("d M Y"));
    //$gedcom_time = date("H:i:s");
    /*
    // Establishing connection with server by passing "server_name", "user_id", "password".
    $connection = mysql_connect("localhost", "root", "");
    // Selecting Database by passing "database_name" and above connection variable.
    $db = mysql_select_db("mydba", $connection);
    $name2=$_POST['name1']; // Fetching Values from URL
    $email2=$_POST['email1'];
    $contact2=$_POST['contact1'];
    $gender2=$_POST['gender1'];
    $msg2=$_POST['msg1'];
    $query = mysql_query("insert into form_element(name, email, contact, gender, message) values ('$name2','$email2','$contact2','$gender2','$msg2')"); //Insert query
    if($query){
    echo "Data Submitted succesfully";
    }
    mysql_close($connection); // Connection Closed.
    */
}
