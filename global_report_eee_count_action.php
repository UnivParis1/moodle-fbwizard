<?php
/**
 * Création d'un CSV comptant le nombre de réponse
 * @author Isham Yagoub
 * @version 1.0
 * @
 * historique : 26/12/2019 - première version du fichier 
 * This script create a CSV file wich report the count of all answers of feedbacks filled by student
 * 1st line : Date and time 
 * 
* */

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
//ini_set('memory_limit', '-1');

//$url = new moodle_url('/local/fbwizard/global_report_eee_count_action.php');
//$PAGE->set_url($url);

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

	$listDate = array();
	$counter = array();
	if (!empty($_POST['idcategorie'])) {
		$idCategory = $_POST['idcategorie'];
		$idCourse= getCourseIdsForCategory($idCategory);

		foreach ($idCourse as $info){
			$result = getNbReponseByCourse($info->id);
			
			foreach ($result as $id=>$reponse)
			{
				$idCourse = $info->id;
				$courseName = $info->shortname;
				$thisDate = date("Ymd",$reponse->timemodified);
				
				if (empty($listDate[$thisDate]))
					$listDate[$thisDate] = $thisDate;
				$counter[$idCourse]["courseName"]= $courseName;
				if (!empty($counter[$idCourse][$thisDate])) 
					$counter[$idCourse][$thisDate]+= 1;
				else 
					$counter[$idCourse][$thisDate]=1;

			}
		}
		asort($listDate);
		$cptLine = 1;
		$cptColumn = 1;
		$array_csv[$cptLine][$cptColumn]='';
		$cptColumn ++;	
		foreach ($listDate as $key=>$answerDate){
			$array_csv[$cptLine][$cptColumn]= date("d/m/Y",strtotime($answerDate));
			$cptColumn ++;
		}
		$array_csv[$cptLine][$cptColumn]= "Total";
 		$cptColumn ++;
		$cptLine++;
		foreach ( $counter as $idCourse=>$answers){
			$cptColumn=1;
 			$array_csv[$cptLine][$cptColumn]=Nettoyer_chaine($answers["courseName"]);
			ksort($answers);
			$cptTotal = 0;
			$cptIndex =0;
			foreach ($answers as $answerDate=>$nb){
				$cptColumn=1;
				$cptIndex++;
				if ($answerDate != "courseName")
				{
					while (date("d/m/Y",strtotime($answerDate)) != $array_csv[1][$cptColumn]){
						if (empty($array_csv[$cptLine][$cptColumn]))
					     		$array_csv[$cptLine][$cptColumn]=0;
						$cptColumn++;
					}
					if (date("d/m/Y",strtotime($answerDate)) == $array_csv[1][$cptColumn])
					{
						$array_csv[$cptLine][$cptColumn]=$nb;
						$cptColumn++;
						$cptTotal+=$nb;
					}
					
					if ($cptIndex == sizeof($answers) )
					{
						while ($cptColumn <= sizeof($listDate)+1)
						{
							if (empty($array_csv[$cptLine][$cptColumn]))
                        	                        {
								$array_csv[$cptLine][$cptColumn]=0;
                                       				$cptColumn++;
							}
						}
					}
				}		

			}
			 $array_csv[$cptLine][$cptColumn]=$cptTotal ;
	
			$cptLine++;
		}

	}
	else if(!empty( $_GET['id'])){
		$result = getNbReponseByCourse($_GET['id']);
		$cptTotal = 0;

                foreach ($result as $id=>$reponse)
           	{
			$courseName = Nettoyer_chaine($reponse->fullname);
                        $thisDate = date("Ymd",$reponse->timemodified);

		        if (empty($listDate[$thisDate]))
                        	$listDate[$thisDate] = $thisDate;
                        $counter["courseName"]= $courseName;
                        if (!empty($counter[$thisDate]))
                                $counter[$thisDate]+= 1;
                        else
                       	        $counter[$thisDate]=1;

                }
		$array_csv[1][1]=" ";
		$array_csv[2][1]=Nettoyer_chaine($counter["courseName"]);
		$cptColumn=2;
		foreach ( $counter as $dateAnswer=>$nbAnswer){
			
			if ($dateAnswer != "courseName"){
				$array_csv[1][$cptColumn]=date("d/m/Y",strtotime($dateAnswer));
				$array_csv[2][$cptColumn]=$nbAnswer;
				$cptColumn++;
				$cptTotal+=$nbAnswer;
			}
		}
		$array_csv[1][$cptColumn]="Total";
                $array_csv[2][$cptColumn]=$cptTotal;
	}
download_send_headers("data_export_" . date("Y-m-d") . ".csv");
echo array2csv($array_csv);

}

