<?php

namespace folosuru\item_storage;

use pocketmine\item\Item;

class ItemStorage{
	private array $item;

	public function __construct(){
		$this->item = array();
	}


	public function addItem(Item $item) : void{
		$this->existsItem($item);
		$this->item[$item->getId().':'.$item->getMeta()] = $item->getCount();
	}

	public function hasItem(Item $item) : bool{
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
		if ($this->item[$item->getId().':'.$item->getMeta()] == $item->getCount()){
			unset($this->item[$item->getId().':'.$item->getMeta()]);
		}else {
			$this->item[$item->getId() . ':' . $item->getMeta()] -= $item->getCount();
		}
	}

	/***	system	***/

	private function existsItem(Item $item) : void{
		if (!array_key_exists($item->getId().':'.$item->getMeta(),$this->item)){
			$this->item[$item->getId().':'.$item->getMeta()] = 0;
		}
	}

	/**
	 * @return array
	 */
	public function getAllItem(): array{
		return $this->item;
	}

	public function setAllItem(array $item){
		$this->item = $item;
	}




}