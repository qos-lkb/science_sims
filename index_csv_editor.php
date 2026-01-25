<?php
/**
 * CSV Viewer/Editor
 * 用於查看和編輯 index.csv 的管理介面
 */

// CSV 檔案路徑
define('CSV_FILE', 'index.csv');
define('CSV_BACKUP', 'index.csv.bak');

// CSV 欄位定義
$csvHeaders = ['Category', 'Title', 'URL', 'Screenshot', 'Category_ZH', 'Category_EN', 'Title_ZH', 'Title_EN', 'Last_Updated'];

// 處理 AJAX 請求
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json; charset=utf-8');
    
    switch ($_POST['action']) {
        case 'save':
            echo json_encode(saveRow($_POST));
            break;
        case 'delete':
            echo json_encode(deleteRow($_POST['rowIndex']));
            break;
        case 'add':
            echo json_encode(addRow($_POST));
            break;
        case 'reorder':
            echo json_encode(reorderRows($_POST['order']));
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Unknown action']);
    }
    exit;
}

// 匯出 CSV
if (isset($_GET['export'])) {
    exportCSV();
    exit;
}

/**
 * 讀取 CSV 檔案
 */
function readCSV() {
    global $csvHeaders;
    $data = [];
    
    if (!file_exists(CSV_FILE)) {
        return $data;
    }
    
    if (($handle = fopen(CSV_FILE, "r")) !== FALSE) {
        $header = fgetcsv($handle); // 跳過標題行
        $rowIndex = 0;
        while (($row = fgetcsv($handle)) !== FALSE) {
            if (count($row) >= 3) {
                $data[] = [
                    'rowIndex' => $rowIndex,
                    'Category' => $row[0] ?? '',
                    'Title' => $row[1] ?? '',
                    'URL' => $row[2] ?? '',
                    'Screenshot' => $row[3] ?? '',
                    'Category_ZH' => $row[4] ?? $row[0],
                    'Category_EN' => $row[5] ?? $row[0],
                    'Title_ZH' => $row[6] ?? $row[1],
                    'Title_EN' => $row[7] ?? $row[1],
                    'Last_Updated' => $row[8] ?? date('Y-m-d')
                ];
                $rowIndex++;
            }
        }
        fclose($handle);
    }
    return $data;
}

/**
 * 寫入 CSV 檔案
 */
function writeCSV($data) {
    global $csvHeaders;
    
    // 建立備份
    if (file_exists(CSV_FILE)) {
        copy(CSV_FILE, CSV_BACKUP);
    }
    
    if (($handle = fopen(CSV_FILE, "w")) !== FALSE) {
        // 寫入標題行
        fputcsv($handle, $csvHeaders);
        
        // 寫入資料行
        foreach ($data as $row) {
            fputcsv($handle, [
                $row['Category'],
                $row['Title'],
                $row['URL'],
                $row['Screenshot'],
                $row['Category_ZH'],
                $row['Category_EN'],
                $row['Title_ZH'],
                $row['Title_EN'],
                $row['Last_Updated']
            ]);
        }
        fclose($handle);
        return true;
    }
    return false;
}

/**
 * 儲存（更新）一筆資料
 */
function saveRow($postData) {
    $data = readCSV();
    $rowIndex = intval($postData['rowIndex']);
    
    if ($rowIndex < 0 || $rowIndex >= count($data)) {
        return ['success' => false, 'message' => '找不到該筆資料'];
    }
    
    $data[$rowIndex] = [
        'rowIndex' => $rowIndex,
        'Category' => trim($postData['Category'] ?? ''),
        'Title' => trim($postData['Title'] ?? ''),
        'URL' => trim($postData['URL'] ?? ''),
        'Screenshot' => trim($postData['Screenshot'] ?? ''),
        'Category_ZH' => trim($postData['Category_ZH'] ?? ''),
        'Category_EN' => trim($postData['Category_EN'] ?? ''),
        'Title_ZH' => trim($postData['Title_ZH'] ?? ''),
        'Title_EN' => trim($postData['Title_EN'] ?? ''),
        'Last_Updated' => trim($postData['Last_Updated'] ?? date('Y-m-d'))
    ];
    
    if (writeCSV($data)) {
        return ['success' => true, 'message' => '儲存成功'];
    }
    return ['success' => false, 'message' => '儲存失敗'];
}

/**
 * 刪除一筆資料
 */
function deleteRow($rowIndex) {
    $data = readCSV();
    $rowIndex = intval($rowIndex);
    
    if ($rowIndex < 0 || $rowIndex >= count($data)) {
        return ['success' => false, 'message' => '找不到該筆資料'];
    }
    
    array_splice($data, $rowIndex, 1);
    
    // 重新編號
    foreach ($data as $i => &$row) {
        $row['rowIndex'] = $i;
    }
    
    if (writeCSV($data)) {
        return ['success' => true, 'message' => '刪除成功'];
    }
    return ['success' => false, 'message' => '刪除失敗'];
}

