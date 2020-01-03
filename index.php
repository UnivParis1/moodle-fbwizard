<?php 
require_once(__DIR__ . '/../../config.php');
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
	
	$cats = getListeComposantes() ;
	$select = '<select name="composante" id="composante"><option value="0" selected>--</option>';
	foreach($cats as $i=>$row) {
		$select .= '<option value="'.$row->id.'">'.$row->name.'</option>';
	}
	$libelle_choose_cat = get_string('choose_cat', 'local_fbwizard');
	$libelle_valider = get_string('ok', 'local_fbwizard');
	$select .= '</select>';
$form = <<< EOF
<form action="step1.php" method="POST" >
	<h3> $libelle_choose_cat $select <span id="span_1">&nbsp;</span><input type="submit" class="button-action" name="select_mail" value="valider" ></h3>

</form>
EOF;
	echo $form; // insertion du formulaire dans la page

echo '</br></br>
	<ul>
	<li><a href="listes.php">Accéder à la liste des listes SYMPA</a></li>
	<li><a href="global_report_eee.php">Accéder aux rapports statistiques</a></li>
	</ul>
';
 
}
echo $OUTPUT->box_end();
echo $OUTPUT->footer(); 
