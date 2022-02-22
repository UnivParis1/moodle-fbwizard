<?php
/**
 * @package    local
 * @subpackage fbwizard
 * @copyright  2012-2013 Silecs {@link http://www.silecs.info/societe}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// doc https://moodle.org/mod/forum/discuss.php?d=170325#yui_3_7_3_2_1359043225921_310
function local_fbwizard_extend_navigation(global_navigation $navigation) {
    global $USER, $PAGE;


    $context = $PAGE->context;

if (is_siteadmin()) {
        $node1 = $navigation->add('Déploiement automatique de feedbacks');
        $node2 = $node1->add('Création ', new moodle_url('/local/fbwizard/index.php'));
        $node2 = $node1->add('Rapport ', new moodle_url('/local/fbwizard/global_report_eee.php'));
    } 
}
