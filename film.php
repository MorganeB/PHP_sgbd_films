<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd"> 
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"> 
    <title>
      Connexion à la base Films
    </title>
  </head>
  
  <body>
    <h1>
      Connexion à la base Films
    </h1>
    
    <?php
     
	// test si le formulaire a été soumis 
	if(isset($_GET['rech'])){
		// Connexion à la base réalisée grâce à PDO
		$p = new PDO('mysql:host=localhost;dbname=Films', "root", "");
		
		/* consruction d'un tableau pour construire la requête en fonction 
		des champs remplis par l'utilisateur 
		*/
		if($_GET['choix'] == 1){
			$tables = array("film");
			$conditions = array();
			if(!empty($_GET['titre'])){
				$conditions[] = "AND lower(titre) LIKE '%" . strtolower($_GET['titre']) . "%'";
			}
			if(!empty($_GET['annee'])){
				$conditions[] = "AND annee = " . $_GET['annee'];
			}
			if(!empty($_GET['perso'])){
				$tables[] = "role";
				$tables[] = "artiste";
				$conditions[] = "AND film.id = role.idFilm";
				$conditions[] = "AND artiste.id = role.idActeur";
				$conditions[] = "AND lower(perso) LIKE '%" . strtolower($_GET['perso']) . " %'";	
			}
			
			//$requete = "SELECT * FROM " . implode(", ", $tables) . " WHERE titre IS NOT NULL " . implode(" ", $conditions) . " ;"		
			$requete = sprintf('SELECT * FROM %s WHERE titre IS NOT NULL %s;', implode(", ", $tables), implode(", ", $conditions));
		}else{
			$requete = sprintf("SELECT * FROM film WHERE lower(film.titre) LIKE '%%%s%%';", strtolower($_GET['motcle']));
		}
		//execution
		$req = $p->query($requete);
		//on compte le nb de résultats
		$nbrow = $req->rowCount();
	}
	
		//test l'absence de résultat
		if($nbrow > 0){
			$nbParPage = 5; 
			$page = (isset($_GET['page'])) ? $_GET['page'] : 1;
			$offset = ($page -1) * $nbParPage;
			?>
			<table border="1">
			<?php
			$req->setFetchmode(PDO::FETCH_OBJ);
			foreach ($req as $k => $f){
				if($k >= $offset && $k < $offset + $nbParPage){
					//echo $k . "-";
					$tr = sprintf('<tr style="background-color:#%s"><td>%s</td><td>%s</td><td>%s</td></tr>', 
						(($i++ % 2) ? "D0FFFF" : "F6E497") , $f->titre, $f->annee, $f->genre); 
					echo $tr;
				}
			}
			
		?>
		</table>
		<?php
			//Pagination
			$nbPages = ($nbrow % $nbParPage == 0) ? $nbrow / $nbParPage : ($nbrow / $nbParPage)+1;
			$params = "";
			//on enleve le parametre qui correspond à "page" sinon il concatene 
			unset($_GET['page']);
			foreach($_GET as $k=>$v){
				$param = $param . "&" . $k . "=" . $v;
			}
			for($i = 1; $i <= $nbPages; $i++){
				$lien = sprintf('<a href="%s?page=%s%s">Page %s  </a>', $_SERVER['PHP_SELF'], $i, $param, $i);
				echo $lien;
			}
		}else{
			echo "Pas de résultats";
		}
		
        // Script terminé, on libère la ressource PDO		
        $p = null; 
		?>
		
  </body>
</html>