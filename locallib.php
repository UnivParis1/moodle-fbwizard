<?php
/**
 * @author 		El-Miqui CHEMLALI <el-miqui.chemlali@univ-paris1.fr>
 * @package    	local
 * @subpackage 	fbwizard
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php'); // global moodle config file.
require_once($CFG->dirroot.'/course/lib.php');
require_once($CFG->libdir.'/coursecatlib.php');
require_once($CFG->libdir.'/custominfo/lib_data.php');
require_once($CFG->dirroot.'/local/crswizard/lib_wizard.php');
require_once($CFG->dirroot.'/local/crswizard/wizard_modele_duplicate.class.php');
require_once($CFG->dirroot.'/local/crswizard/wizard_core.class.php');
require_once($CFG->dirroot.'/local/roftools/roflib.php');
global $CFG;

function updatecohort($courseid,$oldchortid,$newcohortid) {
	global $DB;
	$update  = " UPDATE mdl_enrol set customint1= $newcohortid
			WHERE courseid=$courseid and enrol='cohort' 
			and customint1=$oldchortid";
	$DB->execute($update);
	$update2 = " UPDATE mdl_fbwizard set cohorte= $newcohortid
                         WHERE courseid=$courseid and cohorte=$oldchortid";
	$DB->execute($update2);
}


/**
 * 
 * Retourne les pères de toutes les catégories
 * @return array 
 */
function getListeComposantes() {
	global $DB;
	$SELECT = "	SELECT id, name, parent FROM {course_categories} where depth=3 and path like ? order by name";
	$listeFathers= $DB->get_records_sql($SELECT, array('/'.get_config("local_fbwizard", "category_model").'%'));
	$result = array();
	foreach ($listeFathers as $key => $value) {
		$value->name = getPreviousNameOfCategorie($value->parent,2) . ' > ' . $value->name;
		$result[] = $value;
	}
    return $result;
}



function getInfoCohorte($id) {
	global $DB;
	$select = "SELECT id, name, idnumber FROM {cohort} WHERE id = ?";
	$obj = $DB->get_records_sql($select,array($id));
	$result = array();
	foreach ($obj as $key => $value) {
		$result[] = array($value->id, $value->name, $value->idnumber);
	}
    return $result;
	
}
function getAll() {
	global $DB;
	$SELECT = "	SELECT * FROM {fbwizard} order by category";
	$listeFb= $DB->get_records_sql($SELECT);
	$result = array();
	$i=0;
	foreach ($listeFb as $key => $value) {
		$cohorte =  getInfoCohorte($value->cohorte) ;
		$cat = getNameOfCategory($value->category,2) ;
		$result[$i]['categoryid'] = $value->category;
		$result[$i]['category'] = $cat;
		$result[$i]['lib_etp'] = $value->lib_etp;
		$result[$i]['idcohorte'] = $cohorte[0][0];
		$result[$i]['nom_cohorte'] = $cohorte[0][1];
		$result[$i]['idnumber_cohorte'] = $cohorte[0][2];
		$result[$i]['courseid'] = $value->courseid;
		// Est ce qu'il s'agit d'une cohorte de 2017
		$exp = explode ( '-', $result[$i]['idnumber_cohorte']);
		$result[$i]['idcohorte_2017'] = '';
                $result[$i]['nom_cohorte_2017'] = '';
                $result[$i]['idnumber_cohorte_2017'] = '';	
		if (count($exp)>2){
			if ($exp[2] != '2017' ) {
				print_object($liste_cohorte);
				$liste_cohorte = getCohorte($value->cod_etp);
				$result[$i]['idcohorte_2017'] = $liste_cohorte[count($liste_cohorte)-1][0];
                		$result[$i]['nom_cohorte_2017'] = $liste_cohorte[count($liste_cohorte)-1][1];
                		$result[$i]['idnumber_cohorte_2017'] = $liste_cohorte[count($liste_cohorte)-1][2]; 
					
			}
		}

		unset($cat);
		unset($cohorte);
		$i++;
	}
    return $result;
}

