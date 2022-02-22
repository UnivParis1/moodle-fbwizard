<?php 
/**
 * @author El-Miqui CHEMLAL
 * @version 1.0
 * 
 */
class apogee_connecteur{
	private $array_liste_tables = array(	'rof_component',
										'rof_constant',
										'rof_course',
										'rof_person',
										'rof_program');
	private $user_oracle;
	private $passwd_oracle;
	private $base_oracle; // de type HOST:PORT/NOM_BDD
	
	public function __construct() {
		$this->getConfigOracle();
	}
	
/**
 * 
 * Récupération des paramêtres de connexion à APOGEE
 */
	public function getConfigOracle() {
		global $CFG;
		$this->user_oracle = $CFG->user_oracle;
		$this->passwd_oracle = $CFG->passwd_oracle;
		$this->base_oracle = $CFG->base_oracle;
	}
	
	
	/**
	 * 
	 * Ouvre a connexion Oracle pour accéder à la BD APOGEE
	 */
	private function OpenOracleConn() {
		if (empty($this->user_oracle)||empty($this->passwd_oracle)||empty($this->base_oracle))
			$this->getConfigOracle();
		$cnxoracle = oci_connect($this->user_oracle, $this->passwd_oracle,$this->base_oracle,"AL32UTF8"); //"AMERICAN_AMERICA.WE8ISO8859P9");
		$cnxoracle = ocilogon($this->user_oracle, $this->passwd_oracle,$this->base_oracle,"AL32UTF8");
	    if ($cnxoracle == false) die("Connexion $base_oracle impossible ".OCIError($cnxoracle)."\n");
	    else return $cnxoracle; 
	}
	
	/**
	 * 
	 * Retourne l'année universitaire actuelle
	 */
  	private function getAnneeUniversitaire() {
  		$conn = $this->OpenOracleConn() ;
  		$SELECT_ANNEE = "select * from ANNEE_UNI where ETA_ANU_IAE = 'O'";
		$cursor = OCIParse($conn, $SELECT_ANNEE);
		$result = OCIExecute($cursor); 
		$values = oci_fetch_assoc($cursor) ;
  		oci_close($conn);
  		return $values['COD_ANU'];
  		
  	}
	
	
	/**
	 * Récupère la liste des Licences d'une compoosante
	 * @param string $cmp : COD_CMP de l'UFR
	 * @param string $category : id de la catégorie Moodle
	 */
	public function getLicencesByCMP($cmp, $category) {
		$conn = $this->OpenOracleConn() ;
		$annee =$this->getAnneeUniversitaire();
		$SELECT_ETAPES = "	SELECT DISTINCT DIP.COD_TPD_ETB as CTE,  VDE.cod_etp as CE , VDE.cod_vrs_vet as CVV, ETP.lib_etp as NAME 
							FROM vdi_fractionner_vet VDE
							INNER JOIN version_etape VET ON VET.cod_etp = VDE.cod_etp and VET.cod_vrs_vet = VDE.cod_vrs_vet
							INNER JOIN version_diplome VDI ON VDI.cod_dip = VDE.cod_dip and VDI.cod_vrs_vdi = VDE.cod_vrs_vdi
							INNER JOIN etape ETP on VDE.COD_ETP = ETP.COD_ETP
							INNER JOIN diplome DIP on VDI.cod_dip = DIP.cod_dip
							WHERE '$annee' BETWEEN VDE.daa_deb_rct_vet and VDE.daa_fin_val_vet
							and DIP.COD_TPD_ETB IN ('20','DP','L1','L2','D2','L3','LI','D3','M1','E1','M2','E2','U3','U4','U5')
							and VET.COD_CMP='$cmp'
							ORDER BY ETP.lib_etp";
		$cursor = OCIParse($conn, $SELECT_ETAPES);
		$result = OCIExecute($cursor); 
		$data = array();
		$i=0;
		while ($values = oci_fetch_assoc($cursor)) {
			$data[$i]['category'] = $category;
			$data[$i]['cod_tpd_etb'] = $values['CTE'] ;
			$data[$i]['cod_etp'] = $values['CE'] ;
			$data[$i]['cod_vrs_vet'] = $values['CVV'];
			$data[$i]['lib_etp'] = $values['NAME'];
			$i++;
		}
		oci_close($conn);
		return $data;
	}
	
