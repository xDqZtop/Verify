<?php

declare(strict_types=1);

namespace xDqZtop\verify;

use JsonException;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat as TF;

class Main extends PluginBase implements Listener {

    public Config $config;

    protected function onEnable(): void
    {
        $logger = $this->getLogger();
        $logger->notice("Loading...");

        $this->saveResource("config.json");
        $this->config = new Config($this->getDataFolder() . "config.json", Config::JSON);

        $this->getServer()->getPluginManager()->registerEvents($this, $this);

        $logger->notice("Enabled.");
    }

    protected function onDisable(): void
    {
        $this->getLogger()->notice("Disabled.");
    }

    /**
     * @throws JsonException
     */
    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
    {
        if ($command->getName() !== "verify") {
            return false;
        }

        if (empty($args)) {
            $sender->sendMessage(TF::MINECOIN_GOLD . "Usage: verify <add|remove|list> [name]");
            return true;
        }

        $subCommand = strtolower($args[0]);

        if ($subCommand === "list") {
            $list = $this->verifyList();
            $sender->sendMessage(TF::GREEN . "Verified players: " . TF::AQUA . implode(", ", $list));
            return true;
        }

        if (!isset($args[1])) {
            $sender->sendMessage(TF::RED . "Please specify a player name");
            return true;
        }

        $name = $args[1];

        switch ($subCommand) {
            case "add":
                $this->verifyAdd($name);
                $sender->sendMessage(TF::GREEN . "Player " . TF::AQUA . $name . TF::GREEN . " verified!");
                break;
            case "remove":
                $this->verifyRemove($name);
                $sender->sendMessage(TF::GREEN . "Player " . TF::AQUA . $name . TF::GREEN . " unverified!");
                break;
            default:
                $sender->sendMessage(TF::RED . "Unknown subcommand. Use: add, remove, or list");
                break;
        }

        return true;
    }

    private function exists(string $name): bool
    {
        return $this->config->exists("players.$name");
    }

    /**
     * @throws JsonException
     */
    public function verifyAdd(string $name): void
    {
        $this->config->setNested("players.$name", true);
        $this->config->save();
    }

    /**
     * @throws JsonException
     */
    public function verifyRemove(string $name): void
    {
        $this->config->removeNested("players.$name");
        $this->config->save();
    }

    public function verifyList(): array
    {
        return array_keys($this->config->getNested("players", []));
    }

    public function onJoin(PlayerJoinEvent $event): void
    {
        $player = $event->getPlayer();
        $name = $player->getName();

        if (!$this->exists($name)) {
            $player->kick(TF::RED . $this->getConfig()->get("error-join", "You are not verified!"));
        } else {
            $server = $this->getConfig()->get("success-join");
            if ($server !== null) {
                $player->transfer($server);
            }
        }

        $event->setJoinMessage("");
    }
}