/**
 * 新增一筆資料
 */
function addRow($postData) {
    $data = readCSV();
    
    $newRow = [
        'rowIndex' => count($data),
        'Category' => trim($postData['Category'] ?? ''),
        'Title' => trim($postData['Title'] ?? ''),
        'URL' => trim($postData['URL'] ?? ''),
        'Screenshot' => trim($postData['Screenshot'] ?? ''),
        'Category_ZH' => trim($postData['Category_ZH'] ?? ''),
        'Category_EN' => trim($postData['Category_EN'] ?? ''),
        'Title_ZH' => trim($postData['Title_ZH'] ?? ''),
        'Title_EN' => trim($postData['Title_EN'] ?? ''),
        'Last_Updated' => trim($postData['Last_Updated'] ?? date('Y-m-d'))
    ];
    
    $data[] = $newRow;
    
    if (writeCSV($data)) {
        return ['success' => true, 'message' => '新增成功', 'rowIndex' => $newRow['rowIndex']];
    }
    return ['success' => false, 'message' => '新增失敗'];
}

/**
 * 重新排序資料
 */
function reorderRows($orderJson) {
    $order = json_decode($orderJson, true);
    
    if (!is_array($order)) {
        return ['success' => false, 'message' => '無效的排序資料'];
    }
    
    $data = readCSV();
    $newData = [];
    
    // 根據新的順序重新排列資料
    foreach ($order as $oldIndex) {
        if (isset($data[$oldIndex])) {
            $newData[] = $data[$oldIndex];
        }
    }
    
    // 確保所有資料都被保留
    if (count($newData) !== count($data)) {
        return ['success' => false, 'message' => '排序資料不完整'];
    }
    
    // 重新編號
    foreach ($newData as $i => &$row) {
        $row['rowIndex'] = $i;
    }
    
    if (writeCSV($newData)) {
        return ['success' => true, 'message' => '排序成功'];
    }
    return ['success' => false, 'message' => '排序失敗'];
}

/**
 * 匯出 CSV
 */
function exportCSV() {
    global $csvHeaders;
    
    $filename = 'index_export_' . date('Ymd_His') . '.csv';
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    // UTF-8 BOM for Excel
    echo "\xEF\xBB\xBF";
    
    $output = fopen('php://output', 'w');
    fputcsv($output, $csvHeaders);
    
    $data = readCSV();
    foreach ($data as $row) {
        fputcsv($output, [
            $row['Category'],
            $row['Title'],
            $row['URL'],
            $row['Screenshot'],
            $row['Category_ZH'],
            $row['Category_EN'],
            $row['Title_ZH'],
            $row['Title_EN'],
            $row['Last_Updated']
        ]);
    }
    
    fclose($output);
}

// 讀取資料
$csvData = readCSV();

// 取得所有類別
$categories = array_unique(array_column($csvData, 'Category'));
sort($categories);
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CSV Viewer/Editor | 資料管理</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* 自定義捲軸 */
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: #1e293b; }
        ::-webkit-scrollbar-thumb { background: #475569; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #64748b; }
        
        /* 表格樣式 */
        .table-container {
            overflow-x: auto;
            max-height: calc(100vh - 280px);
        }
        
        table {
            border-collapse: collapse;
            width: 100%;
            min-width: 1200px;
        }
        
        th {
            position: sticky;
            top: 0;
            z-index: 10;
            background: #1e293b;
            cursor: pointer;
            user-select: none;
            white-space: nowrap;
        }
        
        th:hover {
            background: #334155;
        }
        
        th .sort-icon {
            opacity: 0.3;
            transition: opacity 0.2s;
        }
        
        th.sorted .sort-icon {
            opacity: 1;
        }
        
        tr:hover td {
            background: #334155;
        }
        
        td {
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        td.url-cell {
            max-width: 250px;
        }
        
        /* Modal 樣式 */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.75);
            backdrop-filter: blur(4px);
            z-index: 100;
            align-items: center;
            justify-content: center;
        }
        
        .modal.active {
            display: flex;
        }
        
        .modal-content {
            background: #1e293b;
            border-radius: 12px;
            max-width: 700px;
            width: 95%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3);
        }
        
        /* Toast 樣式 */
        .toast {
            position: fixed;
            bottom: 20px;
            right: 20px;
            padding: 12px 24px;
            border-radius: 8px;
            color: white;
            font-weight: 500;
            z-index: 200;
            transform: translateY(100px);
            opacity: 0;
            transition: all 0.3s ease;
        }
        
        .toast.show {
            transform: translateY(0);
            opacity: 1;
        }
        
        .toast.success {
            background: #10b981;
        }
        
        .toast.error {
            background: #ef4444;
        }
        
        /* 輸入框樣式 */
        input[type="text"], input[type="date"], select {
            background: #0f172a;
            border: 1px solid #334155;
            color: #e2e8f0;
            padding: 8px 12px;
            border-radius: 6px;
            width: 100%;
            transition: border-color 0.2s;
        }
        
        input[type="text"]:focus, input[type="date"]:focus, select:focus {
            outline: none;
            border-color: #6366f1;
        }
        
        /* 載入中 */
        .loading {
            pointer-events: none;
            opacity: 0.6;
        }
        
        .spinner {
            border: 3px solid #334155;
            border-top: 3px solid #6366f1;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            animation: spin 1s linear infinite;
            display: inline-block;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* 拖放樣式 */
        .drag-handle {
            cursor: grab;
            padding: 4px;
            color: #64748b;
            transition: color 0.2s;
        }
        
        .drag-handle:hover {
            color: #94a3b8;
        }
        
        .drag-handle:active {
            cursor: grabbing;
        }
        
        tr.dragging {
            opacity: 0.5;
            background: #334155 !important;
        }
        
        tr.drag-over {
            border-top: 2px solid #6366f1 !important;
        }
        
        tr.drag-over-bottom {
            border-bottom: 2px solid #6366f1 !important;
        }
        
        .drag-indicator {
            position: fixed;
            background: #1e293b;
            border: 1px solid #6366f1;
            border-radius: 8px;
            padding: 8px 16px;
            color: #e2e8f0;
            font-size: 14px;
            pointer-events: none;
            z-index: 1000;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3);
        }
    </style>
