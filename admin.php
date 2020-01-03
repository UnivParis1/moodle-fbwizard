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
$url = new moodle_url('/local/fbwizard/admin.php');
$PAGE->set_url($url);
$context = context_system::instance();;
$PAGE->set_context($context);
$PAGE->set_pagelayout('report');

/**
 * vérification que l'utilisateur est un administrateur
 */

if (is_siteadmin()) {
	$annee = 0;	
	if (!empty($_POST['idcohorte_2017']) && !empty($_POST['idcohorte'])  
				&& !empty($_POST['courseid'])  ) {
		updatecohort($_POST['courseid'],$_POST['idcohorte'],$_POST['idcohorte_2017']);
		}

	$PAGE->set_heading(get_string('heading_index', 'local_fbwizard'));
	$PAGE->set_title(get_string('title_index', 'local_fbwizard'));

	echo $OUTPUT->header();
	echo $OUTPUT->box_start('generalbox boxaligncenter boxwidthwide');
	//Recherche du COD_CMP
	echo '<h2>Etat des feedbacks déployés</h2>';
	$liste_formations=getAll() ;
	$data=array();
	foreach ($liste_formations as $key=>$value) {
		$data[$key]['UFR'] = $value['category'];
		$data[$key]['cours'] = '<a href="https://eee.univ-paris1.fr/course/view.php?id='.$value['courseid'].'" target="_BLANK">'.$value['lib_etp'].'</a>';
		$data[$key]['cohorte_name'] = '<a href="https://eee.univ-paris1.fr/enrol/instances.php?id='.$value['courseid'].'" target="_BLANK">'.$value['nom_cohorte'].'</a>';
		$data[$key]['cohorte_idnumber'] = $value['idnumber_cohorte'];
		if (!empty( $value['idnumber_cohorte_2017'])) {
		  $data[$key]['modifier_cohorte'] = '
			<form action="admin.php" method="POST">
			<input type="hidden" name="idcohorte_2017" value="'. $value['idcohorte_2017'].'">
                        <input type="hidden" name="nom_cohorte_2017" value="'. $value['nom_cohorte_2017'].'">
                        <input type="hidden" name="idnumber_cohorte_2017" value="'. $value['idnumber_cohorte_2017'].'">

                        <input type="hidden" name="idcohorte" value="'. $value['idcohorte'].'">
                        <input type="hidden" name="nom_cohorte" value="'. $value['nom_cohorte'].'">
                        <input type="hidden" name="idnumber_cohorte" value="'. $value['idnumber_cohorte'].'">

                        <input type="hidden" name="courseid" value="'. $value['courseid'].'">


			<p>Remplacer cette cohorte par '.$value['nom_cohorte_2017'].'</p>

                        <input type="submit" class="button-action" name="remplacer" value="remplacer" >
			</form>
		  ';
		} else {
			$data[$key]['modifier_cohorte'] = '';
		}
	}
	$table2 = new html_table();
	$table2->head = array('UFR', 'Cours','Libelle de la cohorte', 'idnumber de la cohorte', 'Modification de cohorte');
	$table2->data = $data;
	echo html_writer::table($table2);	
 
}
echo $OUTPUT->box_end();
echo $OUTPUT->footer(); 