/**
 * 
 * Retourne les pères de toutes les catégories
 * @return array 
 */
function getComposante($id) {
	global $DB;
	$SELECT = "	SELECT name, idnumber FROM {course_categories} where id=? order by name";
	$obj = $DB->get_record_sql($SELECT,array($id));
	$cat = array();
	(empty($obj->name))?$cat['name']='':$cat['name']=$obj->name;
	(empty($obj->idnumber))?$cat['idnumber']='':$cat['idnumber']=$obj->idnumber;
    return $cat;
}

/**
 * 
 * Retourne le nom du père de la catégorie
 * @return string 
 */
function getPreviousNameOfCategorie($id,$level) {
	global $DB;
		$select = "SELECT name,parent FROM {course_categories} WHERE id = ?";
		$obj = $DB->get_record_sql($select,array($id));
	if ($level == 1) {
		return $obj->name;	
	} elseif ($level == 2) {
		return  getPreviousNameOfCategorie($obj->parent,1) . ' > ' . $obj->name;
	}
	return '';
}


function getNameOfCategory($id) {
	global $DB;
		$select = "SELECT name FROM {course_categories} WHERE id = ?";
		$obj = $DB->get_record_sql($select,array($id));
		return $obj->name;	
}

/**
 * 
 * Retourne l'id du père de la catégorie
 * @return string 
 */
function getParentIdOfCategorie($id) {
	global $DB;
	$select = "SELECT parent FROM {course_categories} WHERE id = ?";
	$obj = $DB->get_record_sql($select,array($id));
	return $obj->parent;	

}

/**
 * Retourne les infos liés à une cohorte d'une étape
 * @return Array
 */
function getCohorte($cod_elp) {
	global $DB;
	$select = "SELECT id, name, idnumber FROM {cohort} WHERE idnumber like ?  order by id desc";
	$obj = $DB->get_records_sql($select,array('diploma-'.$cod_elp.'%'));
	$result = array();
	foreach ($obj as $key => $value) {
		$result[] = array($value->id, $value->name, $value->idnumber);
	}
    return $result;
	
}

/**
 * 
        $data->cod_tpd_etb = $row['cod_tpd_etb'];
        $data->cod_etp = $row['cod_etp'];
        $data->cod_vrs_vet = $row['cod_vrs_vet'];
 * Enter description here ...
 * @param unknown_type $cod_tbd_etb
 */

function isdeclaredToDeploy($category,$cod_tpd_etb,$cod_etp,$cod_vrs_vet) {
	global $DB;
	$select = "	SELECT id 
				FROM {fbwizard} 
				WHERE category = ?
				AND cod_tpd_etb  = ?
				AND cod_etp  = ?
				AND cod_vrs_vet  = ?";
	$obj = $DB->get_records_sql($select,array($category,$cod_tpd_etb,$cod_etp,$cod_vrs_vet));
	if (empty($obj)) return false;
	return true;
	
}

function getIdModèleByCodTpdEtb($cod_etp, $cod_vrs_vet ,$cod_tpd_etb) {
	/*
	 * On utilise des id fixes :
	 * L1-D1 : 230
	 * L2-D2 : 231
	 * L3-D3 : 232
	 * LP-DP : 233
	 * M1 : 645
	 * M2 : 646
	 * 
	 */
	$niv=0;
	$obj_apogee = new apogee_connecteur();
    $niv = $obj_apogee->getCodSisDaaMin($cod_etp, $cod_vrs_vet ,$cod_tpd_etb) ;
    if ($cod_tpd_etb == 'L1' || $cod_tpd_etb == 'D1') {
    	return 230;
    } elseif ($cod_tpd_etb == 'L2' || $cod_tpd_etb == 'D2') {
    	if ($niv == 1)  return 230;
    	return 231;
    } elseif ($cod_tpd_etb == 'L3' || $cod_tpd_etb == 'D3') {
    	if ($niv == 1) return 230;
    	if ($niv == 2) return 231;
    	return 232;	
    } elseif ($cod_tpd_etb == 'LP' || $cod_tpd_etb == 'DP') {
    	return 233;
    } elseif ($cod_tpd_etb == 'M1' ) {
    	return 645;
    } elseif ($cod_tpd_etb == 'M2' ) {
    	return 646;
    }

}


