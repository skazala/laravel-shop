<x-filament-panels::page>

    @php
        $logs = $this->getLogs();

        $levelBadgeStyles = [
            'error' => 'background:#fee2e2;color:#b91c1c;',
            'critical' => 'background:#fecaca;color:#991b1b;',
            'warning' => 'background:#ffedd5;color:#c2410c;',
            'alert' => 'background:#fed7aa;color:#9a3412;',
            'notice' => 'background:#dbeafe;color:#1d4ed8;',
            'info' => 'background:#dcfce7;color:#15803d;',
            'debug' => 'background:#f3f4f6;color:#4b5563;',
            'emergency' => 'background:#f3e8ff;color:#7e22ce;',
        ];

        $rowStyles = [
            'error' => 'background:#fff1f2;',
            'critical' => 'background:#ffe4e6;',
            'warning' => 'background:#fff7ed;',
            'alert' => 'background:#ffedd5;',
            'notice' => 'background:#eff6ff;',
            'info' => 'background:#f0fdf4;',
            'debug' => 'background:#f9fafb;',
            'emergency' => 'background:#faf5ff;',
        ];
    @endphp

    @if (empty($logs))
        <div class="text-center text-gray-500 dark:text-gray-400 py-12">
            No log entries found.
        </div>
    @else
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <table class="w-full text-sm">
                <thead
                    class="bg-gray-50 dark:bg-gray-800 text-gray-600 dark:text-gray-300 text-xs uppercase tracking-wider">
                    <tr>
                        <th class="text-left" style="padding: 14px 24px; width: 170px;">Date / Time</th>
                        <th class="text-left" style="padding: 14px 24px; width: 100px;">Level</th>
                        <th class="text-left" style="padding: 14px 24px;">Message</th>
                        <th class="text-left" style="padding: 14px 24px; width: 280px;">Path</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($logs as $log)
                        <tr
                            style="{{ $rowStyles[$log['level']] ?? 'background:#ffffff;' }} border-top: 1px solid #e5e7eb;">
                            <td
                                style="padding: 14px 24px; font-family: monospace; font-size: 12px; color: #6b7280; white-space: nowrap;">
                                {{ $log['datetime'] }}
                            </td>
                            <td style="padding: 14px 24px;">
                                <span
                                    style="{{ $levelBadgeStyles[$log['level']] ?? 'background:#f3f4f6;color:#4b5563;' }} font-size: 11px; font-weight: 600; padding: 3px 10px; border-radius: 9999px; text-transform: uppercase;">
                                    {{ $log['level'] }}
                                </span>
                            </td>
                            <td style="padding: 14px 24px; color: #111827;">
                                {{ $log['message'] }}
                            </td>
                            <td
                                style="padding: 14px 24px; font-family: monospace; font-size: 11px; color: #9ca3af; word-break: break-all;">
                                {{ $log['path'] ?? '—' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

</x-filament-panels::page>
