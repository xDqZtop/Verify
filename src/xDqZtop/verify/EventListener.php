<?php
declare(strict_types=1);

namespace xDqZtop\verify;

use JsonException;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;

class EventListener implements Listener {

    /**
     * @throws JsonException
     */
    public function onJoin(PlayerJoinEvent $event): void {
        $main = Main::getInstance();
        $player = $event->getPlayer();
        $verifyManager = $main->getVerifyManager();
        $forms = $main->getForms();

        if (!$verifyManager->checkDevice($player)) {
            $forms->showDeviceMismatchForm($player);
            return;
        }

        if ($verifyManager->isVerified($player->getName())) {
            $forms->showWelcomeForm($player);
        } else {
            $forms->showVerifyOptionsForm($player);
            $verifyManager->addPendingVerification($player);
        }

        $event->setJoinMessage("");
    }

    public function onQuit(PlayerQuitEvent $event): void {
        $main = Main::getInstance();
        $main->verifyManager->removePendingVerification($event->getPlayer());
    }
}