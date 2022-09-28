<?php

namespace folosuru\item_storage;


use pocketmine\scheduler\AsyncTask;

class dataWriteTask extends AsyncTask{


	private array $data;
	private string $filepath;

	public function __construct(array $data,string $filepath){
		$this->data = $data;
		$this->filepath = $filepath;
	}

	public function onRun(): void{
		foreach ($this->data as $key => $item){
			if ($item instanceof ItemStorage){
				file_put_contents($this->filepath.$key.".json",json_encode($item->getAllItem()));
			}
		}

	}

	public function onCompletion(): void{

	}
}