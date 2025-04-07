<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Парсер ONU</title>
    <link href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        button { padding: 10px 20px; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .controls { margin-bottom: 20px; }
        select, input { margin-right: 10px; padding: 5px; }
    </style>
</head>
<body>
<button id="loadData">Загрузить данные</button>
<div class="controls">
    <label>Сортировать по:</label>
    <select id="sortField">
        <option value="interface">Интерфейс</option>
        <option value="vendor_id">Производитель</option>
        <option value="model_id">Модель</option>
        <option value="sn">Серийный номер</option>
        <option value="loid">LOID</option>
        <option value="status">Статус</option>
        <option value="config_status">Статус конфигурации</option>
        <option value="active_time">Время активности</option>
        <option value="temperature">Температура</option>
        <option value="voltage">Напряжение</option>
        <option value="bias">Смещение</option>
        <option value="tx_power">Мощность передачи</option>
        <option value="rx_power">Мощность приёма</option>
    </select>
    <select id="sortDirection">
        <option value="asc">По возрастанию</option>
        <option value="desc">По убыванию</option>
    </select>

    <label>Фильтр:</label>
    <select id="filterField">
        <option value="status">Статус</option>
        <option value="vendor_id">Производитель</option>
        <option value="temperature">Температура</option>
        <option value="voltage">Напряжение</option>
        <option value="bias">Смещение</option>
        <option value="tx_power">Мощность передачи</option>
        <option value="rx_power">Мощность приёма</option>
    </select>
    <select id="filterOperator">
        <option value="=">Равно</option>
        <option value=">">Больше</option>
        <option value=">=">Больше или равно</option>
        <option value="<">Меньше</option>
        <option value="<=">Меньше или равно</option>
        <option value="contains">Содержит</option>
    </select>
    <input type="text" id="filterValue" placeholder="Введите значение">
    <button id="applyFilter">Применить фильтр</button>
</div>

<table id="onuTable" class="display">
    <thead>
    <tr>
        <th>Интерфейс</th>
        <th>Производитель</th>
        <th>Модель</th>
        <th>Серийный номер</th>
        <th>LOID</th>
        <th>Статус</th>
        <th>Статус конфигурации</th>
        <th>Время активности</th>
        <th>Температура</th>
        <th>Напряжение</th>
        <th>Смещение</th>
        <th>Мощность передачи</th>
        <th>Мощность приёма</th>
    </tr>
    </thead>
    <tbody></tbody>
</table>

<script>
    $(document).ready(function() {
        let table = $('#onuTable').DataTable({
            "serverSide": true,
            "processing": true,
            "paging": true,
            "ordering": false,
            "searching": false,
            "pageLength": 100,
            "ajax": {
                "url": "/api/fetch-onu-data",
                "type": "GET",
                "data": function(d) {
                    let sortField = $('#sortField').val();
                    let sortDirection = $('#sortDirection').val();
                    let filterField = $('#filterField').val();
                    let filterOperator = $('#filterOperator').val();
                    let filterValue = $('#filterValue').val();

                    let filters = [];
                    if (filterValue) {
                        filters.push({
                            field: filterField,
                            operator: filterOperator,
                            value: filterValue
                        });
                    }

                    return {
                        sort_field: sortField,
                        sort_direction: sortDirection,
                        filters: JSON.stringify(filters),
                        page: (d.start / d.length) + 1,
                        per_page: d.length
                    };
                },
                "dataSrc": function(json) {
                    json.recordsTotal = json.total || 0;
                    json.recordsFiltered = json.total || 0;
                    return json.data;
                }
            },
            "columns": [
                { "data": "interface" },
                { "data": "vendor_id" },
                { "data": "model_id" },
                { "data": "sn" },
                { "data": "loid" },
                { "data": "status" },
                { "data": "config_status" },
                { "data": "active_time" },
                { "data": "temperature", "defaultContent": "" },
                { "data": "voltage", "defaultContent": "" },
                { "data": "bias", "defaultContent": "" },
                { "data": "tx_power", "defaultContent": "" },
                { "data": "rx_power", "defaultContent": "" }
            ],
            "language": {
                "lengthMenu": "Показать _MENU_ записей на странице",
                "zeroRecords": "Ничего не найдено",
                "info": "Страница _PAGE_ из _PAGES_ (всего _TOTAL_ записей)",
                "infoEmpty": "Нет записей",
                "infoFiltered": "(отфильтровано из _MAX_ записей)",
                "paginate": {
                    "first": "Первая",
                    "last": "Последняя",
                    "next": "Следующая",
                    "previous": "Предыдущая"
                }
            },
            "initComplete": function() {
                $('#onuTable').show();
            }
        });

        table.ajax.reload();

        function reloadTable() {
            table.ajax.reload();
            $('#onuTable').show();
        }

        $('#loadData').click(reloadTable);
        $('#applyFilter').click(reloadTable);
        $('#sortField, #sortDirection, #filterField, #filterOperator').change(reloadTable);
    });
</script>
</body>
</html>
