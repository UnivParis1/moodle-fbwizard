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
	
 
}
echo $OUTPUT->box_end();
echo $OUTPUT->footer(); 
