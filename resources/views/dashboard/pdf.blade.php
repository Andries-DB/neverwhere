<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Dashboard Export - {{ $dashboard->name }}</title>
    <style>
        @page {
            margin: 15mm;
            size: A4 landscape;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .header {
            border-bottom: 2px solid #3B82F6;
            margin-bottom: 20px;
            padding-bottom: 10px;
        }

        .header h1 {
            margin: 0 0 5px 0;
            font-size: 24px;
            color: #1F2937;
        }

        .header .meta {
            font-size: 11px;
            color: #6B7280;
        }

        .item-card {
            border: 1px solid #E5E7EB;
            border-radius: 8px;
            background: white;
            page-break-inside: avoid;
            margin-bottom: 25px;
            overflow: hidden;
        }

        .item-header {
            background: #F9FAFB;
            padding: 5px 8px;
            border-bottom: 1px solid #E5E7EB;
        }

        .item-title {
            font-size: 16px;
            font-weight: 600;
            margin: 0 0 4px 0;
            color: #1F2937;
        }

        .item-subtitle {
            font-size: 11px;
            color: #6B7280;
            margin: 0;
        }

        .item-content {
            padding: 20px;
        }

        /* VEEL BETERE Chart Styles */
        .chart-container {
            width: 100%;
            /* height: 320px; */
            position: relative;
            background: white;
        }


        .chart-line {
            background:  #3B82F6; /* lichte achtergrond */
        }
        .chart-wrapper {
            position: relative;
            height: 220px;
            margin: 10px 10px 40px 40px;
        }

        /* Verbeterde Y-axis */
        .chart-y-axis {
            position: absolute;
            left: -70px;
            top: -10px;
            height: 110%;
            width: 60px;
        }

        .chart-y-axis::after {
            content: '';
            position: absolute;
            right: 0;
            top: 10px;
            bottom: 10px;
            width: 2px;
            background: #374151;
        }

        .y-axis-label {
            position: absolute;
            font-size: 10px;
            color: #374151;
            right: 5px;
            transform: translateY(-50%);
            font-weight: 500;
            background: white;
            padding: 0 3px;
        }

        /* Verbeterde X-axis */
        .chart-x-axis {
            position: absolute;
            bottom: -50px;
            left: -5px;
            right: -5px;
            height: 2px;
            background: #374151;
        }

        /* Betere Bars */
        .chart-bars {
            width: 100%;
            display: flex;
            flex-direction: row;
            flex-wrap: nowrap;
            align-items: flex-end;
            gap: 8px;
            /* padding: 10px 0; */
            position: relative;
              bottom: -50px;
        }

        .chart-bar {
           
            position: relative;
            display: inline-flex;
            flex-direction: column;
            align-items: center;
        }

        .chart-bar-fill {
            background-color: #3B82F6;
            width: 85%;
            border-radius: 4px 4px 0 0;
            position: relative;
            box-shadow: 0 2px 6px rgba(59, 130, 246, 0.4);
            border: 1px solid rgba(59, 130, 246, 0.6);
        }

        .chart-bar-value {
            position: absolute;
            top: -22px;
            left: 45%;
            transform: translateX(-50%);
            font-size: 9px;
            font-weight: 600;
            color: #1F2937;
            background: #F8FAFC;
            padding: 2px 5px;
            border-radius: 3px;
            white-space: nowrap;
            border: 1px solid #E2E8F0;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .chart-bar-label {
            position: absolute;
            top: 12px;
            left: 45%;
            transform: translateX(-50%);
            font-size: 8px;
            color: #4A5568;
            font-weight: 500;
            text-align: center;
            line-height: 1.1;
            max-width: 80px;
            word-wrap: break-word;
            hyphens: auto;
        }

        .chart-axis-labels {
            position: absolute;
            bottom: -80px;
            left: 50%;
            transform: translateX(-50%);
            text-align: center;
        }

        .axis-labels-row {
            font-size: 11px;
            color: #6B7280;
            font-weight: 500;
        }

        .axis-arrow {
            font-size: 14px;
            color: #9CA3AF;
            margin: 5px 15px;
        }

        /* VEEL BETERE Table Styles */
        .table-container {
            overflow: hidden;
        }

        .pdf-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
            margin-bottom: 12px;
            table-layout: fixed;
        }

        .pdf-table th {
            background: linear-gradient(135deg, #F8FAFC 0%, #E2E8F0 100%);
            border: 1px solid #CBD5E0;
            padding: 4px 3px;
            text-align: left;
            font-weight: 600;
            color: #2D3748;
            font-size: 9px;
            position: sticky;
            top: 0;
        }

        .pdf-table td {
            border: 1px solid #E2E8F0;
            padding: 6px;
            color: #2D3748;
            font-size: 4px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .pdf-table tr:nth-child(even) {
            background: #F7FAFC;
        }

        .pdf-table tr:hover {
            background: #EDF2F7;
        }

       

        .table-meta {
            font-size: 10px;
            color: #6B7280;
            margin-top: 8px;
            text-align: right;
            font-style: italic;
        }

        .no-data {
            text-align: center;
            color: #9CA3AF;
            padding: 50px 0;
            font-style: italic;
            font-size: 14px;
        }

        .page-break {
            
            page-break-before: always;
        }

        /* Line Chart Styles */
        .chart-line-container {
            position: relative;
            height: 200px;
            margin-bottom: 40px;
        }

        .chart-line-svg {
            border-radius: 6px;
            background-color: red;
        }

        .line-chart-labels {
            position: absolute;
            bottom: -25px;
            left: 0;
            right: 0;
            height: 20px;
        }

        .line-label {
            position: absolute;
            transform: translateX(-50%);
            font-size: 8px;
            color: #4A5568;
            font-weight: 500;
            text-align: center;
        }

       

        /* Geld formatting */
        .currency {
            font-weight: 600;
            color: #059669;
        }

        .currency.zero {
            color: #6B7280;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $dashboard->name }}</h1>
        <div class="meta">
            Geëxporteerd op {{ now()->format('d-m-Y') }} om {{ now()->format('H:i') }} • 
            {{ count($items) }} items
        </div>
    </div>

    @if(count($items) > 0)
        @foreach($items as $index => $item)
            @if($index > 0 && $index % 2 === 0)
                <div class="page-break"></div>
            @endif

            <div class="item-card">
                <div class="item-header">
                    <h3 class="item-title">
                        {{ $item['title'] ?: 'Item ' . ($index + 1) }}
                    </h3>
                    <p class="item-subtitle">
                        @if($item['type'] === 'graph')
                            {{ ucfirst($item['chart_data']['chart_type'] ?? 'bar') }}diagram • 
                            {{ count($item['chart_data']['data']) }} categorieën
                        @else
                            Tabel • {{ $item['table_data']['total_rows'] ?? 0 }} rijen
                        @endif
                    </p>
                </div>

                <div class="item-content">
                    @if($item['type'] === 'graph' && !empty($item['chart_data']['data']))
                        <div class="chart-container">
                            <div class="chart-wrapper">
                             

                                <!-- Y-axis met betere spacing -->
                                <div class="chart-y-axis">
                                    @php
                                        $maxValue = $item['chart_data']['max_value'];
                                        $steps = 5;
                                    @endphp
                                    @for($i = 0; $i < $steps; $i++)
                                        @php
                                            $value = $maxValue - (($maxValue / ($steps - 1)) * $i);
                                            $percentage = ($i / ($steps - 1)) * 100;
                                        @endphp
                                        <div class="y-axis-label" style="top: {{ $percentage }}%;">
                                            {{ number_format($value, 0, ',', '.') }}
                                        </div>
                                    @endfor
                                </div>

                                   {{-- @if($item['chart_data']['chart_type'] === 'bar') --}}

                                @php
                                    $barCount = count($item['chart_data']['data']);
                                    $barWidth = $barCount > 0 ? (100 / $barCount) : 100;
                                    $barWidth -= 2;
                                    
                                @endphp

                                <!-- Bars met betere labels -->
                                <div class="chart-bars">
                                    @foreach($item['chart_data']['data'] as $dataPoint)
                                        @php
                                            $height = $dataPoint['value'] == 0 
                                                ? 0 
                                                : ($dataPoint['value'] / $maxValue) * 100;
                                            
                                            // Intelligente label afkorting
                                            $label = $dataPoint['category'];
                                            if (strlen($label) > 10) {
                                                $words = explode(' ', $label);
                                                if (count($words) > 1) {
                                                    $label = substr($words[0], 0, 8) . (strlen($words[0]) > 8 ? '...' : '') . ' ' . 
                                                            substr(end($words), 0, 6) . (strlen(end($words)) > 6 ? '...' : '');
                                                } else {
                                                    $label = substr($label, 0, 10) . '...';
                                                }
                                            }
                                        @endphp
                                        <div class="chart-bar" style="width: {{ $barWidth }}%">
                                            <div class="chart-bar-fill" style="height: {{ $height }}%">
                                                <div class="chart-bar-value">
                                                    {{ number_format($dataPoint['value'], 0, ',', '.') }}
                                                </div>
                                                
                                            </div>
                                            <div class="chart-bar-label">
                                                {{ $label }} 

                                               
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

{{-- 
                                  @elseif($item['chart_data']['chart_type'] === 'line')
                                    <!-- Line Chart -->
                                    <div class="chart-line-container">
                                        @php
                                            $points = [];
                                            $barCount = count($item['chart_data']['data']);
                                        @endphp
                                        <svg class="chart-line-svg" width="100%" height="200" viewBox="0 0 400 200" preserveAspectRatio="xMidYMid meet">
                                            <!-- Grid lines -->
                                            @for($i = 1; $i < 5; $i++)
                                                <line x1="0" y1="{{ $i * 40 }}" x2="400" y2="{{ $i * 40 }}" 
                                                      stroke="#E5E7EB" stroke-width="1" opacity="0.5"/>
                                            @endfor
                                            
                                            <!-- Line path -->
                                            @foreach($item['chart_data']['data'] as $index => $dataPoint)
                                                @php
                                                    $x = $barCount > 1 ? ($index / ($barCount - 1)) * 380 + 10 : 200;
                                                    $y = $maxValue > 0 ? 190 - (($dataPoint['value'] / $maxValue) * 180) : 190;
                                                    $points[] = $x . ',' . $y;
                                                @endphp
                                            @endforeach
                                            
                                            <polyline 
                                                fill="none" 
                                                stroke="#3B82F6" 
                                                stroke-width="3" 
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                points="{{ implode(' ', $points) }}" />
                                            
                                            <!-- Data points -->
                                            @foreach($item['chart_data']['data'] as $index => $dataPoint)
                                                @php
                                                    $x = $barCount > 1 ? ($index / ($barCount - 1)) * 380 + 10 : 200;
                                                    $y = $maxValue > 0 ? 190 - (($dataPoint['value'] / $maxValue) * 180) : 190;
                                                @endphp
                                                <circle cx="{{ $x }}" cy="{{ $y }}" r="4" fill="#1E40AF" stroke="white" stroke-width="2" />
                                                
                                                <!-- Value labels -->
                                                <text x="{{ $x }}" y="{{ $y - 12 }}" 
                                                      text-anchor="middle" 
                                                      font-size="9" 
                                                      font-weight="600" 
                                                      fill="#374151">
                                                    {{ number_format($dataPoint['value'], 0, ',', '.') }}
                                                </text>
                                            @endforeach
                                        </svg>
                                        
                                        <!-- X-axis labels voor line chart -->
                                        <div class="line-chart-labels">
                                            @foreach($item['chart_data']['data'] as $index => $dataPoint)
                                                @php
                                                    $label = $dataPoint['category'];
                                                    if (strlen($label) > 8) {
                                                        $label = substr($label, 0, 8) . '...';
                                                    }
                                                    $position = $barCount > 1 ? ($index / ($barCount - 1)) * 100 : 50;
                                                @endphp
                                                <span class="line-label" style="left: {{ $position }}%;">{{ $label }}</span>
                                            @endforeach
                                        </div>
                                    </div> --}}
                                {{-- @endif --}}
                                <div class="chart-x-axis"></div>
                              

                            </div>

                            <div class="chart-axis-labels">
                                <div class="axis-labels-row">
                                    <span>{{ $item['chart_data']['x_label'] ?? 'Categorie' }}</span>
                                    <span class="axis-arrow">▶</span>
                                    <span>{{ $item['chart_data']['y_label'] ?? 'Waarde' }}</span>
                                </div>
                            </div>
                        </div>
                    @elseif($item['type'] === 'table' && !empty($item['table_data']['rows']))
                        <div class="table-container">
                            <table class="pdf-table">
                                <thead>
                                    <tr>
                                        @foreach($item['table_data']['headers'] as $header)
                                            <th>{{ $header['headerName'] }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($item['table_data']['rows'] as $row)
                                        <tr>
                                            @foreach($item['table_data']['headers'] as $header)
                                                <td>
                                                    @php
                                                        $value = $row[$header['id']] ?? '-';
                                                        $headerId = $header['id'];
                                                        
                                                        if ($headerId === 'bedrag' && is_numeric($value)) {
                                                            $class = $value == 0 ? 'currency zero' : 'currency';
                                                            echo '<span class="' . $class . '">€ ' . number_format($value, 2, ',', '.') . '</span>';
                                                        } elseif (in_array($headerId, ['prijs', 'aantal']) && is_numeric($value)) {
                                                            echo number_format($value, 2, ',', '.');
                                                        } elseif ($headerId === 'datum' && $value !== '-') {
                                                            echo date('d-m-Y', strtotime($value));
                                                        } else {
                                                            $maxLength = $headerId === 'omschrijving' ? 45 : ($headerId === 'klant' ? 25 : 20);
                                                            echo strlen($value) > $maxLength ? substr($value, 0, $maxLength) . '...' : $value;
                                                        }
                                                    @endphp
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            
                            <div class="table-meta">
                                {{ $item['table_data']['total_rows'] }} rijen weergegeven
                            </div>
                        </div>
                    @else
                        <div class="no-data">
                            Geen data beschikbaar voor dit item
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    @else
        <div class="no-data">
            <p>Geen items gevonden om te exporteren</p>
        </div>
    @endif
</body>
</html>