<?php

namespace App\Services\Parsers;

class OnuDataParser implements ParserInterface
{
    public function parse(string $data): array
    {
        $lines = explode("\n", trim($data));
        $parsed = [];
        $currentRecord = '';
        $dataStarted = false;

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }

            if (preg_match('/^GPON0\/1:\d+\s+/', $line)) {
                $dataStarted = true;
            }

            if (!$dataStarted) {
                continue;
            }

            if (strpos($line, 'OLT-Leninskoe-GPON#') !== false) {
                if (!empty($currentRecord)) {
                    $parsed[] = $this->parseRecord($currentRecord);
                }
                break;
            }

            if (preg_match('/^GPON0\/1:\d+\s+/', $line)) {
                if (!empty($currentRecord)) {
                    $parsed[] = $this->parseRecord($currentRecord);
                }
                $currentRecord = $line;
            } else {
                $currentRecord .= ' ' . $line;
            }
        }

        if (!empty($currentRecord)) {
            $parsed[] = $this->parseRecord($currentRecord);
        }

        return array_filter($parsed);
    }

    private function parseRecord(string $record): array
    {
        $columns = preg_split('/\s+/', trim($record));
        if (count($columns) < 7) {
            return [];
        }

        $vendorId = $columns[1] ?? '';
        if (isset($columns[2]) && strlen($columns[1]) === 3 && preg_match('/^[A-Z]$/', $columns[2])) {
            $vendorId .= $columns[2];
            array_splice($columns, 2, 1);
        }

        $sn = $columns[3] ?? '';
        if (isset($columns[4]) && !in_array($columns[4], ['N/A', 'active', 'off-line', 'disabled']) && strpos($columns[4], ':') !== false) {
            $sn .= $columns[4];
            array_splice($columns, 4, 1);
        }

        $activeTimeIndex = -1;
        for ($i = 0; $i < count($columns); $i++) {
            if (preg_match('/^\d{4}-\d{2}-\d{2}/', $columns[$i])) {
                $activeTimeIndex = $i;
                break;
            }
        }

        return [
            'interface' => $columns[0] ?? '',
            'vendor_id' => $vendorId,
            'model_id' => $columns[2] ?? 'N/A',
            'sn' => $sn,
            'loid' => $columns[4] ?? 'N/A',
            'status' => $columns[5] ?? 'N/A',
            'config_status' => $columns[6] ?? 'N/A',
            'active_time' => $activeTimeIndex !== -1 ? implode(' ', array_slice($columns, $activeTimeIndex)) : 'N/A',
        ];
    }
}
