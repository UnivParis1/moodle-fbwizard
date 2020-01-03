# moodle-fbwizard
Déploiement automatique de feedback  interfacés avec APOGEE

## Pré-requis 
Ce plugin ne fonctionne que si vous avez installé les plugins local_crswizard et local_roftools

## Installation 
- Paramétrez dans le fichier de configuration Moodle ces variables :

		  $CFG->user_oracle 		// utilisateur se connectant en lecture sur la base APOGEE
		  $CFG->passwd_oracle 	// mot de passe de l'utilisateur
		  $CFG->base_oracle		// infos de la base de donnée APOGEE (domain:port/nom_base_de_donnée) 

- Placer vous dans le repertoire [racine_moodle]/local
- Executer la commande https://github.com/UnivParis1/moodle-fbwizard : 

         git clone https://github.com/UnivParis1/moodle-fbwizard fbwizard
 - Puis rendez vous dans l'espace d'administration de votre plateforme Moodle pour compléter l'installation
- Enfin ajoutez une tache de fond pour la création de feedbacks en tache fond

       */10 * * * * cd <emplacement du plugin> && php create_feedback_cli -i 
        