</head>
<body class="bg-slate-900 text-slate-200 min-h-screen">
    <!-- 標題列 -->
    <header class="bg-indigo-900 text-white shadow-md sticky top-0 z-50">
        <div class="max-w-[1600px] mx-auto px-4">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center gap-3">
                    <a href="index.php" class="p-2 rounded-md hover:bg-indigo-800 transition-colors" title="返回主頁">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                    </a>
                    <div class="flex items-center gap-2">
                        <svg class="w-7 h-7 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <span class="font-bold text-lg md:text-xl tracking-tight" id="app-title" data-zh="CSV 資料管理" data-en="CSV Data Manager">CSV 資料管理</span>
                    </div>
                </div>
                
                <div class="flex items-center gap-3">
                    <span class="text-sm text-indigo-300" id="record-count" data-zh="共 <?php echo count($csvData); ?> 筆資料" data-en="<?php echo count($csvData); ?> records">共 <?php echo count($csvData); ?> 筆資料</span>
                    <button onclick="toggleLang()" class="px-3 py-1 md:px-4 md:py-1.5 rounded-full border border-indigo-400 hover:bg-white hover:text-indigo-900 transition-all text-xs md:text-sm font-medium">
                        中 / EN
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- 工具列 -->
    <div class="bg-slate-800 border-b border-slate-700 sticky top-16 z-40">
        <div class="max-w-[1600px] mx-auto px-4 py-3">
            <div class="flex flex-wrap items-center gap-3">
                <!-- 搜尋框 -->
                <div class="relative flex-1 min-w-[200px] max-w-md">
                    <input type="text" id="search-input" placeholder="搜尋..." class="pl-10 pr-4 py-2 w-full" data-placeholder-zh="搜尋..." data-placeholder-en="Search...">
                    <svg class="w-5 h-5 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                
                <!-- 類別篩選 -->
                <select id="category-filter" class="py-2 px-3 min-w-[150px]">
                    <option value="" data-zh="所有類別" data-en="All Categories">所有類別</option>
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo htmlspecialchars($cat); ?>"><?php echo htmlspecialchars($cat); ?></option>
                    <?php endforeach; ?>
                </select>
                
                <!-- 顯示筆數 -->
                <span class="text-sm text-slate-400" id="filtered-count"></span>
                
                <div class="flex-1"></div>
                
                <!-- 新增按鈕 -->
                <button onclick="openAddModal()" class="flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-500 rounded-lg transition-colors text-sm font-medium">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span data-zh="新增" data-en="Add">新增</span>
                </button>
                
                <!-- 匯出按鈕 -->
                <a href="?export=1" class="flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-500 rounded-lg transition-colors text-sm font-medium">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <span data-zh="匯出 CSV" data-en="Export CSV">匯出 CSV</span>
                </a>
            </div>
        </div>
    </div>

    <!-- 主要內容區 -->
    <main class="max-w-[1600px] mx-auto px-4 py-4">
        <div class="table-container bg-slate-800 rounded-xl border border-slate-700">
            <table id="data-table">
                <thead>
                    <tr class="text-left text-sm text-slate-300">
                        <th class="px-2 py-3 font-semibold w-10" title="拖放排序"></th>
                        <th class="px-3 py-3 font-semibold" data-sort="rowIndex">#</th>
                        <th class="px-3 py-3 font-semibold" data-sort="Category">
                            <div class="flex items-center gap-1">
                                <span data-zh="類別" data-en="Category">類別</span>
                                <svg class="w-4 h-4 sort-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                                </svg>
                            </div>
                        </th>
                        <th class="px-3 py-3 font-semibold" data-sort="Title">
                            <div class="flex items-center gap-1">
                                <span data-zh="標題" data-en="Title">標題</span>
                                <svg class="w-4 h-4 sort-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                                </svg>
                            </div>
                        </th>
                        <th class="px-3 py-3 font-semibold" data-sort="URL">
                            <div class="flex items-center gap-1">
                                <span>URL</span>
                                <svg class="w-4 h-4 sort-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                                </svg>
                            </div>
                        </th>
                        <th class="px-3 py-3 font-semibold" data-sort="Title_ZH">
                            <div class="flex items-center gap-1">
                                <span data-zh="標題（中）" data-en="Title (ZH)">標題（中）</span>
                                <svg class="w-4 h-4 sort-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                                </svg>
                            </div>
                        </th>
                        <th class="px-3 py-3 font-semibold" data-sort="Title_EN">
                            <div class="flex items-center gap-1">
                                <span data-zh="標題（英）" data-en="Title (EN)">標題（英）</span>
                                <svg class="w-4 h-4 sort-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                                </svg>
                            </div>
                        </th>
                        <th class="px-3 py-3 font-semibold" data-sort="Last_Updated">
                            <div class="flex items-center gap-1">
                                <span data-zh="更新日期" data-en="Updated">更新日期</span>
                                <svg class="w-4 h-4 sort-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                                </svg>
                            </div>
                        </th>
                        <th class="px-3 py-3 font-semibold text-center" data-zh="操作" data-en="Actions">操作</th>
                    </tr>
                </thead>
                <tbody id="table-body">
                    <?php foreach ($csvData as $row): ?>
                    <tr class="border-t border-slate-700 text-sm" data-row='<?php echo htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8'); ?>' data-row-index="<?php echo $row['rowIndex']; ?>" draggable="true">
                        <td class="px-2 py-2">
                            <div class="drag-handle" title="拖放排序">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"></path>
                                </svg>
                            </div>
                        </td>
                        <td class="px-3 py-2 text-slate-400"><?php echo $row['rowIndex'] + 1; ?></td>
                        <td class="px-3 py-2"><?php echo htmlspecialchars($row['Category']); ?></td>
                        <td class="px-3 py-2" title="<?php echo htmlspecialchars($row['Title']); ?>"><?php echo htmlspecialchars($row['Title']); ?></td>
                        <td class="px-3 py-2 url-cell">
                            <a href="<?php echo htmlspecialchars($row['URL']); ?>" target="_blank" class="text-indigo-400 hover:text-indigo-300" title="<?php echo htmlspecialchars($row['URL']); ?>">
                                <?php echo htmlspecialchars($row['URL']); ?>
                            </a>
                        </td>
                        <td class="px-3 py-2" title="<?php echo htmlspecialchars($row['Title_ZH']); ?>"><?php echo htmlspecialchars($row['Title_ZH']); ?></td>
                        <td class="px-3 py-2" title="<?php echo htmlspecialchars($row['Title_EN']); ?>"><?php echo htmlspecialchars($row['Title_EN']); ?></td>
                        <td class="px-3 py-2 text-slate-400"><?php echo htmlspecialchars($row['Last_Updated']); ?></td>
                        <td class="px-3 py-2">
                            <div class="flex items-center justify-center gap-2">
                                <button onclick="openEditModal(this.closest('tr'))" class="p-1.5 rounded hover:bg-slate-600 text-indigo-400 hover:text-indigo-300 transition-colors" title="編輯">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </button>
                                <button onclick="confirmDelete(this.closest('tr'))" class="p-1.5 rounded hover:bg-slate-600 text-red-400 hover:text-red-300 transition-colors" title="刪除">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>

    <!-- 編輯 Modal -->
    <div id="edit-modal" class="modal" onclick="if(event.target === this) closeModal()">
        <div class="modal-content">
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-700">
                <h2 class="text-lg font-bold" id="modal-title" data-zh="編輯資料" data-en="Edit Record">編輯資料</h2>
                <button onclick="closeModal()" class="p-2 rounded-lg hover:bg-slate-700 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <form id="edit-form" class="p-6 space-y-4">
                <input type="hidden" id="edit-rowIndex" name="rowIndex">
                <input type="hidden" name="action" id="form-action" value="save">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-400 mb-1" data-zh="類別" data-en="Category">類別</label>
                        <input type="text" id="edit-Category" name="Category" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-400 mb-1" data-zh="標題" data-en="Title">標題</label>
                        <input type="text" id="edit-Title" name="Title" required>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-1">URL</label>
                    <input type="text" id="edit-URL" name="URL" required>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-1" data-zh="截圖路徑" data-en="Screenshot Path">截圖路徑</label>
                    <input type="text" id="edit-Screenshot" name="Screenshot">
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-400 mb-1" data-zh="類別（中文）" data-en="Category (ZH)">類別（中文）</label>
                        <input type="text" id="edit-Category_ZH" name="Category_ZH">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-400 mb-1" data-zh="類別（英文）" data-en="Category (EN)">類別（英文）</label>
                        <input type="text" id="edit-Category_EN" name="Category_EN">
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-400 mb-1" data-zh="標題（中文）" data-en="Title (ZH)">標題（中文）</label>
                        <input type="text" id="edit-Title_ZH" name="Title_ZH">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-400 mb-1" data-zh="標題（英文）" data-en="Title (EN)">標題（英文）</label>
                        <input type="text" id="edit-Title_EN" name="Title_EN">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-1" data-zh="更新日期" data-en="Last Updated">更新日期</label>
                    <input type="date" id="edit-Last_Updated" name="Last_Updated">
                </div>
                
                <div class="flex justify-end gap-3 pt-4 border-t border-slate-700">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 rounded-lg bg-slate-700 hover:bg-slate-600 transition-colors" data-zh="取消" data-en="Cancel">取消</button>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-500 transition-colors flex items-center gap-2" id="save-btn">
                        <span data-zh="儲存" data-en="Save">儲存</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- 刪除確認 Modal -->
    <div id="delete-modal" class="modal" onclick="if(event.target === this) closeDeleteModal()">
        <div class="modal-content max-w-md">
            <div class="p-6 text-center">
                <svg class="w-16 h-16 mx-auto text-red-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <h3 class="text-lg font-bold mb-2" data-zh="確定要刪除嗎？" data-en="Confirm Delete?">確定要刪除嗎？</h3>
                <p class="text-slate-400 text-sm mb-6" id="delete-info"></p>
                <div class="flex justify-center gap-3">
                    <button onclick="closeDeleteModal()" class="px-4 py-2 rounded-lg bg-slate-700 hover:bg-slate-600 transition-colors" data-zh="取消" data-en="Cancel">取消</button>
                    <button onclick="executeDelete()" class="px-4 py-2 rounded-lg bg-red-600 hover:bg-red-500 transition-colors" data-zh="刪除" data-en="Delete">刪除</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast 通知 -->
    <div id="toast" class="toast"></div>

    <script>
        // 語言設定
        let currentLang = 'zh';
        
        // 資料
        let csvData = <?php echo json_encode($csvData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
        let filteredData = [...csvData];
        let sortColumn = null;
        let sortDirection = 'asc';
        let deleteRowIndex = null;

        // 拖放相關變數
        let draggedRow = null;
        let draggedIndex = null;

        // 初始化
        document.addEventListener('DOMContentLoaded', function() {
            // 搜尋監聽
            document.getElementById('search-input').addEventListener('input', debounce(filterData, 300));
            
            // 類別篩選監聽
            document.getElementById('category-filter').addEventListener('change', filterData);
            
            // 排序監聽
            document.querySelectorAll('th[data-sort]').forEach(th => {
                th.addEventListener('click', () => sortData(th.dataset.sort));
            });
            
            // 表單提交
            document.getElementById('edit-form').addEventListener('submit', handleSubmit);
            
            // ESC 關閉 Modal
            document.addEventListener('keydown', e => {
                if (e.key === 'Escape') {
                    closeModal();
                    closeDeleteModal();
                }
            });
            
            // 初始化拖放功能
            initDragAndDrop();
            
            updateFilteredCount();
        });
        
        // 初始化拖放功能
        function initDragAndDrop() {
            const tbody = document.getElementById('table-body');
            
            tbody.addEventListener('dragstart', handleDragStart);
            tbody.addEventListener('dragend', handleDragEnd);
            tbody.addEventListener('dragover', handleDragOver);
            tbody.addEventListener('dragleave', handleDragLeave);
            tbody.addEventListener('drop', handleDrop);
        }
        
        // 拖放開始
        function handleDragStart(e) {
            const row = e.target.closest('tr');
            if (!row || !row.dataset.rowIndex) return;
            
            // 檢查是否有搜尋或篩選，如果有則禁止拖放
            const searchTerm = document.getElementById('search-input').value;
            const categoryFilter = document.getElementById('category-filter').value;
            if (searchTerm || categoryFilter) {
                e.preventDefault();
                showToast(currentLang === 'zh' ? '請先清除搜尋和篩選條件再進行排序' : 'Please clear search and filter before reordering', 'error');
                return;
            }
            
            draggedRow = row;
            draggedIndex = parseInt(row.dataset.rowIndex);
            
            // 設置拖放效果
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', draggedIndex);
            
            // 延遲添加拖動樣式，避免立即觸發
            setTimeout(() => {
                row.classList.add('dragging');
            }, 0);
        }
        
        // 拖放結束
        function handleDragEnd(e) {
            const row = e.target.closest('tr');
            if (row) {
                row.classList.remove('dragging');
            }
            
            // 清除所有行的拖放狀態
            document.querySelectorAll('#table-body tr').forEach(tr => {
                tr.classList.remove('drag-over', 'drag-over-bottom');
            });
            
            draggedRow = null;
            draggedIndex = null;
        }
        
        // 拖放經過
        function handleDragOver(e) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            
            const row = e.target.closest('tr');
            if (!row || row === draggedRow || !row.dataset.rowIndex) return;
            
            // 清除其他行的樣式
            document.querySelectorAll('#table-body tr').forEach(tr => {
                if (tr !== row) {
                    tr.classList.remove('drag-over', 'drag-over-bottom');
                }
            });
            
            // 根據滑鼠位置決定是插入到上方還是下方
            const rect = row.getBoundingClientRect();
            const midY = rect.top + rect.height / 2;
            
            if (e.clientY < midY) {
                row.classList.add('drag-over');
                row.classList.remove('drag-over-bottom');
            } else {
                row.classList.add('drag-over-bottom');
                row.classList.remove('drag-over');
            }
        }
        
        // 拖放離開
        function handleDragLeave(e) {
            const row = e.target.closest('tr');
            if (row) {
                // 檢查是否真的離開了這一行
                const rect = row.getBoundingClientRect();
                if (e.clientY < rect.top || e.clientY > rect.bottom ||
                    e.clientX < rect.left || e.clientX > rect.right) {
                    row.classList.remove('drag-over', 'drag-over-bottom');
                }
            }
        }
        
        // 放下
        async function handleDrop(e) {
            e.preventDefault();
            
            const targetRow = e.target.closest('tr');
            if (!targetRow || targetRow === draggedRow || !targetRow.dataset.rowIndex) return;
            
            const targetIndex = parseInt(targetRow.dataset.rowIndex);
            const fromIndex = draggedIndex;
            
            // 判斷插入位置
            const rect = targetRow.getBoundingClientRect();
            const midY = rect.top + rect.height / 2;
            const insertAfter = e.clientY >= midY;
            
            // 清除樣式
            targetRow.classList.remove('drag-over', 'drag-over-bottom');
            
            // 計算新的順序
            let newOrder = csvData.map(row => row.rowIndex);
            
            // 從原位置移除
            newOrder.splice(fromIndex, 1);
            
            // 計算新的插入位置
            let insertIndex = targetIndex;
            if (fromIndex < targetIndex) {
                insertIndex--;
            }
            if (insertAfter) {
                insertIndex++;
            }
            
            // 插入到新位置
            newOrder.splice(insertIndex, 0, fromIndex);
            
            // 發送到後端保存
            await saveReorder(newOrder);
        }
        
        // 保存新排序
        async function saveReorder(order) {
            try {
                const formData = new FormData();
                formData.append('action', 'reorder');
                formData.append('order', JSON.stringify(order));
                
                const response = await fetch('', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showToast(currentLang === 'zh' ? '排序已更新' : 'Order updated', 'success');
                    setTimeout(() => location.reload(), 500);
                } else {
                    showToast(result.message || (currentLang === 'zh' ? '排序失敗' : 'Reorder failed'), 'error');
                }
            } catch (error) {
                showToast(currentLang === 'zh' ? '發生錯誤' : 'An error occurred', 'error');
                console.error(error);
            }
        }

        // 防抖函數
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        // 篩選資料
        function filterData() {
            const searchTerm = document.getElementById('search-input').value.toLowerCase();
            const categoryFilter = document.getElementById('category-filter').value;
            
            filteredData = csvData.filter(row => {
                // 類別篩選
                if (categoryFilter && row.Category !== categoryFilter) {
                    return false;
                }
                
                // 搜尋篩選
                if (searchTerm) {
                    const searchFields = [
                        row.Category, row.Title, row.URL, row.Screenshot,
                        row.Category_ZH, row.Category_EN, row.Title_ZH, row.Title_EN
                    ];
                    return searchFields.some(field => 
                        field && field.toLowerCase().includes(searchTerm)
                    );
                }
                
                return true;
            });
            
            renderTable();
            updateFilteredCount();
        }

        // 排序資料
        function sortData(column) {
            // 切換排序方向
            if (sortColumn === column) {
                sortDirection = sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                sortColumn = column;
                sortDirection = 'asc';
            }
            
            // 更新表頭樣式
            document.querySelectorAll('th[data-sort]').forEach(th => {
                th.classList.remove('sorted');
                if (th.dataset.sort === column) {
                    th.classList.add('sorted');
                }
            });
            
            // 排序
            filteredData.sort((a, b) => {
                let valA = a[column] ?? '';
                let valB = b[column] ?? '';
                
                // 數字排序
                if (column === 'rowIndex') {
                    valA = parseInt(valA);
                    valB = parseInt(valB);
                }
                
                if (valA < valB) return sortDirection === 'asc' ? -1 : 1;
                if (valA > valB) return sortDirection === 'asc' ? 1 : -1;
                return 0;
            });
            
            renderTable();
        }

        // 渲染表格
        function renderTable() {
            const tbody = document.getElementById('table-body');
            
            if (filteredData.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="9" class="px-3 py-8 text-center text-slate-400">
                            <span data-zh="沒有找到符合的資料" data-en="No matching records found">${currentLang === 'zh' ? '沒有找到符合的資料' : 'No matching records found'}</span>
                        </td>
                    </tr>
                `;
                return;
            }
            
            tbody.innerHTML = filteredData.map(row => `
                <tr class="border-t border-slate-700 text-sm" data-row='${escapeHtml(JSON.stringify(row))}' data-row-index="${row.rowIndex}" draggable="true">
                    <td class="px-2 py-2">
                        <div class="drag-handle" title="${currentLang === 'zh' ? '拖放排序' : 'Drag to reorder'}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"></path>
                            </svg>
                        </div>
                    </td>
                    <td class="px-3 py-2 text-slate-400">${row.rowIndex + 1}</td>
                    <td class="px-3 py-2">${escapeHtml(row.Category)}</td>
                    <td class="px-3 py-2" title="${escapeHtml(row.Title)}">${escapeHtml(row.Title)}</td>
                    <td class="px-3 py-2 url-cell">
                        <a href="${escapeHtml(row.URL)}" target="_blank" class="text-indigo-400 hover:text-indigo-300" title="${escapeHtml(row.URL)}">
                            ${escapeHtml(row.URL)}
                        </a>
                    </td>
                    <td class="px-3 py-2" title="${escapeHtml(row.Title_ZH)}">${escapeHtml(row.Title_ZH)}</td>
                    <td class="px-3 py-2" title="${escapeHtml(row.Title_EN)}">${escapeHtml(row.Title_EN)}</td>
                    <td class="px-3 py-2 text-slate-400">${escapeHtml(row.Last_Updated)}</td>
                    <td class="px-3 py-2">
                        <div class="flex items-center justify-center gap-2">
                            <button onclick="openEditModal(this.closest('tr'))" class="p-1.5 rounded hover:bg-slate-600 text-indigo-400 hover:text-indigo-300 transition-colors" title="${currentLang === 'zh' ? '編輯' : 'Edit'}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </button>
                            <button onclick="confirmDelete(this.closest('tr'))" class="p-1.5 rounded hover:bg-slate-600 text-red-400 hover:text-red-300 transition-colors" title="${currentLang === 'zh' ? '刪除' : 'Delete'}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>
            `).join('');
        }

        // 更新篩選後筆數
        function updateFilteredCount() {
            const countEl = document.getElementById('filtered-count');
            if (filteredData.length !== csvData.length) {
                countEl.textContent = currentLang === 'zh' 
                    ? `顯示 ${filteredData.length} / ${csvData.length} 筆`
                    : `Showing ${filteredData.length} / ${csvData.length}`;
            } else {
                countEl.textContent = '';
            }
        }

        // 開啟編輯 Modal
        function openEditModal(tr) {
            const row = JSON.parse(tr.dataset.row);
            
            document.getElementById('modal-title').setAttribute('data-zh', '編輯資料');
            document.getElementById('modal-title').setAttribute('data-en', 'Edit Record');
            document.getElementById('modal-title').textContent = currentLang === 'zh' ? '編輯資料' : 'Edit Record';
            
            document.getElementById('form-action').value = 'save';
            document.getElementById('edit-rowIndex').value = row.rowIndex;
            document.getElementById('edit-Category').value = row.Category;
            document.getElementById('edit-Title').value = row.Title;
            document.getElementById('edit-URL').value = row.URL;
            document.getElementById('edit-Screenshot').value = row.Screenshot || '';
            document.getElementById('edit-Category_ZH').value = row.Category_ZH || '';
            document.getElementById('edit-Category_EN').value = row.Category_EN || '';
            document.getElementById('edit-Title_ZH').value = row.Title_ZH || '';
            document.getElementById('edit-Title_EN').value = row.Title_EN || '';
            document.getElementById('edit-Last_Updated').value = row.Last_Updated || '';
            
            document.getElementById('edit-modal').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        // 開啟新增 Modal
        function openAddModal() {
            document.getElementById('modal-title').setAttribute('data-zh', '新增資料');
            document.getElementById('modal-title').setAttribute('data-en', 'Add Record');
            document.getElementById('modal-title').textContent = currentLang === 'zh' ? '新增資料' : 'Add Record';
            
            document.getElementById('form-action').value = 'add';
            document.getElementById('edit-rowIndex').value = '';
            document.getElementById('edit-Category').value = '';
            document.getElementById('edit-Title').value = '';
            document.getElementById('edit-URL').value = '';
            document.getElementById('edit-Screenshot').value = '';
            document.getElementById('edit-Category_ZH').value = '';
            document.getElementById('edit-Category_EN').value = '';
            document.getElementById('edit-Title_ZH').value = '';
            document.getElementById('edit-Title_EN').value = '';
            document.getElementById('edit-Last_Updated').value = new Date().toISOString().split('T')[0];
            
            document.getElementById('edit-modal').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        // 關閉編輯 Modal
        function closeModal() {
            document.getElementById('edit-modal').classList.remove('active');
            document.body.style.overflow = '';
        }

        // 確認刪除
        function confirmDelete(tr) {
            const row = JSON.parse(tr.dataset.row);
            deleteRowIndex = row.rowIndex;
            
            document.getElementById('delete-info').textContent = 
                currentLang === 'zh' 
                    ? `將刪除：${row.Title}`
                    : `Will delete: ${row.Title}`;
            
            document.getElementById('delete-modal').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        // 關閉刪除 Modal
        function closeDeleteModal() {
            document.getElementById('delete-modal').classList.remove('active');
            document.body.style.overflow = '';
            deleteRowIndex = null;
        }

        // 執行刪除
        async function executeDelete() {
            if (deleteRowIndex === null) return;
            
            try {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('rowIndex', deleteRowIndex);
                
                const response = await fetch('', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showToast(currentLang === 'zh' ? '刪除成功' : 'Deleted successfully', 'success');
                    setTimeout(() => location.reload(), 500);
                } else {
                    showToast(result.message || (currentLang === 'zh' ? '刪除失敗' : 'Delete failed'), 'error');
                }
            } catch (error) {
                showToast(currentLang === 'zh' ? '發生錯誤' : 'An error occurred', 'error');
                console.error(error);
            }
            
            closeDeleteModal();
        }

        // 處理表單提交
        async function handleSubmit(e) {
            e.preventDefault();
            
            const form = e.target;
            const saveBtn = document.getElementById('save-btn');
            const formData = new FormData(form);
            
            // 載入中狀態
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<span class="spinner"></span>';
            
            try {
                const response = await fetch('', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showToast(currentLang === 'zh' ? '儲存成功' : 'Saved successfully', 'success');
                    closeModal();
                    setTimeout(() => location.reload(), 500);
                } else {
                    showToast(result.message || (currentLang === 'zh' ? '儲存失敗' : 'Save failed'), 'error');
                }
            } catch (error) {
                showToast(currentLang === 'zh' ? '發生錯誤' : 'An error occurred', 'error');
                console.error(error);
            } finally {
                saveBtn.disabled = false;
                saveBtn.innerHTML = `<span data-zh="儲存" data-en="Save">${currentLang === 'zh' ? '儲存' : 'Save'}</span>`;
            }
        }

        // 顯示 Toast
        function showToast(message, type = 'success') {
            const toast = document.getElementById('toast');
            toast.textContent = message;
            toast.className = `toast ${type} show`;
            
            setTimeout(() => {
                toast.classList.remove('show');
            }, 3000);
        }

        // 切換語言
        function toggleLang() {
            currentLang = currentLang === 'zh' ? 'en' : 'zh';
            updateUI();
        }

        // 更新 UI 語言
        function updateUI() {
            document.querySelectorAll('[data-zh][data-en]').forEach(el => {
                const text = el.getAttribute(`data-${currentLang}`);
                if (text) el.textContent = text;
            });
            
            // 更新 placeholder
            document.querySelectorAll('[data-placeholder-zh][data-placeholder-en]').forEach(el => {
                const text = el.getAttribute(`data-placeholder-${currentLang}`);
                if (text) el.placeholder = text;
            });
            
            // 更新篩選下拉選單第一項
            const filterOption = document.querySelector('#category-filter option[value=""]');
            if (filterOption) {
                filterOption.textContent = currentLang === 'zh' ? '所有類別' : 'All Categories';
            }
            
            updateFilteredCount();
            renderTable();
        }

        // HTML 跳脫
        function escapeHtml(text) {
            if (!text) return '';
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return String(text).replace(/[&<>"']/g, m => map[m]);
        }
    </script>
</body>
</html>
