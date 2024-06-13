<?php
// This file is part of a plugin for Moodle - http://moodle.org/

/**
 * @package    local
 * @subpackage fbwizard
 * @copyright  2012-2013 Silecs {@link http://www.silecs.info/societe}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


define('CLI_SCRIPT', true);
require_once(__DIR__ . '/../../config.php');
require_once('locallib.php');
require_once('apogee.class.php');
require_once($CFG->libdir.'/clilib.php');      // cli only functions
// now get cli options
list($options, $unrecognized) = cli_get_params(array(
        'help'=>false, 'count'=>false, 'init'=>false ),
    array('h'=>'help', 'c'=>'count', 'i'=>'init'));

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}


$help =
"Deploy feedbacks  from PAGS webservice. Normally, to be executed by a cron job.

Options:
-c, --count           Return the number of course to deploy
-i, --init            Apply to all course to eploy
-h, --help            Print out this help


Example:
/usr/bin/php local/fbwizard/create_feedback_cli.php --init

";

if ( ! empty($options['help']) ) {
    echo $help;
    return 0;
}
 echo $help;

// Ensure errors are well explained
$CFG->debug = DEBUG_NORMAL;

if ( $options['count'] ) {
    $nb = count_courses();
    echo $nb;
    return 0;
}

if ( $options['init'] ) {
    $since = 0;
    create_courses(); 
    return 0;
}

