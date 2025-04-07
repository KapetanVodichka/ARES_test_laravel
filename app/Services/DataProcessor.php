<?php

namespace App\Services;

use App\Services\Parsers\ParserInterface;
use Illuminate\Http\Request;

class DataProcessor
{
    protected ApiService $apiService;
    protected ParserInterface $onuDataParser;
    protected ParserInterface $onuStatsParser;

    public function __construct(ApiService $apiService, ParserInterface $onuDataParser, ParserInterface $onuStatsParser)
    {
        $this->apiService = $apiService;
        $this->onuDataParser = $onuDataParser;
        $this->onuStatsParser = $onuStatsParser;
    }

    public function process(Request $request): array
    {
        // Извлечение параметров фильтрации из запроса
        $sortField = $request->query('sort_field', 'interface');
        $sortDirection = $request->query('sort_direction', 'asc');
        $filters = $request->query('filters', []);
        $page = (int) $request->query('page', 1);
        $perPage = (int) $request->query('per_page', 100);

        if (is_string($filters)) {
            $filters = json_decode($filters, true) ?? [];
        }

        // Получение данных
        $onuDataRaw = $this->apiService->getOnuData();
        $onuStatsRaw = $this->apiService->getOnuStats();

        // Парсинг
        $onuData = $this->onuDataParser->parse($onuDataRaw);
        $onuStats = $this->onuStatsParser->parse($onuStatsRaw);

        $onuData = array_map(function ($item) {
            $item['interface'] = strtolower($item['interface']);
            return $item;
        }, $onuData);

        $onuStats = array_map(function ($item) {
            $item['interface'] = strtolower($item['interface']);
            return $item;
        }, $onuStats);

        $combined = collect($onuData)->keyBy('interface')->map(function ($item) use ($onuStats) {
            $stat = collect($onuStats)->where('interface', $item['interface'])->first();
            return array_merge($item, $stat ?? []);
        })->values();

        // Применение фильтров
        if (!empty($filters)) {
            $combined = $combined->filter(function ($item) use ($filters) {
                foreach ($filters as $filter) {
                    $field = $filter['field'] ?? '';
                    $operator = $filter['operator'] ?? '=';
                    $value = $filter['value'] ?? '';

                    if (!isset($item[$field]) || $value === '') {
                        continue;
                    }

                    $itemValue = $item[$field];
                    $isNumericField = in_array($field, ['temperature', 'voltage', 'bias', 'rx_power', 'tx_power']);
                    if ($isNumericField) {
                        $itemValue = floatval($itemValue);
                        $value = floatval($value);
                    }

                    switch ($operator) {
                        case '=':
                            if ($itemValue != $value) return false;
                            break;
                        case '>':
                            if ($itemValue <= $value) return false;
                            break;
                        case '<':
                            if ($itemValue >= $value) return false;
                            break;
                        case '>=':
                            if ($itemValue < $value) return false;
                            break;
                        case '<=':
                            if ($itemValue > $value) return false;
                            break;
                        case 'contains':
                            if (stripos(strval($itemValue), $value) === false) return false;
                            break;
                        default:
                            return true;
                    }
                }
                return true;
            });
        }

        // Сортировка
        $combined = $combined->sortBy(function ($item) use ($sortField) {
            $value = $item[$sortField] ?? '';
            if ($sortField === 'interface') {
                preg_match('/gpon0\/1:(\d+)/', $value, $match);
                return isset($match[1]) ? (int)$match[1] : 0;
            }
            if (in_array($sortField, ['temperature', 'voltage', 'bias', 'rx_power', 'tx_power'])) {
                return floatval($value);
            }
            return $value;
        }, SORT_REGULAR, $sortDirection === 'desc');

        // Пагинация
        $total = $combined->count();
        $data = $combined->forPage($page, $perPage)->values()->all();

        return [
            'data' => $data,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'last_page' => ceil($total / $perPage)
        ];
    }
}
