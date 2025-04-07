<?php

namespace App\Services\Parsers;

class OnuStatsParser implements ParserInterface
{
    public function parse(string $data): array
    {
        $lines = explode("\n", trim($data));
        $parsed = [];
        $currentRecord = '';

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }

            if (strpos($line, 'OLT-Leninskoe-GPON#') !== false) {
                if (!empty($currentRecord) && preg_match('/^gpon0\/1:\d+\s+/', $currentRecord)) {
                    $parsed[] = $this->parseRecord($currentRecord);
                }
                break;
            }

            if (preg_match('/^gpon0\/1:\d+\s+/', $line)) {
                if (!empty($currentRecord) && preg_match('/^gpon0\/1:\d+\s+/', $currentRecord)) {
                    $parsed[] = $this->parseRecord($currentRecord);
                }
                $currentRecord = $line;
            } elseif (preg_match('/^gpon0\//', $line)) {
                if (!empty($currentRecord) && preg_match('/^gpon0\/1:\d+\s+/', $currentRecord)) {
                    $parsed[] = $this->parseRecord($currentRecord);
                }
                $currentRecord = $line;
            } else {
                $currentRecord .= $line;
            }
        }

        if (!empty($currentRecord) && preg_match('/^gpon0\/1:\d+\s+/', $currentRecord)) {
            $parsed[] = $this->parseRecord($currentRecord);
        }

        return array_filter($parsed);
    }

    private function parseRecord(string $record): array
    {
        $fields = preg_split('/\s{2,}/', trim($record));
        if (count($fields) >= 6) {
            return [
                'interface' => $fields[0],
                'temperature' => $fields[1],
                'voltage' => $fields[2],
                'bias' => $fields[3],
                'rx_power' => $fields[4],
                'tx_power' => $fields[5],
            ];
        }
        return [];
    }
}
