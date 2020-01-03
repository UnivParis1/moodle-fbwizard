<?php
/**
 * Création d'un CSV pour un export global
 * @author El-Miqui CHEMLALI
 * @version 1.0
 * @
 * historique : 05/01/2015 - première version du fichier 
 * This script create a CSV file wich report the sum of all answers of feedbacks filled by students
 * 1st line : Date and time 
 * Culums follows this schéma
 * URF | Diplôme LMD | Niveau | Semestre | Question 1 | Réponse 1 | Nombre de répondant à la Réponse 1 | Moyenne de répondant à la réponse 1| ... 
 * ------------------> Ainsi de suite pour toutes les réponses
 */
function download_send_headers($filename) {
    // disable caching
    $now = gmdate("D, d M Y H:i:s");
    header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
    header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
    header("Last-Modified: {$now} GMT");
    header('Content-Encoding: UTF-8');
    header('Content-type: text/csv; charset=UTF-8');
    // force download  
    header("Content-Type: application/force-download");
    header("Content-Type: application/octet-stream");
    header("Content-Type: application/download");

    // disposition / encoding on response body
    header("Content-Disposition: attachment;filename={$filename}");
    header("Content-Transfer-Encoding: binary");
}
function array2csv(array &$array) {
   if (count($array) == 0) {
     return null;
   }
   ob_start();
   $df = fopen("php://output", 'w');
   fputcsv($df, array_keys(reset($array)));
   foreach ($array as $row) {
      fputcsv($df, $row);
   }
   fclose($df);
   return ob_get_clean();
}
function Nettoyer_chaine($chaine) {
	$chaine = str_replace('#039;', "'", $chaine);
	$chaine = str_replace(';', ',', $chaine);
	$chaine = html_entity_decode($chaine, ENT_QUOTES);
	$chaine = rtrim($chaine);
	return strip_tags($chaine);
}
require_once("../../config.php");
require_once("../../mod/feedback/lib.php");
require_once('apogee.class.php');
require_once('locallib.php');
require_login();

/**
 * vérification que l'utilisateur est un administrateur
 */
if (is_siteadmin()) {
	$fist_line_of_course=true;
	$idcategorie = 0;
	$array_csv = array();
	$cpt = 0;
	$fichier = '/tmp/feedback-'.time().'.csv';
	$delimiter = ";";
	if (!empty($_POST['idcategorie'])) $idcategorie = $_POST['idcategorie'];
		
 //si on a séléctionné une catégoorie de premier niveau déterminer la liste des ufr et pour chaque ufr la liste des diplômes et pour chaque diplôme le niveau et pour chaque niveau le cours 
	if (!empty($_POST['idcategorie'])) {
		//initialisation de la deuxième ligne : les titres de colonnes
		$array_csv[$cpt][0]='Cours';
		$array_csv[$cpt][1]='UFR';
		$array_csv[$cpt][2]='COD_ETP';
		$array_csv[$cpt][3]='COD_VRS_VET';
		$array_csv[$cpt][4]='Niveau';
		$array_csv[$cpt][5]='Utilisateur';
		$cpt_col_item = 5; 
		for ($numero_item=1;$numero_item < 22; $numero_item++) {
			$sql_item = "SELECT distinct name  FROM mdl_feedback_item WHERE label LIKE 'com$numero_item'";
			$nomitems = $DB->get_records_sql($sql_item);
			$out = true;
			foreach($nomitems as $n=>$nomitem) {
				 if ($out) {
				 	if (!empty($nomitem->name)) $array_csv[$cpt][$cpt_col_item+$numero_item]=$nomitem->name;
				 	$out = false;
				 }
			}
			
		}
		$cpt++;
		// recherche de tous les cours avec feedbacks de la catégorie séléctionnée
		$sql_course = "	SELECT c.id as id, c.category as category, c.fullname as fullname, f.id as feedbackid 
						FROM mdl_course c, mdl_feedback f 
						WHERE c.id=f.course 
						AND c.category in (select id from mdl_course_categories where path like '/$idcategorie/%' ) 
						;";
		
		$courses = $DB->get_records_sql($sql_course);
		foreach ($courses as $i=>$rowcourse) {
			$courseid = $rowcourse->id;
			$coursename = $rowcourse->fullname;

			// 
			$sql = " select * from {fbwizard} where courseid=?";
			$metas = $DB->get_record_sql($sql,array($courseid));
                        $cat = getComposante( $metas->category);
                        $cat_idnumber_array = explode('/',$cat['idnumber']);
                        $ufr = $cat_idnumber_array[count($cat_idnumber_array)-1];
			$cod_etp =  $metas->cod_etp;
			$cod_vrs_vet = $metas->cod_vrs_vet;
			$cod_tpd_etb = $metas->cod_tpd_etb;
			$niveau = getNiveau($cod_etp, $cod_vrs_vet ,$cod_tpd_etb);
			//insertion des items par user
			$sql_users = "	SELECT distinct fbc.userid
							FROM mdl_feedback_value fbv, mdl_feedback_completed fbc, mdl_feedback f, mdl_feedback_item fi
							WHERE f.course=$courseid
							 	AND fbv.completed = fbc.id
							 	AND fbv.item=fi.id
							 	AND fi.feedback = f.id";
			$users = $DB->get_records_sql($sql_users);
			foreach( $users as $u=>$user){
				$array_csv[$cpt][0]=$coursename;
				$array_csv[$cpt][1]=$ufr;
				$array_csv[$cpt][2]=$cod_etp;
				$array_csv[$cpt][3]=$cod_vrs_vet;
				$array_csv[$cpt][4]=$niveau;
				$array_csv[$cpt][5]=$user->userid;
				/**
				 * El-Miqui : Correction Bug n°: 43783
				 */
				for($p=6;$p<26;$p++ ){ 
						$array_csv[$cpt][$p] = '-';
				}
				/*
				 * FIN CORRECTION
				 */

//echo $courseid.'<br />';
				$userid =$user->userid;
				$sql_answers = "	SELECT fi.id, fi.name, fi.label, fi.presentation, fi.typ, fbv .  *
									FROM mdl_feedback_value fbv, mdl_feedback_completed fbc, mdl_feedback f, mdl_feedback_item fi
									WHERE f.course=$courseid
									 	AND fbv.completed = fbc.id
									 	AND fbv.item=fi.id
									 	AND fi.feedback = f.id
									 	AND fbc.userid = $userid
										AND fi.label like 'com%'
									ORDER BY fbc.userid, fi.position;";
				$answers = $DB->get_records_sql($sql_answers);
				foreach($answers as $a=>$answer) {
					if ($answer->typ== 'multichoice') {
						$presentation = str_replace("r>>>>>", "", $answer->presentation);
						$presentation = str_replace("<<<<<1", "", $presentation);
						$ans_array = explode("|",$presentation);
						if (!empty($ans_array[intval($answer->value) - 1]))
							$array_csv[$cpt][5+intval(str_replace('com','',str_replace('commun','',$answer->label)))]= rtrim(html_entity_decode(strip_tags( $ans_array[intval($answer->value) - 1])));

					} else {
						$array_csv[$cpt][5+intval(str_replace('com','',str_replace('commun','',$answer->label))    )]=html_entity_decode(strip_tags( Nettoyer_chaine($answer->value)));
					}
				}
				$cpt++;
			}
			//
			$cpt++;
		}
	}
//print_object($array_csv);
download_send_headers("data_export_" . date("Y-m-d") . ".csv");
echo array2csv($array_csv);
exit();
}
