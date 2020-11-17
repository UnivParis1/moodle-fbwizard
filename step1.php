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

echo html_writer::script("","https://code.jquery.com/jquery-1.11.0.min.js");
echo html_writer::script("","js/ajax.js");
$idcategorie=0;
$url = new moodle_url('/local/fbwizard/step1.php');
$PAGE->set_url($url);
$context = context_system::instance();
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
	if (!empty($_POST['composante'])) {
		$cat= getComposante($_POST['composante']);
		//Recherche du COD_CMP
		$exp = explode('/', $cat['idnumber']);
		$COD_CMP = $exp[count($exp)-1];
		echo '<h2>'.$cat['name'].'</h2>';
		echo '<form action="step2.php" method="POST">';

		$obj = new apogee_connecteur();
		// Recherche des formations à déployer de la composante
		$liste_formations = $obj->getLicencesByCMP($COD_CMP,$_POST['composante']);
		/*
		// For testing 
		$liste_formations = array();
		$liste_formations[0] = array('category' => $_POST['composante'], 'cod_tpd_etb' => 'D2','cod_etp' => 'D2H1B1','cod_vrs_vet' => '114','lib_etp' => 'Double Licence 1ère année Géographie & Aménagement-Economie');
		$liste_formations[1] = array('category' => $_POST['composante'], 'cod_tpd_etb' => 'D2','cod_etp' => 'D2H1J1','cod_vrs_vet' => '114','lib_etp' => 'Double Licence 1ère année Géographie & Aménagement-Histoire');
		$liste_formations[2] = array('category' => $_POST['composante'], 'cod_tpd_etb' => 'DP','cod_etp' => 'LPH301','cod_vrs_vet' => '115','lib_etp' => 'Lic Pro MPGE parcours Géomatique et environnement', 'category' => $_POST['composante']);
		$liste_formations[3] = array('category' => $_POST['composante'], 'cod_tpd_etb' => 'L2','cod_etp' => 'L2H101','cod_vrs_vet' => '115','lib_etp' => 'Licence 1ère année Géographie et aménagement parc Géographie');
		$liste_formations[4] = array('category' => $_POST['composante'], 'cod_tpd_etb' => 'L2','cod_etp' => 'L2H201','cod_vrs_vet' => '115','lib_etp' => 'Licence 2ème année Géographie');
		$liste_formations[5] = array('category' => $_POST['composante'], 'cod_tpd_etb' => 'L3','cod_etp' => 'L3H304','cod_vrs_vet' => '115','lib_etp' => 'Licence 3è année Géo & aménagt parc. Aménagement (Magistère)');
		
		////////////////// TEST CREATION COURS ///////////////
		create_coursee( $_POST['composante'],'Licence 3è année Géo & aménagt parc. Aménagement (Magistère)', 'L3H304','L3');
		*/
		foreach ($liste_formations as $key=>$value) {
			$cohorte = $value['category'];
			$cohorte = getCohorte($value['cod_etp']);
			if (!empty($cohorte[0][0])) {
				$liste_formations[$key]['cohortes'] = '<span>'.$cohorte[0][1].'</span>
						<input type="hidden" name="'.$value['cod_etp'].'_cohorte" value="'.$cohorte[0][0].'">';
			} else {
				$liste_formations[$key]['cohortes'] = '';
			}
			if (!isdeclaredToDeploy($value['category'],$value['cod_tpd_etb'],$value['cod_etp'],$value['cod_vrs_vet']))
				$liste_formations[$key]["checkbox"] = '<input type="checkbox" name="'.$value['cod_etp'].'">';
			else {
				$liste_formations[$key]["checkbox"]  = '';
			}
		}
		$SESSION->fbwizard = $liste_formations;
		echo ' <label for="search">Trier :</label> <input type="text" id="search" placeholder="Rechercher">';
		$table2 = new html_table();
		$table2->id = "mytable";
		$table2->head = array('Category', 'COD_TPD_ETB','COD_ETP', 'COD_VRS_VET','LIB_ETP','COHORTE ASSOCIEE','&nbsp;');
		$table2->data = $liste_formations;
		echo html_writer::table($table2);	
		echo '<input type="submit" class="button-action" name="select_mail" value="valider" >';
	}
 
}
echo $OUTPUT->box_end();
echo $OUTPUT->footer(); 
 