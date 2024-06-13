<?php
require_once("../../config.php");
require_once("../../mod/feedback/lib.php");
require_once('locallib.php');
require_login();
global  $CFG,$DB;
$idcategorie=0;
$url = new moodle_url('/local/fbwizard/global_report_eee.php');
$PAGE->set_url($url);
$context = context_user::instance($USER->id);
$PAGE->set_context($context);
$PAGE->navbar->add(get_string('analysis', 'feedback'));
$PAGE->set_heading("REPORT GLOBAL - Choix de la catégorie");
$PAGE->set_title("REPORT GLOBAL");
$PAGE->set_pagelayout('report');
echo $OUTPUT->header();
echo $OUTPUT->heading("RAPPORT DES RÉPONSES DES FEEDBACKS");

/**
 * vérification que l'utilisateur est un administrateur
 */

if (is_siteadmin()) {
echo $OUTPUT->box_start('generalbox boxaligncenter boxwidthwide');
	echo '<h2>Rapport global</h2>';
	if (!empty($_POST['idcategorie'])) $idcategorie = $_POST['idcategorie'];
	$sql= "SELECT id, name from mdl_course_categories where parent=0;";
	$cats = $DB->get_records_sql($sql);
	$select = '<select name="idcategorie" id="idcategorie">';
	if ($idcategorie == 0) $select .= '<option value="0" selected>--</option>'; else $select .= '<option value="0">--</option>';
	foreach($cats as $i=>$row) {
		if ($idcategorie == $row->id) $select .= '<option value="'.$row->id.'" selected>'.$row->name.'</option>'; else $select .= '<option value="'.$row->id.'">'.$row->name.'</option>';
	}
	$select .= '</select>';
$form = <<< EOF
<form action="global_report_eee_action.php" method="POST" >
	<dl>
	<dt><label>Choisir la catégorie: </label></dt>
	<dd>$select</dd>
	<dt></dt>
	<dd><input type="submit" value="Valider"></dd>
	</dl>
</form>
EOF;
	echo $form; // insertion du formulaire dans la page

$form = <<< EOF
<form action="global_report_eee_count_action.php" method="POST" >
        <dl>
        <dt><label>Reporting quotidien</br> Choisir la catégorie: </label></dt>
        <dd>$select</dd>
        <dt></dt>
        <dd><input type="submit" value="Valider"></dd>
        </dl>
</form> 
EOF;
    echo $form; // insertion du formulaire dans la page

	echo $OUTPUT->box_end();
	echo $OUTPUT->box_start('generalbox boxaligncenter boxwidthwide');
	echo '<h2>Rapport par formation</h2>';
        $category_model = get_config("local_fbwizard", "category_model");
        $select = "  select distinct category 
                     from {fbwizard} fb 
                     inner join {course_categories} c on (fb.category = c.id)
                     where c.path like ?
                     order by category";
        $obj =$DB->get_records_sql($select, array('%/'.$category_model.'/%'));

        foreach ($obj as $i=>$row) {
                $cat = getComposante($row->category);
                echo '<h3>'.$cat['name'].'</h3>';
		$select = "select fb.* from {fbwizard} fb  inner join {course} c on c.id=fb.courseid where fb.category=? order by lib_etp";
		$obj2 = $DB->get_records_sql($select,array($row->category));
		$table = new html_table();
		$table->head = array('Libellé','Nbre de réponse','Rapport','Reporting quotidien');
		$data = array();
		foreach($obj2 as $j=>$row2) {
			$completedscount = get_completeds_group_count($row2->courseid);
			$data[] = array(
				'<a href="'.$CFG->wwwroot.'/course/view.php?id='.$row2->courseid.'" target="_BLANK">'.$row2->lib_etp.'</a>',
				$completedscount,
				'<a href="global_report_eee_action2.php?id='.$row2->courseid.'" target="_BLANK">Rapport</a>',
				'<a href="global_report_eee_count_action.php?id='.$row2->courseid.'">Télécharger</a>'
				);
		}
		$table->data = $data;
		echo html_writer::table($table);	
        }
echo $OUTPUT->box_end();
}
echo $OUTPUT->footer();