	/**
	 * 
	 */
	public function getCodSisDaaMin($cod_etp, $cod_vrs_vet ,$cod_tpd_etb) {
		$conn = $this->OpenOracleConn() ;
		$SELECT_ETAPES = "	SELECT DISTINCT VDE.cod_sis_daa_min AS NIV
							FROM vdi_fractionner_vet VDE
							INNER JOIN version_etape VET ON VET.cod_etp = VDE.cod_etp and VET.cod_vrs_vet = VDE.cod_vrs_vet
							INNER JOIN version_diplome VDI ON VDI.cod_dip = VDE.cod_dip and VDI.cod_vrs_vdi = VDE.cod_vrs_vdi
							INNER JOIN diplome DIP on VDI.cod_dip = DIP.cod_dip
				              and DIP.COD_TPD_ETB='$cod_tpd_etb'
				              and VET.COD_ETP='$cod_etp'
				              AND VET.COD_VRS_VET = '$cod_vrs_vet'";
		$cursor = OCIParse($conn, $SELECT_ETAPES);
		$result = OCIExecute($cursor); 
		$data = array();
		$niv=0;
		$values = oci_fetch_assoc($cursor);
		if (!empty($values['NIV'])) $niv = $values['NIV'] ;
		oci_close($conn);
		return $niv;
	}
	
	
	
	public function getListSemestres2($cod_etp,$cod_vrs_vet) {
		$data = array();
		$conn = $this->OpenOracleConn() ;
		$annee =$this->getAnneeUniversitaire();
		$SELECT_LSE ="	SELECT VRL.cod_lse FROM vet_regroupe_lse VRL
        				WHERE VRL.cod_etp = '$cod_etp' AND VRL.cod_vrs_vet = '$cod_vrs_vet'
						AND (VRL.dat_frm_rel_lse_vet > sysdate or VRL.dat_frm_rel_lse_vet is null)";


		$cursor_LSE = OCIParse($conn, $SELECT_LSE);
		OCIExecute($cursor_LSE);
		while ($values_LSE = oci_fetch_array($cursor_LSE)) {
			$COD_LSE = $values_LSE['COD_LSE'];
			//echo "SUB $COD_ETP-$COD_VRS_VDI $COD_LSE\n";
			$todo = array("cod_lse = '$COD_LSE'");
			$added = array();
            $SELECT_ELP_BASE =
                        "SELECT ERE.cod_elp_fils, ELP.cod_nel, ELP.cod_elp, ELP.lib_elp as NAME
                         FROM elp_regroupe_elp ERE
                         INNER JOIN element_pedagogi ELP ON ERE.cod_elp_fils = ELP.cod_elp
                         WHERE ELP.tem_sus_elp = 'N' AND ELP.eta_elp = 'O' 
                         AND ERE.date_fermeture_lien IS NULL
                         AND ERE.";
            while ($todo) {
            	$one = array_shift($todo);
	//echo "$cod_etp: searching child $one<br />";
            	$cursor2 = OCIParse($conn, "$SELECT_ELP_BASE$one");
            	$result = OCIExecute($cursor2);
            	while ($values2 = oci_fetch_array($cursor2)) {
            		if ($values2['COD_NEL'] !== 'SEM') {
            			$cstrt = "cod_elp_pere = '" . $values2['COD_ELP'] . "'";
            			if (!isset($added[$cstrt])) {
            			//echo "$cod_etp: got " . $values2['COD_ELP'] . " " . $values2['COD_NEL'] . ", will search for children for semester<br />";
              			$added[$cstrt] = true;
            			array_push($todo, $cstrt);
            			}
            		continue;
			}
					// Ainsi pour retrouver les code ELP
					// On "explodera" la chaîne (nécéssaire pour Synchroniser les cours)
            		//echo "$COD_ETP: found semester " . $values2['COD_ELP'] . "\n";
            		//continue;
            		$data[] = array(
									'name' 			=> $values2['NAME'],
									'COD_ELP' 		=> $values2['COD_ELP'],
						);
					}
            	}
		return $data;
		}
	}

