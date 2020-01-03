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
	return rtrim(strip_tags($chaine));
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
	$array_csv = array();
	$items = array();
	if (!empty($_GET['id'])) {
		$cptl=0;
		$cptc=6;
		$courseid=$_GET['id'];
		
		$sql = " select * from {fbwizard} where courseid=?";
		$metas = $DB->get_record_sql($sql,array($courseid));
                $cat = getComposante( $metas->category);
                $cat_idnumber_array = explode('/',$cat['idnumber']);
                $ufr = $cat_idnumber_array[count($cat_idnumber_array)-1];
		$lib_course = $metas->lib_etp;
		$cod_etp =  $metas->cod_etp;
		$cod_vrs_vet = $metas->cod_vrs_vet;
		$cod_tpd_etb = $metas->cod_tpd_etb;
		$niveau = getNiveau($cod_etp, $cod_vrs_vet ,$cod_tpd_etb);

		$array_csv[$cptl][0]='Userid';
		$array_csv[$cptl][1]='UFR';
                $array_csv[$cptl][2]='Nom du cours';         
                $array_csv[$cptl][3]='COD_ETP';         
                $array_csv[$cptl][4]='COD_VRS_VET';         
                $array_csv[$cptl][5]='COD_TPD_ETB';         
                $array_csv[$cptl][6]='niveau';         
		

		$select = "select distinct fi.label, fi.name from mdl_feedback f inner join mdl_feedback_item fi on (f.id=fi.feedback) where f.course=? and fi.name!='' and fi.label not like 'com%' and fi.name!='label'";
		$obj_items = $DB->get_records_sql($select,array($courseid));
		foreach ($obj_items as $i=>$row) {
			$items[$cptc]['label'] = $row->label;
			$items[$cptc]['name'] = $row->name;
			$cptc++;
			$array_csv[$cptl][$cptc]='('.$row->label.') '.$row->name;
		} 
		
		
		$sql_users = "	SELECT distinct fbc.userid
							FROM mdl_feedback_value fbv, mdl_feedback_completed fbc, mdl_feedback f, mdl_feedback_item fi
							WHERE f.course=$courseid
							 	AND fbv.completed = fbc.id
							 	AND fbv.item=fi.id
							 	AND fi.feedback = f.id";
		$users = $DB->get_records_sql($sql_users);	





	
		foreach( $users as $u=>$user){
			$cptl++;
			$userid =$user->userid;
			$array_csv[$cptl][0]=$user->userid;
                	$array_csv[$cptl][1]=$ufr;
                	$array_csv[$cptl][2]=$lib_course;
                	$array_csv[$cptl][3]=$cod_etp;
                	$array_csv[$cptl][4]=$cod_vrs_vet;
                	$array_csv[$cptl][5]=$cod_tpd_etb;
                	$array_csv[$cptl][6]=$niveau;

			for($p=7;$p<count($items)+7;$p++ ){ 
				$array_csv[$cptl][$p] = '-';
				$sql_answers = "	SELECT fi.id, fi.name, fi.label, fi.presentation, fi.typ, fbv .  *
						FROM mdl_feedback_value fbv, mdl_feedback_completed fbc, mdl_feedback f, mdl_feedback_item fi
						WHERE f.course=$courseid
						 	AND fbv.completed = fbc.id
						 	AND fbv.item=fi.id
						 	AND fi.feedback = f.id
						 	AND fbc.userid = $userid
							AND fi.label like '".$items[$p-1]['label']."'
							and fi.name!='label'
						ORDER BY fbc.userid, fi.position;";
				$answers = $DB->get_records_sql($sql_answers);
				if (!empty($answers)) {
					foreach($answers as $a=>$answer) {
						if ($answer->typ== 'multichoice') {
							$presentation = str_replace("r>>>>>", "", $answer->presentation);
							$presentation = str_replace("<<<<<1", "", $presentation);
							$ans_array = explode("|",$presentation);
							if (!empty($ans_array[intval($answer->value) - 1]))
								$array_csv[$cptl][$p]= rtrim(html_entity_decode(strip_tags( $ans_array[intval($answer->value) - 1])));
			
						} else {
							$array_csv[$cptl][$p]=html_entity_decode(strip_tags( Nettoyer_chaine($answer->value)));
						}
					}					
				}	
			}
		}
		$sql = " select * from {fbwizard} where courseid=?";
		$metas = $DB->get_record_sql($sql,array($courseid));
		$cod_etp =  $metas->cod_etp;
		$cod_vrs_vet = $metas->cod_vrs_vet;
		$cod_tpd_etb = $metas->cod_tpd_etb;
		download_send_headers($cod_etp."_".$cod_vrs_vet."_" . date("Y-m-d") . ".csv");	
		echo array2csv($array_csv);
		exit();
	}
}	