function getNiveau($cod_etp, $cod_vrs_vet ,$cod_tpd_etb) {
	$niv=0;
	$obj_apogee = new apogee_connecteur();
    $niv = $obj_apogee->getCodSisDaaMin($cod_etp, $cod_vrs_vet ,$cod_tpd_etb) ;
    if ($cod_tpd_etb == 'L1' || $cod_tpd_etb == 'D1') {
    	return $cod_tpd_etb ;
    } elseif ($cod_tpd_etb == 'L2') {
    	if ($niv == 1) return 'L1';
    	return $cod_tpd_etb ;
    } elseif ($cod_tpd_etb == 'D2') {
    	if ($niv == 1) return 'D1';
    	return $cod_tpd_etb ;
    } elseif ($cod_tpd_etb == 'L3') {
    	if ($niv == 1) return 'L1';
    	if ($niv == 2) return 'L2';
    	return $cod_tpd_etb;	
    }elseif ($cod_tpd_etb == 'D3') {
    	if ($niv == 1) return 'D1';
    	if ($niv == 2) return 'D2';
    	return $cod_tpd_etb;	
    } 
    return $cod_tpd_etb;
}


/**
 * retourne les infos d'une catégorie
 */

function getInfoCategory($id) {
	global $DB;
	$select = "SELECT *
				FROM {course_categories} WHERE id = ?";
	$obj = $DB->get_record_sql($select,array($id));
	$infos = array();
	if (!empty($obj->path)) {
		$infos['path'] = $obj->path;
		$path = explode('/', $obj->path);
		// Recherche année
		if (!empty($path[0])) {
			$infos['anneeid'] = $path[0];
			$infos['annee'] = getNameOfCategory($path[0]);
		} else {
			$infos['anneeid'] = 0;
			$infos['annee'] = '';
		}
		// Recherche etab
		if (!empty($path[1])) {
			$infos['etabid'] = $path[1];
			$infos['etab'] = getNameOfCategory($path[1]);
		} else {
			$infos['etabid'] = 0;
			$infos['etab'] = '';
		}
		// Recherche comoosante
		if (!empty($path[2])) {
			$infos['composanteid'] = $path[2];
			$infos['composante'] = getNameOfCategory($path[2]);
		} else {
			$infos['composanteid'] = 0;
			$infos['composante'] = '';
		}
	} 
	return $infos;
}

/**
 * retourne les infos d'un cours
 */

function getInfoCourse($id) {
	global $DB;
	$select = "SELECT category, fullname, shortname 
				FROM {course} WHERE id = ?";
	$obj = $DB->get_record_sql($select,array($id));
	$infos = array();
	if (!empty($obj->category)) $infos['category'] = $obj->category;
	if (!empty($obj->fullname)) $infos['fullname'] = $obj->fullname;
	if (!empty($obj->shortname)) $infos['shortname'] = $obj->shortname;
	return $infos;
}

/**
 * Retourne les objets cohortes d'une liste de cohortes
 */

/**
 * Création de la variable formdata pour utiliser le plugin crswizard
 * 
 */
