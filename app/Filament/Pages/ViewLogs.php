<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Illuminate\Support\Facades\File;

class ViewLogs extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-text';
    protected string $view = 'filament.pages.view-logs';
    protected static ?string $navigationLabel = 'Logs';
    protected static ?string $title = 'Application Logs';

    public function getLogs(): array
    {
        $logFile = storage_path('logs/laravel.log');

        if (!File::exists($logFile)) {
            return [];
        }

        $content = File::get($logFile);
        $logs = [];

        preg_match_all(
            '/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] \w+\.(\w+): (.+?)(?=\[\d{4}|\z)/s',
            $content,
            $matches,
            PREG_SET_ORDER
        );

        foreach (array_reverse($matches) as $match) {
            $datetime = $match[1];
            $level    = strtolower($match[2]);
            $body     = trim($match[3]);

            $lines   = explode("\n", $body);
            $message = trim($lines[0]);

            $message = preg_replace('/ \{\"exception\".+$/s', '', $message);
            $message = trim($message);

            $path = null;
            if (preg_match('/ in ([\/A-Za-z]:[^\s]+\.php(?::\d+)?)/', $body, $pathMatch)) {
                $path = $pathMatch[1];
            }

            $logs[] = [
                'datetime' => $datetime,
                'level'    => $level,
                'message'  => $message,
                'path'     => $path,
            ];
        }

        return array_slice($logs, 0, 200);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('clear_logs')
                ->label('Clear Logs')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Clear log file?')
                ->modalDescription('This will permanently delete all log entries.')
                ->modalSubmitActionLabel('Yes, clear logs')
                ->action(function () {
                    File::put(storage_path('logs/laravel.log'), '');
                    $this->dispatch('$refresh');
                }),
        ];
    }
}
