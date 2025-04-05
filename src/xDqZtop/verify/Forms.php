<?php
declare(strict_types=1);

namespace xDqZtop\verify;

use jojoe77777\FormAPI\{
    SimpleForm,
    CustomForm
};
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as TF;

class Forms {

    public function showWelcomeForm(Player $player): void {
        $main = Main::getInstance();
        $form = new SimpleForm(function(Player $player, ?int $data) use ($main) {
            if ($data === null) return;

            if ($data === 0) {
                $address = $main->getVerifyManager()->getServerAddress();
                if (!empty($address)) {
                    $player->transfer($address);
                } else {
                    $player->sendMessage(TF::RED . "Server address not configured!");
                }
            } else {
                $player->kick(TF::YELLOW . $main->getVerifyManager()->getMessage("leave_message"));
            }
        });

        $form->setTitle(TF::GREEN . "Welcome Back!");
        $form->setContent(
            TF::GREEN . "You are verified!\n\n" .
            TF::WHITE . "Click below to join the main server or leave."
        );
        $form->addButton(TF::GREEN . "Join Server", 0, "textures/ui/confirm.png");
        $form->addButton(TF::RED . "Leave", 0, "textures/ui/cancel.png");

        $player->sendForm($form);
    }

    public function showVerifyOptionsForm(Player $player): void {
        $main = Main::getInstance();
        $form = new SimpleForm(function(Player $player, ?int $data) use ($main) {
            if ($data === null) {
                $player->kick(TF::RED . $main->getVerifyManager()->getMessage("timeout_message"));
                return;
            }

            switch ($data) {
                case 0:
                    $this->showCodeVerifyForm($player);
                    break;
                case 1:
                    $player->kick(TF::YELLOW . "Email verification coming soon!");
                    break;
                case 2:
                    $player->kick(TF::YELLOW . $main->getVerifyManager()->getContactSupport());
                    break;
            }
        });

        $form->setTitle(TF::RED . "Verification Required");
        $form->setContent(
            TF::RED . "You are not verified!\n" .
            TF::WHITE . "Please choose a verification method:"
        );
        $form->addButton(TF::BLUE . "Verify with Code", 0, "textures/ui/icon_book_writable.png");
        $form->addButton(TF::AQUA . "Verify with Email", 0, "textures/ui/icon_mail.png");
        $form->addButton(TF::GOLD . "Contact Support", 0, "textures/ui/feedback.png");

        $player->sendForm($form);
    }

    public function showCodeVerifyForm(Player $player): void {
        $main = Main::getInstance();
        $form = new CustomForm(function(Player $player, ?array $data) use ($main) {
            if ($data === null) {
                $player->kick(TF::RED . $main->getVerifyManager()->getMessage("timeout_message"));
                return;
            }

            $code = $data[1] ?? "";
            if ($main->getVerifyManager()->checkVerifyCode($player->getName(), $code)) {
                $main->getVerifyManager()->verifyPlayer($player->getName());
                $player->sendMessage(TF::GREEN . $main->getVerifyManager()->getMessage("verify_success"));
            } else {
                $player->kick(TF::RED . $main->getVerifyManager()->getMessage("verify_failed"));
            }
        });

        $form->setTitle(TF::GOLD . "Enter Verification Code");
        $form->addLabel("Please enter the 6-digit code you received:");
        $form->addInput("Verification Code:", "123456");

        $player->sendForm($form);
    }

    public function showDeviceMismatchForm(Player $player): void {
        $main = Main::getInstance();
        $form = new SimpleForm(function(Player $player) use ($main) {
            $player->kick(TF::RED . $main->getVerifyManager()->getMessage("device_mismatch"));
        });

        $form->setTitle(TF::RED . "Device Mismatch");
        $form->setContent(
            TF::RED . "Your device doesn't match our records!\n\n" .
            TF::WHITE . "If this is an error, please contact support."
        );
        $form->addButton(TF::RED . "Close", 0, "textures/ui/cancel.png");

        $player->sendForm($form);
    }
}