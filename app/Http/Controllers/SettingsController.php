<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class SettingsController extends Controller
{
    public function users()
    {
        $users = User::excludeFired()->get();
        return view('cabinet.settings.users', compact('users'));
    }

    public function sync()
    {
        try {
            $exitCode = Artisan::call('ldap:manual-sync');

            $output = Artisan::output();

            // Оставляем только последние 2 строки
            $lines = explode("\n", trim($output));
            $lastTwoLines = array_slice($lines, -2); // Последние 2 строки
            $filteredOutput = implode("\n", $lastTwoLines);

            if ($exitCode === 0) {
                return response()->json([
                    'success' => true,
                    'message' => 'Данные обновлены!',
                    'output' => $filteredOutput
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Ошибка при синхронизации',
                'output' => $filteredOutput
            ], 500);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Произошла ошибка: ' . $e->getMessage()
            ], 500);
        }
    }
}