function create_the_formdata_variable($category,$lib_etp, $id_cohorte,$cod_tbd_etb,$id_modele) {
	global $DB, $CFG, $USER;;
	$infos_course = getInfoCourse($id_modele);
    $user = $DB->get_record('user', array('username' => $USER->username));
    $cohorte = $DB->get_record('cohort', array('id' => $id_cohorte));

    $infos_category = getInfoCategory($category);
    $course = $DB->get_record('course', array('id'=>$id_modele), '*', MUST_EXIST);
    if ($course) {
        $custominfo_data = custominfo_data::type('course');
        $custominfo_data->load_data($course);
        $custominfo_data = custominfo_data::type('course');
        $SESSION->wizard['form_step2']['up1datefermeture'] = $course->profile_field_up1datefermeture;
        $summary = array('text' => $course->summary, 'format' => $course->summaryformat);
    }
	$formdata = array(
	    'navigation' => array
        (
            'stepin' => 8,
            'suite' => 9,
            'retour' => 7,
        ),

	    'urlpfixe' => 'https://eee-test.univ-paris1.fr/fixe/',
	    'wizardurl' => '/local/crswizard/index.php',
	    'wizardcase' => 2,
        'form_step1' => array(
              	'stepin' => 1,
	            'modeletype' => 'selm1',
	            'selm1' => $id_modele,
	            'course_summary' => $id_modele,
	            'stepgo_2' => 'Étape suivante',
	            'coursedmodelid' => $id_modele,
	            'coursemodelfullname' => $infos_course['fullname'],
	            'coursemodelshortname' => $infos_course['fullname'],      
        
        ),
        'modele' => $id_modele,

        'form_step2' => array(
        	'category' => $category,
        	'fullname' => $lib_etp,
        	'shortname' => str_replace(' ','_',$lib_etp),
        	'summary_editor' => array(
        		'text' => $course->summary,
        		'format' => $course->summaryformat 
        	),
        	'startdate' => time(),
        	'up1datefermeture' => time(),
        	'mform_isexpanded_id_URL' => 1,
        	'myurl' => '',
        	'visible'  => $course->visible,
		    'format'  => $course->format,
		    'coursedisplay' => $course->visible,
		    'numsections' => 10,
		    'hiddensections' =>    0,
		    'newsitems' =>    $course->newsitems,
		    'showgrades' =>     $course->showgrades,
		    'showreports' =>     $course->showreports,
		    'maxbytes' =>     $course->maxbytes,
		    'groupmode' =>   $course->groupmode,
		    'groupmodeforce' =>     $course->groupmodeforce,
		    'defaultgroupingid' =>    $course->defaultgroupingid,
		    'lang' => $course->lang,
		    'id' =>   0,
        	'stepin' => 2,
        	'stepgo_3' =>  "Étape suivante"
        ),
	'form_step3' => array(
		    'shortname' => str_replace(' ','_',$lib_etp),
		    'rattachements'=> array(),
		    'up1niveauannee'=> array(),
		    'up1semestre'=> array(),
		    'up1niveau'=> array(),
		    'stepin'=> 3,
		    'stepgo_4'=> 'Étape suivante',
        	'composante : ' => $infos_category['composante'],
		    'type de diplôme : '=> "Licences",
		    'période'=>$infos_category['annee'],
		    'etablissement'=> $infos_category['etab'],
            'user_name'=> $user->firstname.' '.$user->lastname,
		    'user_login'=> $user->username,
		    'requestdate'=> time(),
		    'idetab'=>  $infos_category['etabid'],
		    'item'=> array(),
		    'all-rof'=>  array()
        ),
        'form_step4' => array(
        	'role' => 'editingteacher',
        	'something' => '',
        	'user' => array(
        		'editingteacher' => array($USER->email),
        		'responsable_epi' => array($USER->email)
        	),
        	'stepin' => 4,
        	'step' => '',
        	'all-users' => array(
        		'editingteacher' => array(
        			$user->username => $user
        		),
        	),
        ),
       'form_step5' => array(
	      	'role'=>'student',
	    	'something'=> '',
	    	'group'=> array(
	      		'student'=> array( 0 => $cohorte->idnumber)
	     	),  
	     	'stepin'=> '5',
		    'step' =>  '',
		    'all-cohorts'=> array('student'=> array( $cohorte->idnumber=> $cohorte))    	
        ), 
        'form_step6' => array(
		    'mform_isexpanded_id_generalu'=> 1,
		    'passwordu'=> '',
		    'mform_isexpanded_id_generalv'=> 1,
		    'passwordv'=> '',
		    'mform_isexpanded_id_generala'=> 1,
		    'stepin'=> 6,
		    'stepgo_7'=> "Étape suivante" 
        ),
        'form_step7'=>  array(
		    'user_name'=> $user->firstname.' '.$user->lastname,
		    'category'=> $category,
		    'fullname'=> $lib_etp,
		    'shortname'=> str_replace(' ','_',$lib_etp),
		    'profile_field_up1generateur'=>  "Manuel via assistant (cas n°3 hors ROF)",
		    'remarques'=> "",
		    'stepin'=> 7,
		    'stepgo_8'=> "Terminer",
		    'coursemodel'=> "[_questionnaire]Cours modèle",
		    $cohorte->idnumber=> $cohorte->name
	    ),
        'rof2_tabpath'=> '',
	);
	return $formdata;
}

