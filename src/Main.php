<?php

declare(strict_types=1);

namespace folosuru\item_storage;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\item\Item;
use pocketmine\item\ItemIdentifier;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;

class Main extends PluginBase implements Listener {
	private static $instance;
	public $storage;
	public static function getInstance() : Main{
		return self::$instance;
	}

	public function getStorage(Player $player){
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
					case 'up':
					case 'upload':
                        if ($sender instanceof Player) {
                            if (!is_countable($args[2])){
                                $sender->sendMessage('書式不正');
                            }
                            $inventory = $sender->getInventory();
                                if ($args[1] == "hand") {
                                    if (count($args) == 3) {
                                        $item = ($inventory->getItemInHand())->setCount((int)$args[2]);
                                        if ($inventory->contains($item)) {
                                            $inventory->removeItem($item);
                                            $this->getStorage($sender)->addItem($item);
                                            $sender->sendMessage('uploaded ' . $item->getName() . 'x' . $item->getCount());
                                        }
                                    } else {
                                        $sender->sendMessage('usage /itemstorage upload hand [count]');
                                    }
                                }
                                if (count($args) == 4) {
                                    if (is_countable($args[1]) and is_countable($args[3])) {
                                        $item = (new Item(new ItemIdentifier($args[1], $args[2])))->setCount($args[3]);
                                        if ($inventory->contains($item)){
                                            $inventory->removeItem($item);
                                            $this->getStorage($sender)->addItem($item);
                                            $sender->sendMessage('uploaded ' . $item->getName() . 'x' . $item->getCount());
                                        }else{
                                            $sender->sendMessage('足りない');
                                        }
                                    }
                                }
                        }else{
                            $sender->sendMessage('[Error] 誰だお前は！（CommandSenderがPlayerクラスではありません）');
                        }
                      break;
                    case 'down':
                    case 'download':
                        if ($sender instanceof Player) {
                            if (!is_countable($args[2])){
                                $sender->sendMessage('書式不正');
                                return true;
                            }
                            $inventory = $sender->getInventory();
                            if ($args[1] == "hand") {
                                if (count($args) == 3) {
                                    $item = ($inventory->getItemInHand())->setCount((int)$args[2]);
                                }
                            }
                            if (count($args) == 4) {
                                if (is_countable($args[1]) and is_countable($args[3])) {
                                    $item = (new Item(new ItemIdentifier($args[1], $args[2])))->setCount($args[3]);
                                }
                            }
                            if (empty($item)) {
                                $storage = $this->getStorage($sender);
                                if ($inventory->canAddItem($item) and $storage->canRemoveItem($item)) {
                                    $inventory->addItem($item);
                                    $storage->removeItem($item);
                                    $sender->sendMessage('downloaded ' . $item->getName() . ' x' . $item->getCount());
                                }
                            }else{  # Itemインスタンスじゃない→どの構文にも合致してない→構文が間違っている
                                $sender->sendMessage('書式不正');
                                return true;
                            }

                        }else{
                            $sender->sendMessage('[Error] 誰だお前は！（CommandSenderがPlayerクラスではありません）');
                        }
                    case 'list':
                        if ($sender instanceof Player){
                            $storage = $this->getStorage($sender);
                            $count = 0;
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
