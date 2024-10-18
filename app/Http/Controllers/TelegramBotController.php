<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Laravel\Facades\Telegram;

class TelegramBotController extends Controller
{
    public function handleWebhook()
    {
        $update = Telegram::commandsHandler(true);
        \Log::info('Update received: ', (array) $update);

        // Проверка типа обновления
        if ($update->isType('message')) {
            $message = $update->getMessage();
            \Log::info('Message: ', (array) $message);

            if ($message && $message->has('text')) {
                $text = $message->getText();
                $telegramUserId = $message->getFrom()->getId(); // Получение ID Telegram пользователя
                $groupChatId = config('services.telegram.chat_id');
                $groupChat = Telegram::getChat(['chat_id' => $groupChatId]);

                if (filter_var($text, FILTER_VALIDATE_EMAIL)) {
                    $user = User::where('email', $text)->first();

                    if ($user) {
                        if ($user->telegram_id) {
                            $responseText = 'Я тебя уже знаю, ' . $user->name;
                        } else {
                            $user->update(['telegram_id' => $telegramUserId]);
                            $responseText = 'Спасибо ' . $user->name . "\n" . 'Теперь я могу отмечать тебя в группе: ' . $groupChat->getTitle();
                            Telegram::sendMessage([
                                'chat_id' => $groupChatId,
                                'text' => '[' . $user->name . '](tg://user?id=' . $telegramUserId . '), я тебя запомнил 🧛',
                                'parse_mode' => 'Markdown',
                            ]);
                        }
                    } else {
                        $responseText = 'Email ' . $text . ' не найден';
                    }
                } else {
                    $responseText = 'Адрес электронной почты не корректный';
                }

                Telegram::sendMessage([
                    'chat_id' => $message->getChat()->getId(),
                    'text' => $responseText,
                ]);
            } else {
                \Log::info('No text found in message');
            }
        } else {
            \Log::info('Received non-message update: ', (array) $update);
        }

        return response()->json(['status' => 'ok']);
    }
}
