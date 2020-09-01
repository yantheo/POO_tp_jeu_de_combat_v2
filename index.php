<?php
function chargerClasse($classe)
{
	require $classe . '.php';
}
spl_autoload_register('chargerClasse');

session_start();

if(isset($_GET['deconnexion']))
{
	session_destroy();
	header('Location: .');
	exit();
}

$db= new PDO('mysql:host=localhost;dbname=fightgame', 'root', '');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
$manager = new PersonnageManager($db);

if(isset($_SESSION['perso']))// Si la session perso existe, on restaure l'objet
{
	$perso = $_SESSION['perso'];
}

if(isset($_POST['creer']) && isset($_POST['nom']))//Si on veut créer un personnage
{
	switch($_POST['type'])
	{
		case 'magicien':
			$perso = new Magicien(['nom' => $_POST['nom']]);
			break;
		
		case 'guerrier':
			$perso = new Guerrier(['nom' => $_POST['nom']]);
			break;
			
		default:
			$message = 'Le type du personnage est invalide.';
			break;
	}
	
	if(isset($perso))
	{
		if(!$perso->nomValide())
		{
			$message = 'Le nom choisi est invalide.';
			unset($perso);
		}
		elseif($manager->exists($perso->nom()))
		{
			$message = 'Le nom du personnage est déja pris.';
			unset($perso);
		}
		else
		{
			$manager->add($perso);
		}
	}
}

elseif(isset($_POST['utiliser']) && isset($_POST['nom']))// Si on veut utiliser un personnage
{
	if($manager->exists($_POST['nom']))
	{
		$perso = $manager->get($_POST['nom']);
	}
	else
	{
		$message = 'Ce personnage n\'existe pas!';
	}
}

elseif(isset($_GET['frapper']))
{
	if(!isset($perso))
	{
		$message = 'Merci de créer un personnage ou de vous identifier.';
	}
	else
	{
		if(!$manager->exists((int) $_GET['frapper']))
		{
			$message = 'Le personnage que vous voulez frapper n\'existe pas.';
		}
		else
		{
			$persoAFrapper = $manager->get((int) $_GET['frapper']);
			$retour = $perso->frapper($persoAFrapper);//On stocke dans $retour les éventuelles erreurs que renvoie la methode frapper
			
			switch($retour)
			{
				case Personnage::CEST_MOI :
					$message = 'Mais... pourquoi voulez vous vous frapper?';
					break;
					
				case Personnage::PERSONNAGE_FRAPPE :
					$message = 'Le personnage a bien été frappé!';
					
					$manager->update($perso);
					$manager->update($persoAFrapper);
					
					break;
					
				case Personnage::PERSONNAGE_TUE :
					$message = 'Vous avez tué ce personnage!';
					$manager->update($perso);
					$manager->delete($persoAFrapper);
					
					break;
					
				case Personnage::PERSO_ENDORMI :
					$message = 'Vous êtes endormi, vous ne pouvez pas frapper de personnage!';
					break;
			}
		}
	}
}
elseif(isset($_GET['ensorceler']))
{
	if(!isset($perso))
	{
		$message = 'Merci de créer un personnage ou de vous identifier!';
	}
	else
	{	//il faut verifier si le personnage est bien un magicien
		if($perso->type() != "magicien")
		{
			$message = 'Seuls les magiciens peuvent ensorceler des personnages!';
		}
		else
		{
			if(!$manager->exists((int) $_GET['ensorceler']))
			{
				$message = 'Le personnage que vous voulez frapper n\'existe pas!';
			}
			else
			{
				$persoAEnsorceler = $manager->get((int) $_GET['ensorceler']);
				$retour = $perso->lancerUnSort($persoAEnsorceler);
				
				switch($retour)
				{
					case Personnage::CEST_MOI :
						$message = 'Mais... pourquoi voulez-vous vous ensorceler?';
						break;
						
					case Personnage::PERSONNAGE_ENSORCELE : 
						$message = 'Le personnage a bien été ensorcelé!';
						$manager->update($perso);
						$manager->update($persoAEnsorceler);
						break;
						
					case Personnage::PAS_DE_MAGIE : 
						$message = 'Vous n\'avez pas de magie';
						break;
						
					case Personnage::PERSO_ENDORMI :
						$message = 'Vous êtes endormi, vous ne pouvez pas lancer de sort!';
						break;
						
				}
			}
		}
	}
}
?>
<!doctype html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<ti<l></l>le TP : Mini jeu de combat TP</title>
	</head>
	<body>
		<p>Nombre de personnages créés : <?= $manager-> count()?></p>
<?php
if(isset($message))//on a un message à afficher
{
	echo '<p>', $message, '</p>';
}
if(isset($perso))//si on utilise un personnage (nouveau ou pas)
{
?>
		<p><a href="?deconnexion=1">Déconnexion</a></p>
		<fieldset>
			<legend>Mes informations</legend>
			<p>
			Type : <?= ucfirst($perso->type()) ?><br>
			Nom : <?= htmlspecialchars($perso->nom())?><br>
			Dégâts : <?= $perso->degats()?><br>
<?php
//on affiche l'atout du personnage suivant son type
switch($perso->type())
{
	case 'magicien':
		echo 'Magie : ';
		break;
		
	case 'guerrier':
		echo 'Protection : ';
		break;
}
echo $perso->atout();
?>
			</p>
		</fieldset>
		
		<fieldset>
			<legend>Qui attaquer ?</legend>
			<p>
<?php
//On récupère tous les personnages par ordre alphabétique, dont le nom est different de celui de notre personnage
//On ne va pas se frapper soi meme!
$retourPersos = $manager->getList($perso->nom());

if(empty($retourPersos))
{
	echo 'Personne à frapper';
}
else
{
	if($perso->estEndormi())
	{
		echo 'Un magicien vous a endormi ! Vous allez vous réveiller dans ', $perso->reveil(), '.';
	}
	else
	{
		foreach($retourPersos as $unPerso)
		{
			echo '<a href="?frapper=', $unPerso->id(), '">',htmlspecialchars($unPerso->nom()), '</a>
			(dégâts : ', $unPerso->degats(), ' | type : ', $unPerso->type(), ')';
			
			//on ajoute un lien pour lancer un sort si le personnage est un magicien.
			if($perso->type() == 'magicien')
			{
				echo ' | <a href="?ensorceler=',$unPerso->id(),'">lancer un sort</a>';
			}
			
			echo '<br>';
		}
	}
}
?>		
			</p>
		</fieldset>
<?php
}
else
{
?>
		<form action="" method="post">
		<p>
			Nom : <input type="text" name="nom" maxlenght="50"/><input type="submit" value="Utiliser ce personnage" name="utiliser"><br>
			Type :
			<select name="type">
				<option value="magicien">Magicien</option>
				<option value="guerrier">Guerrier</option> 
			</select>
			<input type="submit" value="Créer ce personnage" name="creer">
		</p>
		</from>
<?php
}
?>
	</body>
</html>
<?php
if(isset($perso))//si on créé un personnage, on le stocke dans une variable session afin d'économiser une requete SQL
{
	$_SESSION['perso'] = $perso;
}

























