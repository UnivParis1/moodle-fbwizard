<?php 
require(__DIR__.'/../../config.php'); 
global $CFG;

require_once($CFG->libdir.'/adminlib.php');
require_once(__DIR__.'/../../lib/accesslib.php'); 
require_once('locallib.php');
require_login();

ini_set('max_execution_time', 600);
ini_set('memory_limit', '2048M');
$idcategorie=0;
$url = new moodle_url('/local/fbwizard/index.php');
$PAGE->set_url($url);
$context = context_system::instance();;
$PAGE->set_context($context);
$PAGE->set_pagelayout('report');

/**
 * vérification que l'utilisateur est un administrateur
 */

if (is_siteadmin()) {
	$annee = 0;	

	$PAGE->set_heading(get_string('heading_index', 'local_fbwizard'));
	$PAGE->set_title(get_string('title_index', 'local_fbwizard'));

	echo $OUTPUT->header();
	echo $OUTPUT->box_start('generalbox boxaligncenter boxwidthwide');
	
	echo '<h2>Liste des listes SYMPA des cours créés pour l\'année en 2017-2018.</h2>';
	$cat_annee =  get_config('local_fbwizard','category_model');
	$select = "	SELECT fb.cod_etp
				FROM {fbwizard} fb
				INNER JOIN {course} c on (c.id=fb.courseid) 
				INNER JOIN {course_categories} cc on ( c.category =  cc.id) 
				WHERE (cc.path LIKE ? OR cc.path LIKE ?)
				";

	$liste= $DB->get_records_sql($select, array('%/'.$cat_annee.'/%','%/'.$cat_annee.'/%'));

 	echo '<textarea cols="200" rows="25">';
 	foreach ($liste as $cod_etp => $row) {
 		echo 'liste_'.$cod_etp.'-2017@listes.univ-paris1.fr, ';
 	}
 	echo '</textarea>';
}
echo $OUTPUT->box_end();
echo $OUTPUT->footer(); 
