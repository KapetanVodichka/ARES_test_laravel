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
        $columns = preg_split('/\s{2,}/', trim($record));
        $columns = array_map('trim', $columns);

        if (isset($columns[1])) {
            $columns[1] = str_replace(' ', '', $columns[1]);
        }
        if (isset($columns[4])) {
            $columns[4] = str_replace(' ', '', $columns[4]);
        }

        if (count($columns) === 7 && strpos($columns[5], ' ') !== false) {
            $parts = preg_split('/\s+/', $columns[5]);
            if (count($parts) === 2) {
                $columns[5] = $parts[0];
                array_splice($columns, 6, 0, $parts[1]);
            }
        }

        if (count($columns) < 7) {
            return [];
        }

        $activeTimeIndex = -1;
        for ($i = 0; $i < count($columns); $i++) {
            if (preg_match('/^\d{4}-\d{2}-\d{2}/', $columns[$i])) {
                $activeTimeIndex = $i;
                break;
            }
        }
        $activeTime = $activeTimeIndex !== -1 ? implode(' ', array_slice($columns, $activeTimeIndex)) : 'N/A';

        return [
            'interface'      => $columns[0] ?? '',
            'vendor_id'      => $columns[1] ?? '',
            'model_id'       => $columns[2] ?? 'N/A',
            'sn'             => $columns[3] ?? '',
            'loid'           => $columns[4] ?? 'N/A',
            'status'         => $columns[5] ?? 'N/A',
            'config_status'  => $columns[6] ?? 'N/A',
            'active_time'    => $activeTime,
        ];
    }

}
