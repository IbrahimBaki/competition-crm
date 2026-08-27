<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $locale === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <title>Report Export</title>
    <style>
        * {
            margin: 0;
            padding: 0;
        }
        body {
            font-family: Arial, sans-serif;
            direction: {{ $locale === 'ar' ? 'rtl' : 'ltr' }};
            line-height: 1.6;
            color: #333;
        }
        .container {
            padding: 20px;
        }
        h1 {
            margin-bottom: 20px;
            font-size: 24px;
        }
        .metadata {
            font-size: 12px;
            color: #666;
            margin-bottom: 20px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: {{ $locale === 'ar' ? 'right' : 'left' }};
        }
        th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .totals {
            margin-top: 20px;
            padding: 10px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>{{ $result->generatedAt->format('Y-m-d H:i') }}</h1>

        <div class="metadata">
            <p>{{ __('Timezone') }}: {{ $result->timezone }}</p>
            <p>{{ __('Generated') }}: {{ $result->generatedAt->toIso8601String() }}</p>
        </div>

        <table>
            <thead>
                <tr>
                    @foreach ($definition->columns() as $column)
                        <th>{{ $column }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($result->rows as $row)
                    <tr>
                        @foreach ($definition->columns() as $column)
                            <td>{{ $row[$column] ?? '' }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals">
            <h3>Totals</h3>
            <ul>
                @foreach ($result->totals as $key => $value)
                    <li><strong>{{ $key }}:</strong> {{ $value }}</li>
                @endforeach
            </ul>
        </div>
    </div>
</body>
</html>
