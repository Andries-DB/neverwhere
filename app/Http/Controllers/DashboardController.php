<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Dashboard;
use App\Models\PinnedGraph;
use App\Models\PinnedItem;
use App\Models\PinnedTable;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;
use LDAP\Result;

class DashboardController extends Controller
{
    public function show($guid = null)
    {
        $user = auth()->user();
        $user->load('dashboards');

        $dashboard = Dashboard::query()
            ->when($guid, fn($q) => $q->where('guid', $guid))
            ->when(!$guid, fn($q) => $q->where('default', 1))
            ->where('user_id', $user->id)
            ->with([

                'pinned_items.message.conversation',
            ])
            ->first();

        // 2. Indien niet gevonden, maak een nieuwe aan
        if (!$dashboard) {
            $dashboard = Dashboard::create([
                'user_id' => $user->id,
                'name' => 'Pinboard ' . $user->firstname,
                'default' => 1,
                'guid' => Str::uuid(),
            ]);

            // Leeg laden zodat je geen fouten krijgt in frontend
            $dashboard->loadMissing([

                'pinned_items.message.conversation',

            ]);
        }

        return Inertia::render('Dashboard', [
            'dashboard' => $dashboard,

            'pinned_items' => $dashboard->pinned_items,
            'all_dashboards' => $user->dashboards
        ]);
    }

    public function create(Request $request)
    {

        $request->validate([
            'name' => 'required|max:255'
        ]);

        $user = auth()->user();

        Dashboard::create([
            'user_id' => $user->id,
            'name' => $request->name,
            'default' => 0,
            'guid' => Str::uuid(),
        ]);

        return redirect()->back();
    }

    public function updateItemOrder(Request $request)
    {
        foreach ($request->graphs as $graph) {
            $pinned_item = PinnedItem::find($graph['id']);
            $pinned_item->display_order = $graph['display_order'];
            $pinned_item->save();
        }

        return [
            'csrf_token' => csrf_token(),
        ];
    }

    public function makeDefault(Request $request)
    {
        $user = auth()->user();

        $dashboard = Dashboard::where('user_id', $user->id)
            ->where('default', 1)
            ->first();

        $dashboard->default = 0;
        $dashboard->save();

        $dashboard_to_make_default = Dashboard::find($request->dashboard['id']);
        $dashboard_to_make_default->default = 1;
        $dashboard_to_make_default->save();

        return redirect()->route('dashboard', $dashboard_to_make_default->guid);
    }

    public function delete(Request $request)
    {
        $user = auth()->user();

        $dashboard = Dashboard::where('user_id', $user->id)
            ->where('id', $request->dashboard['id'])
            ->firstOrFail();

        $isDefault = $dashboard->default;

        // Verwijder de dashboard
        $dashboard->delete();

        // Als het de default was, maak de eerste overblijvende dashboard default
        if ($isDefault) {
            $newDefault = Dashboard::where('user_id', $user->id)->first();

            if ($newDefault) {
                $newDefault->default = 1;
                $newDefault->save();

                return redirect()->route('dashboard', $newDefault->guid);
            } else {
                return redirect()->route('dashboard');
            }
        }

        // Als het geen default was → redirect naar huidige default dashboard
        $default = Dashboard::where('user_id', $user->id)
            ->where('default', 1)
            ->first();

        return redirect()->route('dashboard', $default?->guid ?? '');
    }

    public function export(Request $request, $guid)
    {
        $user = auth()->user();
        $dashboard = Dashboard::where('user_id', $user->id)
            ->where('guid', $guid)
            ->with('pinned_items.message.conversation')
            ->first();

        // Prepare chart data voor PDF
        $processedItems = $this->processItemsForPDF($dashboard->pinned_items);

        $filePath = public_path('storage/pdf/' . "dashboard-{$dashboard->name}-" . now()->format('Y-m-d') . ".pdf");

        // dd($processedItems);
        Pdf::loadView('dashboard.pdf', [
            'dashboard' => $dashboard,
            'items' => $processedItems
        ])
            // ->setPaper('a4', 'portrait')
            ->setOptions([
                'defaultFont' => 'sans-serif',
                'isRemoteEnabled' => true,
                'isHtml5ParserEnabled' => true,
            ])
            ->save($filePath);

        return [
            'success' => true,
            'csrf_token' => csrf_token(),
            'file_url' => asset('storage/pdf/' . "dashboard-{$dashboard->name}-" . now()->format('Y-m-d') . ".pdf"),
            'file_name' => "dashboard-{$dashboard->name}-" . now()->format('Y-m-d') . ".pdf"
        ];
    }

