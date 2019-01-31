<?php
declare(strict_types = 1);

namespace JackMD\EssentialsHomesImporter;

use JackMD\EasyHomes\Main;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\level\Location;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

class EssentialsHomesImporter extends PluginBase{

	private const PREFIX = "§7[§6EH§eI§7]§r ";

	/** @var Main */
	private $easyHomes;

	public function onEnable(){
		if(!is_dir($this->getDataFolder())){
			mkdir($this->getDataFolder());
		}

		$this->easyHomes = $this->getServer()->getPluginManager()->getPlugin("EasyHomes");
		$this->getLogger()->info("Plugin Enabled.");
	}

	public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool{
		switch(strtolower($command->getName())){
			case "import":
				if(!isset($args[0])){
					$sender->sendMessage(self::PREFIX . "§cPlease make sure that you have put the EssentialsPE folder located in plugin_data\EssentialsPE into plugin_data\EssentialsHomesImporter folder.");
					$sender->sendMessage(self::PREFIX . "§cUse command §d/import confirm §cto continue...");

					return false;
				}
				if($args[0] === "confirm"){
					$path = $this->getDataFolder() . "EssentialsPE" . DIRECTORY_SEPARATOR . "Sessions" . DIRECTORY_SEPARATOR;

					foreach(glob($path . "*") as $folderPath){
						$sender->sendMessage(self::PREFIX . "§2Looking for files in §6" . $folderPath);
						$folderPath = $folderPath . DIRECTORY_SEPARATOR;

						foreach(glob($folderPath . "*.session") as $filePath){
							$playerName = str_replace([$folderPath, ".session"], "", $filePath);
							$sender->sendMessage(self::PREFIX . "§2Found file for player §e$playerName §ain §6$filePath");

							$config = new Config($filePath, Config::JSON);
							$homes = $config->get("homes");
							if(!empty($homes)){
								foreach($homes as $homeName => $data){
									if($this->getServer()->isLevelGenerated($data[3])){
										if(!$this->getServer()->isLevelLoaded($data[3])){
											$this->getServer()->loadLevel($data[3]);
										}
									}

									$x = $data[0];
									$y = $data[1];
									$z = $data[2];
									$levelName = $data[3];
									$yaw = $data[4];
									$pitch = $data[5];

									$location = new Location($x, $y, $z, $yaw, $pitch, $this->getServer()->getLevelByName($levelName));
									$this->easyHomes->getProvider()->setHome($playerName, $homeName, $location, $yaw, $pitch);

									$sender->sendMessage(self::PREFIX . "§aHome §d" . $homeName . " §aof player §d" . $playerName . " §asuccessfully imported to EasyHomes.");
								}
							}
						}
					}
				}
		}

		return false;
	}
}