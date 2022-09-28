<?php

declare(strict_types=1);

namespace folosuru\item_storage;

use folosuru\commandPager\commandPager;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIdentifier;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\Task;
use pocketmine\Server;

class Main extends PluginBase implements Listener {
	private static $instance;
	private array $storage;
	public static function getInstance() : Main{
		return self::$instance;
	}

	public function getStorage(Player|string $player) : ItemStorage{
		if ($player instanceof Player) $player = $player->getName();
		if (array_key_exists($player,$this->storage)){
			return $this->storage[$player];
		}else{
			return $this->storage[$player] = new ItemStorage();
		}
	}

	public function getAllStorage() : array{
		return $this->storage;
	}

	public function setStorage(array $storage): void{
		$this->storage = $storage;
	}

	public function onEnable(): void{
		$this->getServer()->getPluginManager()->registerEvents($this,$this);
		$this->storage = array();
		if (!file_exists($this->getDataFolder()."Player/")){
			mkdir($this->getDataFolder()."Player/",0777,true);
		}
		$this->getServer()->getAsyncPool()->submitTask(new DataLoadTask($this->getDataFolder()."Player/"));
		$this->getScheduler()->scheduleRepeatingTask(
			new class extends Task{
				public function onRun(): void{
					Server::getInstance()->getAsyncPool()->submitTask(new dataWriteTask(Main::getInstance()->getAllStorage(),Main::getInstance()->getDataFolder()."Player/"));
				}
			},24000);
	}

	protected function onDisable(): void{
		foreach ($this->getAllStorage() as $key => $item){
			if ($item instanceof ItemStorage){
				file_put_contents($this->getDataFolder()."Player/".$key.".json",json_encode($item->getAllItem()));
			}
		}
	}

	public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool{
		switch ($command->getName()){
			case "itemstorage":
			case "its":
				if (!array_key_exists($sender->getName(),$this->storage)){
					$this->storage[$sender->getName()] = new ItemStorage();
				}
				if ($sender instanceof Player) {
					$storage = $this->getStorage($sender);
					switch ($args[0]) {
						/*
						 * upload
						 * 	usage: /its up 33:4 64
						   */
						case "up":
						case "upload":
							if (count($args) != 3) $sender->sendMessage('構文が正しくない');
							if (!is_numeric($args[2])) {
								$sender->sendMessage("P3 is not number");
								break;
							}
							if (count($itemid = explode(":", $args[1])) == 2 and is_numeric($itemid[0]) and is_numeric($itemid[1])) {
								$item = (new Item(new ItemIdentifier((int)$itemid[0], (int)$itemid[1])))->setCount((int)$args[2]);
							} elseif ($args[1] == "hand") {
								$item = $sender->getInventory()->getItemInHand()->setCount((int)$args[2]);
							}else{
								$sender->sendMessage("Item format illegal");
								break;
							}
							$count=0;
							foreach ($sender->getInventory()->getContents() as $i) {
								if ($i->getID() == $item->getID() and $i->getMeta() == $item->getMeta()) {
									$count += $i->getCount();
								}
							}
							if ($count < $args[2]) {
								$sender->sendMessage("item need more");
								break;
							}
							$sender->getInventory()->removeItem($item);
							$storage->addItem($item);
							break;

						case "down":
						case "download":
							if (!is_numeric($args[2])) {
								$sender->sendMessage("P3 is not number");
								break;
							}
							if (count($itemid = explode(":", $args[1])) == 2 and is_numeric($itemid[0]) and is_numeric($itemid[1])) {
								$item = new Item(new ItemIdentifier((int)$itemid[0], (int)$itemid[1]));
							} elseif ($args[1] == "hand") {
								$item = $sender->getInventory()->getItemInHand();
							}else{
								$sender->sendMessage("Item format illegal");
								break;
							}
							if ($storage->getCount($item) < $args[2]){
								$sender->sendMessage("storage need more");
								break;
							}
							if ($sender->getInventory()->canAddItem($item)){
								$sender->getInventory()->addItem($item);
								$storage->removeItem($item);
								$sender->sendMessage("downloaded");
							}
							break;

						case "list":
							$result=[];
							foreach ($storage->getAllItem() as $key => $i){
								$itemid=explode(":", $key);
								$item = ItemFactory::getInstance()->get((int)$itemid[0], (int)$itemid[1]);
								$result[] =$item->getName() ."(".$key.") = ".$i;
							}
							commandPager::getInstance()->getPager($sender)->newPages($result,"Item Storage List")->sendMessage();
							break;

						case "count":
							if (count($itemid = explode(":", $args[1])) == 2 and is_numeric($itemid[0]) and is_numeric($itemid[1])) {
								$item = ItemFactory::getInstance()->get((int)$itemid[0], (int)$itemid[1]);
							} elseif ($args[1] == "hand") {
								$item = $sender->getInventory()->getItemInHand();
							}else{
								$sender->sendMessage("Item format illegal");
								break;
							}
							$sender->sendMessage($item->getName()."(".$item->getId().":".$item->getMeta().") = ". $storage->getCount($item));
					}
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