    private function processItemsForPDF($items)
    {
        $processedItems = [];

        foreach ($items as $item) {
            $processedItem = [
                'id' => $item->id,
                'title' => $item->title,
                'type' => $item->type,
                'width' => $item->width ?? 'half',
            ];

            if ($item->type === 'graph') {
                $processedItem['chart_data'] = $this->prepareChartDataForPDF($item);
            } elseif ($item->type === 'table') {
                $processedItem['table_data'] = $this->prepareTableDataForPDF($item);
            }

            $processedItems[] = $processedItem;
        }


        // dd($processedItems);
        return $processedItems;
    }

    private function prepareChartDataForPDF($item)
    {
        $json = $item->json ?? [];
        $xAxis = $item->_x;
        $yAxis = $item->_y;
        $aggregation = $item->_agg ?? 'sum';

        if (!$json || !$xAxis || !$yAxis) {
            return ['data' => [], 'max_value' => 0];
        }

        // Aggregeer data
        if ($yAxis === '__count') {
            $chartData = $this->aggregateCountData($json, $xAxis);
        } else {
            $chartData = $this->aggregateValueData($json, $xAxis, $yAxis, $aggregation);
        }

        // Sorteer en limiteer
        $chartData = collect($chartData)->sortByDesc('value')->take(10)->values()->toArray();
        $maxValue = collect($chartData)->max('value') ?: 100;


        return [
            'data' => $chartData,
            'max_value' => $maxValue,
            'chart_type' => $item->sort_chart ?? 'column',
            'x_label' => $this->formatFieldName($xAxis),
            'y_label' => $this->formatFieldName($yAxis),
        ];
    }

    private function aggregateCountData($data, $xAxis)
    {
        $counts = [];

        foreach ($data as $row) {
            $category = $row[$xAxis] ?? 'Onbekend';
            $category = is_string($category) ? $category : (string)$category;
            $counts[$category] = ($counts[$category] ?? 0) + 1;
        }

        $result = [];
        foreach ($counts as $category => $count) {
            $result[] = [
                'category' => $category,
                'value' => $count
            ];
        }

        return $result;
    }

    private function aggregateValueData($data, $xAxis, $yAxis, $aggregation)
    {
        $groups = [];

        foreach ($data as $row) {
            $category = $row[$xAxis] ?? 'Onbekend';
            $value = is_numeric($row[$yAxis] ?? 0) ? floatval($row[$yAxis]) : 0;

            if (!isset($groups[$category])) {
                $groups[$category] = [];
            }
            $groups[$category][] = $value;
        }

        $result = [];
        foreach ($groups as $category => $values) {
            $aggregatedValue = $this->aggregateValues($values, $aggregation);
            $result[] = [
                'category' => $category,
                'value' => $aggregatedValue
            ];
        }

        return $result;
    }

    private function aggregateValues($values, $aggregation)
    {
        switch ($aggregation) {
            case 'avg':
                return count($values) > 0 ? array_sum($values) / count($values) : 0;
            case 'count':
                return count($values);
            case 'min':
                return count($values) > 0 ? min($values) : 0;
            case 'max':
                return count($values) > 0 ? max($values) : 0;
            case 'sum':
            default:
                return array_sum($values);
        }
    }

    private function prepareTableDataForPDF($item)
    {
        $json = $item->json ?? [];

        if (empty($json)) {
            return ['headers' => [], 'rows' => []];
        }

        // Get headers
        $headers = [];
        if ($item->col_def) {
            $colDef = json_decode($item->col_def);

            $headers = collect($colDef['columnState'])
                ->filter(fn($col) => empty($col['hide']) || $col['hide'] === false)
                ->map(fn($col) => [
                    'id' => $col['colId'] ?? null,
                    'headerName' => $col['headerName'] ?? null,
                ])
                ->all();
        } else {
            $headers = collect(array_keys($json[0]))
                ->map(fn($key) => ['id' => $key, 'headerName' => $key])
                ->all();
        }


        // Limiteer rijen voor PDF (max 20)
        // $rows = array_slice($json, 0, 20);
        $totalRows = count($json);

        return [
            'headers' => $headers,
            'rows' => $json,
            'total_rows' => $totalRows,
        ];
    }

    private function formatFieldName($fieldName)
    {
        if ($fieldName === '__count') return 'Aantal';
        if ($fieldName === '__index') return 'Index';

        return ucfirst(str_replace('_', ' ', $fieldName));
    }
}