/**
 * création de la liste des cours à créer
 */

function inserer_liste_cours_a_creer($liste) {
	global $DB,$USER;
	foreach ($liste as $i=>$row) {
        $data = new stdClass();
        $data->category = $row['category'];
        $data->userid = $USER->id;
        $data->cod_tpd_etb = $row['cod_tpd_etb'];
        $data->cod_etp = $row['cod_etp'];
        $data->cod_vrs_vet = $row['cod_vrs_vet'];
        $data->lib_etp = $row['lib_etp'];
        $data->cohorte = $row['cohorte'];
        $data->model_courseid = getIdModèleByCodTpdEtb($row['cod_etp'],$row['cod_vrs_vet'],$row['cod_tpd_etb']);
        $DB->insert_record('fbwizard', $data);
        unset($data);
	}
}



/**
 * création de cours par modèle
 */
function create_courses() {
	global $DB, $USER,$CFG;
    $email_user = array();
    $liste_course_2_deploye = $DB->get_records('fbwizard', array('deployed' => 0));
    $liste_deployed = '';
    foreach ($liste_course_2_deploye as $i=>$obj) {
    	    	$model_courseid = getIdModèleByCodTpdEtb($obj->cod_etp,$obj->cod_vrs_vet,$obj->cod_tpd_etb);
		$formdata= create_the_formdata_variable
											($obj->category,
											$obj->lib_etp, 
											$obj->cohorte,
											$obj->cod_tpd_etb,
											$model_courseid);
		$corewizard = new wizard_core($formdata, $USER);
	    $errorMsg = $corewizard->create_course_to_validate();
	    // récupère l'id du cours créé et mettre à jour fbwizard
	    $SELECT = "SELECT MAX(id) as maxid from {course}";
	    $objmax = $DB->get_record_sql($SELECT,array());
		(empty($objmax->maxid))?$idcours=0:$idcours=$objmax->maxid;
		$email_user[$obj->userid][] = array ('id'=>$idcours,'lib' =>$obj->lib_etp, 'liste_deployed'=>'
  -'.$obj->lib_etp.' : '.$CFG->wwwroot.'/course/view.php?id='.$idcours);
		$obj->courseid=$idcours;
		$obj->deployed = 1;
		$DB->update_record('fbwizard', $obj, true);
		// Super on a créé le cours sur modèle
		/*	@TODO : - Récupérer l'id de feedback
		 * 			- Dupliquer le feedback
		 * 			- Faire la question subsidiaire
		 */

		// Récupération de l'id du feedback
		$feedback = $DB->get_record('feedback', array('course'=>$idcours), '*', MUST_EXIST);
		// Récupérer l'ordre maximum des items
		$select_ordre = "SELECT MAX(id) as maxid FROM {feedback_item} WHERE feedback=?";
		$objordre = $DB->get_record_sql($select_ordre,array($feedback->id));
		(empty($objordre->maxid))?$ordremax=0:$ordremax=$objordre->maxid;
		
		///////////////////// QUESTION SUBSIDIAIRE //////////////////////
    		$obj_apogee = new apogee_connecteur();
		$t = $obj_apogee->getListSemestres($obj->cod_etp,$obj->cod_vrs_vet);
		$list_cod_elp = array();
		for($i=0;$i<count($t);$i++) {
			$ordremax++;
			insert_item($feedback->id,$t[$i]['name'],$ordremax,$t[$i]['COD_ELP'],true);
			$o = array();
			$obj_apogee->GetilsElp($t[$i]['COD_ELP'],null,$o);
			for ($j=0;$j<count($o);$j++) {
				if (!in_array($o[$j]['code'], $list_cod_elp)) {
					$list_cod_elp[] = $o[$j]['code'];
                                	$ordremax++;
                                	insert_item($feedback->id,$o[$j]['name'],$ordremax,$o[$j]['code']);
				}
			}
		}
    }

                foreach($email_user as $userid=>$row) {
                         $subject = '[EEE] Déploiement de feedbacks';
                         $message = '
                         Bonjour,
 Les cours suivants ont bien été déployés:
 ';
                         for($i=0;$i<count($row);$i++) {
                                  $liste_deployed .= $row[$i]['liste_deployed'];
                         }
                         $message .= $liste_deployed;
                         $message .='
 
 Cordialement,
 
 --
 Ceci est un message automatique. Merci de na pas y répondre.';
 
                         $supportuser = core_user::get_support_user();
                         $user= core_user::get_user($userid);
                         email_to_user($user, $supportuser, $subject, $message);
                 }

}

/**
 * function create_label() create a decorator for label for the feedback
 * @param $lib : libelle to decore
 * @return string
 */

function create_label($lib) {
	$decorator = '<p style="font-weight : bold;
				font-size:11pt;
				color:#00326E;
				text-decoration:underline;"> 
				<br>
				'.$lib.' :
				</p>';
	return $decorator;
}

/**
 * 
 * Insère un item des par UE/MATI
 * @param int $feedback
 * @param string $lib
 * @param int $order
 * @param string $cod_elp
 * @param bool $label : Vrai si il s'agit d'un semestre
 */
function insert_item($feedback,$lib,$order,$cod_elp,$label = false) {
	global $DB;
	$data = new stdClass();
	$data->feedback = $feedback;
	$data->template = 0;
	if ($label) {
		$data->name = '';
		$data->label ='';
		$data->presentation = create_label($lib) ;
		$data->typ = 'label';
		$data->hasvalue = 0;
		$data->options = '';
	} else {
		$data->name = $lib;
		$data->label = $cod_elp;
		$data->presentation = 'r>>>>>Oui
|Plutôt oui
|Plutôt non
|Non
|Sans avis
|Non concerné-e<<<<<1';
		$data->typ = 'multichoice';
		$data->hasvalue = 1;
		$data->options = 'h';
	}
	$data->position = $order;
	$data->required = 0;
	$data->dependitem = '';
	$DB->insert_record('feedback_item', $data);
}


/**
* count nb courses to deploy
*/
function count_courses() {
    global $DB;
    $SELECT = "SELECT COUNT(id) as nb from {fbwizard}";
	$obj = $DB->get_record_sql($SELECT,array());
	(empty($obj->nb))?$nb=0:$nb=$obj->nb;
	return $nb;
}

/*
* get All the ids courses for a category
*/
function getCourseIdsForCategory($id_cat) {
	global $DB;
	$select = "select C.id,fullname,shortname, cod_etp
	from {course} C 
	INNER JOIN {course_categories} CC on C.category = CC.id
	Inner JOIN {fbwizard} fb on C.id=fb.courseid
	WHERE CC.path LIKE ? ";
	$obj_courseids =  $DB->get_records_sql($select,array('/'.$id_cat.'%'));
	return $obj_courseids ;
}

/*
* get All the answer for a course
*/
function getNbReponseByCourse($id_course) {
        global $DB;
        $select = "select mdlfc.id, mdlfc.timemodified,fullname , cod_etp
        from mdl_feedback_completed mdlfc 
        inner join mdl_feedback mdlfbk on mdlfbk.id=mdlfc.feedback 
        inner join mdl_course C on mdlfbk.course = C.id 
        Inner JOIN {fbwizard} fb on C.id=fb.courseid
        WHERE mdlfbk.course=? 
        order by mdlfc.timemodified";
        $obj_courseById =  $DB->get_records_sql($select,array($id_course));
        return $obj_courseById ;
}
