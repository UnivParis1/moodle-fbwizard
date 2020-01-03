<?php 
require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once('../../lib/accesslib.php');
require_once('locallib.php');
require_once('apogee.class.php');
require_login();
global $DB, $CFG, $PAGE, $OUTPUT, $SESSION, $USER;

ini_set('max_execution_time', 600);
ini_set('memory_limit', '2048M');
$idcategorie=0;
$url = new moodle_url('/local/fbwizard/step2.php');
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
	
	$a_traiter = array();
	$i=0;
	foreach ($SESSION->fbwizard as $key=>$row) {
		if (isset($_POST[$row['cod_etp']])) {
		    if (isset($_POST[$row['cod_etp'].'_cohorte'])) {	
			$a_traiter[$i]['category']= $row['category'];
			$a_traiter[$i]['cod_tpd_etb']= $row['cod_tpd_etb'];
			$a_traiter[$i]['cod_etp']= $row['cod_etp'];
			$a_traiter[$i]['cod_vrs_vet']= $row['cod_vrs_vet'];
			$a_traiter[$i]['lib_etp']= $row['lib_etp'];
			$a_traiter[$i]['cohorte']= $_POST[$row['cod_etp'].'_cohorte'];
			$i++;
		    }
		}
	}
	inserer_liste_cours_a_creer($a_traiter);
	echo '<h2>Liste des formations qui seront déployées</h2>';
	$table2 = new html_table();
	$table2->head = array('CATGORY','COD_TPD_ETB','COD_ETP', 'COD_VRS_VET','LIB_ETP','COHORTE ASSOCIEE');
	$table2->data = $a_traiter;
	echo html_writer::table($table2);
}
echo $OUTPUT->box_end();
echo $OUTPUT->footer(); 
