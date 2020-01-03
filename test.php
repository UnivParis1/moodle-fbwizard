<?php 
require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once('../../lib/accesslib.php');
require_once('locallib.php');
require_once('apogee.class.php');
require_login();
global $DB, $CFG, $PAGE, $OUTPUT, $SESSION, $USER;
$obj_apogee = new apogee_connecteur();

if (is_siteadmin()) {
	////////////////// TEST CREATION COURS ///////////////
	/*
	$liste_formations[0] = array('category' =>66, 'cod_tpd_etb' => 'D2','cod_etp' => 'D2H1B1','cod_vrs_vet' => '114','lib_etp' => 'Double Licence 1ère année Géographie & Aménagement-Economie');
	$liste_formations[1] = array('category' => 66, 'cod_tpd_etb' => 'D2','cod_etp' => 'D2H1J1','cod_vrs_vet' => '114','lib_etp' => 'Double Licence 1ère année Géographie & Aménagement-Histoire');
	$liste_formations[2] = array('category' => 66, 'cod_tpd_etb' => 'DP','cod_etp' => 'LPH301','cod_vrs_vet' => '115','lib_etp' => 'Lic Pro MPGE parcours Géomatique et environnement');
	$liste_formations[3] = array('category' => 66, 'cod_tpd_etb' => 'L2','cod_etp' => 'L2H101','cod_vrs_vet' => '115','lib_etp' => 'Licence 1ère année Géographie et aménagement parc Géographie');
	$liste_formations[4] = array('category' => 66, 'cod_tpd_etb' => 'L2','cod_etp' => 'L2H201','cod_vrs_vet' => '115','lib_etp' => 'Licence 2ème année Géographie');
	$liste_formations[5] = array('category' => 66, 'cod_tpd_etb' => 'L3','cod_etp' => 'L3H304','cod_vrs_vet' => '115','lib_etp' => 'Licence 3è année Géo & aménagt parc. Aménagement (Magistère)');
	
	//inserer_liste_cours_a_creer($liste_formations)
	//create_coursee();
	*/	
	/////////////////// TEST ARBRE ROF /////////////////////
	$t = $obj_apogee->getListSemestres2('L3H301','114');
	for($i=0;$i<count($t);$i++) {
		$o = array();
		echo $t[$i]['COD_ELP'].'<br />';
		$obj_apogee->GetilsElp($t[$i]['COD_ELP'],null,$o);
		echo '<pre>';
		print_r($o);
		echo '</pre>';
	}
	
	

	/*
    $liste_course_deployed = $DB->get_records('fbwizard', array('deployed' => 1));
    $data =array();
    $i=0;
    foreach($liste_course_deployed as $i=>$row) {
    	$niv = $obj_apogee->getCodSisDaaMin($row->cod_etp, $row->cod_vrs_vet ,$row->cod_tpd_etb) ;
    	$data[$i]['cod_etp'] = $row->cod_etp;
    	$data[$i]['cod_vrs_vet'] = $row->cod_vrs_vet;
    	$data[$i]['cod_tpd_etb'] = $row->cod_tpd_etb;
    	$data[$i]['niveau'] = $niv;
    	$idmodele = getIdModèleByCodTpdEtb($row->cod_etp, $row->cod_vrs_vet ,$row->cod_tpd_etb) ;
    	$data[$i]['modele'] = $idmodele;
    	$i++;
    }
	echo '<pre>';
	print_r($data);
	echo '</pre>';
	*/
}
