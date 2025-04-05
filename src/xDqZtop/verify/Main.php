<?php
declare(strict_types=1);

namespace xDqZtop\verify;

use JsonException;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\TextFormat as TF;

class Main extends PluginBase {

    public static ?Main $instance = null;
    public VerifyManager $verifyManager;
    public Forms $forms;

    public static function getInstance(): Main {
        return self::$instance;
    }

    protected function onEnable(): void {
        self::$instance = $this;
        $this->getLogger()->info(TF::GREEN . "Loading VerifySystem...");
        $this->verifyManager = new VerifyManager($this);
        $this->forms = new Forms($this);
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
        $this->getScheduler()->scheduleRepeatingTask(new ClosureTask(
            fn() => $this->verifyManager->checkTimeouts()
        ), 20 * 60);
        $this->getLogger()->info(TF::GREEN . "VerifySystem enabled!");
    }

    public function getVerifyManager(): VerifyManager {
        return $this->verifyManager;
    }

    public function getForms(): Forms {
        return $this->forms;
    }

    /**
     * @throws JsonException
     */
    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {
        if ($command->getName() !== "verify") {
            return false;
        }

        if (empty($args)) {
            $sender->sendMessage(TF::RED . "Usage: /verify <add|remove|list|createcode> [player]");
            return true;
        }

        $subCommand = strtolower($args[0]);

        switch ($subCommand) {
            case "add":
                if (isset($args[1])) {
                    $this->verifyManager->verifyPlayer($args[1]);
                    $sender->sendMessage(TF::GREEN . "Player " . $args[1] . " has been verified!");
                } else {
                    $sender->sendMessage(TF::RED . "Usage: /verify add <player>");
                }
                break;

            case "remove":
            case "unverify":
                if (isset($args[1])) {
                    $this->verifyManager->unverifyPlayer($args[1]);
                    $sender->sendMessage(TF::GREEN . "Player " . $args[1] . " has been unverified!");
                } else {
                    $sender->sendMessage(TF::RED . "Usage: /verify remove <player>");
                }
                break;

            case "list":
                $verifiedPlayers = $this->verifyManager->getVerifiedPlayers();
                $sender->sendMessage(TF::GREEN . "Verified players (" . count($verifiedPlayers) . "):");
                $sender->sendMessage(implode(", ", $verifiedPlayers));
                break;

            case "createcode":
            case "cc":
                if (isset($args[1])) {
                    $code = $this->verifyManager->createVerifyCode($args[1]);
                    $sender->sendMessage(TF::GREEN . "Verification code for " . $args[1] . ": " . TF::YELLOW . $code);
                } else {
                    $sender->sendMessage(TF::RED . "Usage: /verify createcode <player>");
                }
                break;

            default:
                $sender->sendMessage(TF::RED . "Unknown subcommand. Available: add, remove, list, createcode");
                break;
        }

        return true;
    }

    protected function onDisable(): void {
        $this->getLogger()->info(TF::RED . "VerifySystem disabled!");
    }
}