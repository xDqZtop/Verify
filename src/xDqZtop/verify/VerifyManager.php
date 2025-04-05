<?php
declare(strict_types=1);

namespace xDqZtop\verify;

use pocketmine\player\Player;
use pocketmine\utils\Config;
use JsonException;

class VerifyManager {

    private Config $config;
    private string $playersPath;
    private array $pendingVerifications = [];

    public function __construct(Main $plugin) {
        $this->playersPath = $plugin->getDataFolder() . "players/";
        if (!file_exists($this->playersPath)) {
            mkdir($this->playersPath, 0777, true);
        }
        $plugin->saveResource("config.yml");
        $this->config = new Config($plugin->getDataFolder() . "config.yml", Config::YAML);
    }

    public function getPlayerData(string $playerName): Config {
        $playerName = strtolower($playerName);
        return new Config($this->playersPath . $playerName . ".yml", Config::YAML, [
            "verified" => false,
            "device_id" => "",
            "verify_code" => "",
            "last_login" => time()
        ]);
    }

    public function isVerified(string $playerName): bool {
        return $this->getPlayerData($playerName)->get("verified", "false");
    }

    /**
     * @throws JsonException
     */
    public function verifyPlayer(string $playerName): void {
        $data = $this->getPlayerData($playerName);
        $data->set("verified", "true");
        $data->save();
    }

    /**
     * @throws JsonException
     */
    public function unverifyPlayer(string $playerName): void {
        $data = $this->getPlayerData($playerName);
        $data->set("verified", false);
        $data->save();
    }

    public function getVerifiedPlayers(): array {
        $players = [];
        foreach (glob($this->playersPath . "*.yml") as $file) {
            $playerName = basename($file, ".yml");
            if ($this->isVerified($playerName)) {
                $players[] = $playerName;
            }
        }
        return $players;
    }

    /**
     * @throws JsonException
     */
    public function createVerifyCode(string $playerName): string {
        $code = substr(md5(uniqid()), 0, 6);
        $data = $this->getPlayerData($playerName);
        $data->set("verify_code", $code);
        $data->save();
        return $code;
    }

    public function checkVerifyCode(string $playerName, string $code): bool {
        return $this->getPlayerData($playerName)->get("verify_code", "") === $code;
    }

    /**
     * @throws JsonException
     */
    public function checkDevice(Player $player): bool {
        $data = $this->getPlayerData($player->getName());
        $currentDeviceId = $this->generateDeviceId($player);
        $registeredDeviceId = $data->get("device_id", "");

        if (empty($registeredDeviceId)) {
            $data->set("device_id", $currentDeviceId);
            $data->save();
            return true;
        }

        return $currentDeviceId === $registeredDeviceId;
    }

    private function generateDeviceId(Player $player): string {
        return hash("sha256",
            $player->getNetworkSession()->getIp() .
            $player->getUniqueId()->toString()
        );
    }

    public function addPendingVerification(Player $player): void {
        $this->pendingVerifications[strtolower($player->getName())] = time();
    }

    public function removePendingVerification(Player $player): void {
        unset($this->pendingVerifications[strtolower($player->getName())]);
    }

    public function checkTimeouts(): void {
        $timeout = $this->config->get("verification_timeout", 120);
        $currentTime = time();

        foreach ($this->pendingVerifications as $name => $startTime) {
            if ($currentTime - $startTime > $timeout) {
                Main::getInstance()->getServer()->getPlayerExact($name)?->kick($this->getMessage("timeout_message"));
                unset($this->pendingVerifications[$name]);
            }
        }
    }

    public function getMessage(string $key): string {
        return $this->config->get("messages")[$key] ?? $key;
    }

    public function getServerAddress(): string {
        return $this->config->get("server_address", "");
    }

    public function getContactSupport(): string {
        return $this->config->get("contact_support", "");
    }
}