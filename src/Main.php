<?php

declare(strict_types=1);

namespace folosuru\item_storage;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;

class Main extends PluginBase implements Listener {
	private static $instance;
	public $storage;
	public static function getInstance() : Main{
		return self::$instance;
	}

	public function GetStorage(Player $player){
		if (array_key_exists($player->getName(),$this->storage)){
			return $this->storage[$player->getName()];
		}else{
			return $this->storage[$player->getName()] = new ItemStorage();
		}
	}

	public function onEnable(): void{
		$this->getServer()->getPluginManager()->registerEvents($this,$this);
		$this->storage = array();
	}

	public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool{
		switch ($command->getName()){
			case "itemstorage":
			case "its":
				switch ($args[0]){
					case "up":
					case "upload":
				}
				break;

		}
		return true;
	}

	public function onLoad(): void{
		if(!self::$instance instanceof Main){
			self::$instance = $this;
		}
	}



}
