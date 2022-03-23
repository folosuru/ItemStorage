<?php

namespace folosuru\item_storage;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\item\Item;

class ItemStorage{
	private $item;

	public function __construct(){
		$this->item =array();
	}


	public function addItem(Item $item) : void{
		$this->existsItem($item);
		$this->item[$item->getId().':'.$item->getMeta()] = $item->getCount();
	}

	public function canRemoveItem(Item $item) : bool{
		$this->existsItem($item);
		if ($this->item[$item->getId().':'.$item->getMeta()] >= $item->getCount()){
			return true;
		}else{
			return false;
		}
	}

	public function getCount(Item $item) : int{
		$this->existsItem($item);
		return $this->item[$item->getId().':'.$item->getMeta()];
	}

	public function removeItem(Item $item) : void{
		$this->existsItem($item);
		if ($this->item[$item->getId().':'.$item->getMeta()] >= $item->getCount()){
			$this->item[$item->getId().':'.$item->getMeta()] -= $item->getCount();
		}else{
			$this->item[$item->getId().':'.$item->getMeta()] = 0;
		}
	}

    public function getAll(): array{
        return $this->item;
    }

	/***	system	***/

	public function existsItem(Item $item) : void{
		if (!array_key_exists($item->getId().':'.$item->getMeta(),$this->item)){
			$this->item[$item->getId().':'.$item->getMeta()] = 0;
		}
	}



}