        public function getListSemestres($cod_etp,$cod_vrs_vet) {
                $data = array();
                $conn = $this->OpenOracleConn() ;
                $annee =$this->getAnneeUniversitaire();
                $SELECT_LSE ="  SELECT VRL.cod_lse FROM vet_regroupe_lse VRL
                                        WHERE VRL.cod_etp = '$cod_etp' AND VRL.cod_vrs_vet = '$cod_vrs_vet'
                                                AND (VRL.dat_frm_rel_lse_vet > sysdate or VRL.dat_frm_rel_lse_vet is null)";

                $cursor_LSE = OCIParse($conn, $SELECT_LSE);
                OCIExecute($cursor_LSE);
                while ($values_LSE = oci_fetch_array($cursor_LSE)) {
                        $COD_LSE = $values_LSE['COD_LSE'];
                        //echo "SUB $COD_ETP-$COD_VRS_VDI $COD_LSE\n";
                        $todo = array("cod_lse = '$COD_LSE'");
                        $added = array();
            $SELECT_ELP_BASE =
                        "SELECT ERE.cod_elp_fils, ELP.cod_nel, ELP.cod_elp, ELP.lib_elp as NAME
                         FROM elp_regroupe_elp ERE
                         INNER JOIN element_pedagogi ELP ON ERE.cod_elp_fils = ELP.cod_elp
                         WHERE ELP.tem_sus_elp = 'N' AND ELP.eta_elp = 'O'
                         AND ERE.date_fermeture_lien IS NULL
                         AND ERE.";
            while ($todo) {
                $one = array_shift($todo);
       //echo "$cod_etp: searching child $one<br />";
       //echo "$SELECT_ELP_BASE$one";
                $cursor2 = OCIParse($conn, "$SELECT_ELP_BASE$one");
                $result = OCIExecute($cursor2);
                while ($values2 = oci_fetch_array($cursor2)) {
                        if ($values2['COD_NEL'] !== 'SEM') {
                                $cstrt = "cod_elp_pere = '" . $values2['COD_ELP'] . "'";
                                if (!isset($added[$cstrt])) {
                                //echo "$cod_etp: got " . $values2['COD_ELP'] . " " . $values2['COD_NEL'] . ", will search for children for semester<br />";
                                $added[$cstrt] = true;
                                array_push($todo, $cstrt);
                                }
                        continue;
                        }
                                        // Ainsi pour retrouver les code ELP
                                        // On "explodera" la chaîne (nécéssaire pour Synchroniser les cours)
                        //echo "$COD_ETP: found semester " . $values2['COD_ELP'] . "\n";
                        //continue;
                        $data[] = array(
                                                                        'name'                  => $values2['NAME'],
                                                                        'COD_ELP'               => $values2['COD_ELP'],
                                                );
                                        }
                }
                }
		return $data;
        }	
	/**
	 * 
	 * Recherche tous les fils d'un élément pédagogique
	 * @param string $cod_elp
	 * @param integer $level
	 */ 
	
	public function GetilsElp($cod_elp,$pere=null,&$data=array()) {
		$conn = $this->OpenOracleConn() ;
		$SELECT_COURSES = "	SELECT  distinct ERE.COD_ELP_FILS AS CODE, ELP.LIB_ELP AS NAME,TYH.LIB_TYP_HEU AS COMPOSITION, ELP.COD_NEL
					 FROM APOGEE.elp_regroupe_elp ERE 
					 INNER JOIN ELEMENT_PEDAGOGI ELP on (ELP.COD_ELP= ERE.COD_ELP_FILS)
					 LEFT JOIN ELP_CHG_TYP_HEU ECT on (ELP.COD_ELP = ECT.COD_ELP)
					 LEFT JOIN TYPE_HEURE TYH on (ECT.COD_TYP_HEU = TYH.COD_TYP_HEU)
					 where ERE.DATE_FERMETURE_LIEN is null
					 and ELP.TEM_SUS_ELP  = 'N'
					 and ERE.COD_ELP_PERE='$cod_elp'
					 ";	
		$cursor = OCIParse($conn, $SELECT_COURSES);
		$result = OCIExecute($cursor);
		$cod_nel_a_prendre = array(
							//	'CHOI',
								'MATI',
							//	'MEM',
							//	'PAR',
							//	'PRJ',
								'SEM',
							//	'STAG',
							//	'UE97'
							);
		

		while ($values = oci_fetch_array($cursor) ) {
			//if ( $values['COD_NEL']!='MACR' && $values['COD_NEL']!='SP1'&& $values['COD_NEL']!='SP1' ) {
			if ( in_array( $values['COD_NEL'], $cod_nel_a_prendre) ) {
				$data[] = array(
					'code' 			=>	$values['CODE'],
					'name'			=> 	$values['NAME'],
					'composition'	=> 	$values['COMPOSITION'],
					'COD_NEL'		=> 	$values['COD_NEL'], 
					'pere'			=>	$cod_elp
				);
				
			} else {
				if (!$pere) $pere = $cod_elp;
				$this->GetilsElp($values['CODE'],$pere,$data);
			}
			
		}
	
	 	oci_close($conn);
	
	}
	
}
