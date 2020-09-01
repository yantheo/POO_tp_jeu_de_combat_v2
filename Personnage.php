<?php
//La classe personnage ne pourra �tre instancifier en objet/seulement h�rit�
abstract class Personnage
{
	//ATTRIBUTS
	protected $atout, $degats, $id, $nom, $timeEndormi, $type;
	
	const CEST_MOI = 1; //Constante renvoy�e par la m�thode 'frapper si on se frappe soi-m�me'
	const PERSONNAGE_TUE = 2; //Constante renvoy�e par la m�thode 'frapper' si on a tue le personnage en frappant
	const PERSONNAGE_FRAPPE = 3; //Constante renvoy�ee par la m�thode 'frapper' si on a bien frapper le personnage
	const PERSONNAGE_ENSORCELE = 4; //Constante renvoy�e par la m�thode 'lancerUnSort' (Voir classe Magicien)
	const PAS_DE_MAGIE = 5; //Constante renvoy�e par la m�thode 'lancerUnsort' si on veut lancer un sort si la magie est � O
	const PERSO_ENDORMI = 6; //Constante renvoy�e par la m�thode 'frapper' si le personnage qui veut frapper est endormi
	
	//METHODES
	public function __construct(array $donnees)
	{
		$this->hydrate($donnees);
		//toutes les donn�es hydrat�es $type seront �crit en minuscule
		$this->type = strtolower(static::class);
	}
	
	public function estEndormi()
	{
		return $this->timeEndormi > time();
	}
	
	public function frapper(Personnage $perso)
	{
		if($perso->id == $this->id)
		{
			return self::CEST_MOI;
		}
		
		if($this->estEndormi())
		{
			return self::PERSO_ENDORMI;
		}
		
		//on indique au personnage qu'il doit recevoir des degats
		//puis on retourne la valeur renvoy�ee par la m�thode : self::PERSONNAGE_TUE ou self::PERSONNAGE_FRAPPE
		return $perso->recevoirDegats();
	}
	
	public function hydrate(array $donnees)
	{
		foreach($donnees as $key => $value)
		{
			$method = 'set'.ucfirst($key);
			if(method_exists($this, $method))
			{
				$this->$method($value);
			}
		}
	}
	
	public function nomValide()
	{
		return !empty($this->nom);
	}
	
	public function recevoirDegats()
	{
		$this->degats += 5;
		if($this->degats >= 100)
		{
			return self::PERSONNAGE_TUE;
		}
		return self::PERSONNAGE_FRAPPE;
	}
	
	public function reveil()
	{
		$secondes = $this->timeEndormi;
		$secondes -= time();
		
		$heures = floor($secondes / 3600);
		$secondes -= $heures * 3600;
		$minutes = floor($secondes / 60);
		$secondes -= $minutes * 60;
		
		$heures .= $heures <= 1 ? ' heure' : ' heures';
		$minutes .= $minutes <= 1 ? ' minute' : ' minutes';
		$secondes .= $secondes <= 1 ? ' seconde' : ' secondes';
		
		return $heures . ', ' . $minutes . ' et ' . $secondes;
	}
	
	//GETTER
	public function atout()
	{
		return $this->atout;
	}
	
	public function degats()
	{
		return $this->degats;
	}
	
	public function id()
	{
		return $this->id;
	}
	
	public function nom()
	{
		return $this->nom;
	}
	
	public function timeEndormi()
	{
		return $this->timeEndormi;
	}
	
	public function type()
	{
		return $this->type;
	}
	
	
	//SETTER
	public function setAtout($atout)
	{
		$atout = (int) $atout;
		if($atout >= 0 && $atout <=100)
		{
			$this->atout = $atout;
		}
	}
	
	public function setDegats($degats)
	{
		$degats = (int) $degats;
		if($degats >= 0 && $degats <=100)
		{
			$this->degats = $degats;
		}
	}
	
	public function setId($id)
	{
		$id = (int) $id;
		if($id > 0)
		{
			$this->id = $id;
		}
	}
	
	
	public function setNom($nom)
	{
		if(is_string($nom))
		{
			$this->nom = $nom;
		}
	}
	
	public function setTimeEndormi($time)
	{
		$this->timeEndormi = (int) $time;
	}
}


?>