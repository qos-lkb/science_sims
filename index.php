<?php
// Read and parse CSV file
function parseCSV($filename) {
    $data = [];
    if (($handle = fopen($filename, "r")) !== FALSE) {
        $header = fgetcsv($handle); // Skip header row
        while (($row = fgetcsv($handle)) !== FALSE) {
            if (count($row) >= 3) {
                $data[] = [
                    'category' => $row[0],
                    'title' => $row[1],
                    'url' => $row[2],
                    'screenshot' => isset($row[3]) && !empty($row[3]) ? $row[3] : '',
                    'category_zh' => isset($row[4]) && !empty($row[4]) ? $row[4] : $row[0],
                    'category_en' => isset($row[5]) && !empty($row[5]) ? $row[5] : $row[0],
                    'title_zh' => isset($row[6]) && !empty($row[6]) ? $row[6] : $row[1],
                    'title_en' => isset($row[7]) && !empty($row[7]) ? $row[7] : $row[1]
                ];
            }
        }
        fclose($handle);
    }
    return $data;
}

// Group data by category
function groupByCategory($data) {
    $grouped = [];
    foreach ($data as $item) {
        $category = $item['category'];
        if (!isset($grouped[$category])) {
            $grouped[$category] = [];
        }
        $grouped[$category][] = $item;
    }
    return $grouped;
}

// Build translation maps from CSV data
function buildTranslationMaps($data) {
    $categoryMap = [];
    $titleMap = [];
    
    foreach ($data as $item) {
        // Build category map (only need to add once per category)
        if (!isset($categoryMap[$item['category']])) {
            $categoryMap[$item['category']] = [
                'zh' => $item['category_zh'],
                'en' => $item['category_en']
            ];
        }
        
        // Build title map
        $titleMap[$item['title']] = [
            'zh' => $item['title_zh'],
            'en' => $item['title_en']
        ];
    }
    
    return ['categoryMap' => $categoryMap, 'titleMap' => $titleMap];
}

$csvData = parseCSV('index.csv');
$groupedData = groupByCategory($csvData);
$translations = buildTranslationMaps($csvData);
$categoryMap = $translations['categoryMap'];
$titleMap = $translations['titleMap'];
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>物理模擬實驗平台 | HKDSE Physics Sim</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- MathJax -->
    <script id="MathJax-script" async src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js"></script>
    <!-- html2canvas for screenshot -->
    <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
    <style>
        /* 子選單展開動畫 */
        .submenu { max-height: 0; overflow: hidden; transition: max-height 0.3s ease-out; }
        .submenu.open { max-height: 1000px; }
        .rotate-icon { transition: transform 0.3s; }
        .rotate-icon.active { transform: rotate(180deg); }
        
        /* 側邊欄過渡動畫 (Mobile) */
        #sidebar {
            transition: transform 0.3s ease-in-out;
        }
        @media (max-width: 767px) {
            #sidebar {
                position: fixed;
                left: 0;
                top: 64px;
            }
            #sidebar.hidden-mobile { transform: translateX(-100%); }
            #sidebar.show-mobile { transform: translateX(0); }
        }
        @media (min-width: 768px) {
            #sidebar {
                position: relative;
                height: calc(100vh - 64px);
                top: 0;
            }
        }

        /* 自定義捲軸 */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #1e293b; }
        ::-webkit-scrollbar-thumb { background: #475569; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #64748b; }
        
        /* 遮罩層 */
        #overlay { display: none; }
        #overlay.active { display: block; }
        
        /* Container 樣式 */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
            width: 100%;
        }
        @media (min-width: 640px) {
            .container {
                padding: 0 1.5rem;
            }
        }
        @media (min-width: 1024px) {
            .container {
                padding: 0 2rem;
            }
        }
        
        /* Modal 樣式 */
        #sim-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 100;
            background-color: rgba(0, 0, 0, 0.75);
            backdrop-filter: blur(4px);
        }
        #sim-modal.active {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        #sim-modal-content {
            position: relative;
            width: 95%;
            height: 90%;
            max-width: 1200px;
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3), 0 10px 10px -5px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        #sim-modal-close {
            position: absolute;
            top: 2vh;
            right: 2vw;
            width: 3rem;
            height: 3rem;
            background-color: rgba(0, 0, 0, 0.75);
            border: 2px solid rgba(255, 255, 255, 0.95);
            border-radius: 50%;
            color: white;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
            z-index: 101;
            backdrop-filter: blur(4px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3);
        }
        #sim-modal-capture {
            position: absolute;
            top: 2vh;
            right: calc(2vw + 4rem);
            width: 3rem;
            height: 3rem;
            background-color: rgba(0, 0, 0, 0.75);
            border: 2px solid rgba(255, 255, 255, 0.95);
            border-radius: 50%;
            color: white;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
            z-index: 101;
            backdrop-filter: blur(4px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3);
        }
        #sim-modal-capture:hover {
            background-color: rgba(0, 0, 0, 0.8);
            border-color: white;
            transform: scale(1.1);
        }
        #sim-modal-capture:active {
            transform: scale(0.95);
        }
        #sim-modal-capture:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        #sim-modal-close:hover {
            background-color: rgba(0, 0, 0, 0.8);
            border-color: white;
            transform: scale(1.1);
        }
        #sim-modal-close:active {
            transform: scale(0.95);
        }
        #sim-modal-iframe {
            flex: 1;
            width: 100%;
            border: none;
            background-color: white;
        }
        @media (max-width: 768px) {
            #sim-modal-content {
                width: 100%;
                height: 100%;
                border-radius: 0;
            }
            #sim-modal-close {
                top: 1rem;
                right: 1rem;
                width: 2.75rem;
                height: 2.75rem;
            }
            #sim-modal-capture {
                top: 1rem;
                right: calc(1rem + 3.5rem);
                width: 2.75rem;
                height: 2.75rem;
            }
        }
        
        /* Footer 樣式 */
        footer {
            background-color: #1e293b;
            color: #cbd5e1;
            border-top: 1px solid #334155;
            margin-top: auto;
            padding: 0.75rem 0;
        }
        footer .container {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 1rem;
            text-align: right;
            flex-wrap: wrap;
        }
        .footer-copyright {
            font-size: 0.8125rem;
            color: #94a3b8;
        }
        .footer-license {
            font-size: 0.8125rem;
            color: #94a3b8;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        .footer-license a {
            color: #60a5fa;
            text-decoration: none;
            transition: all 0.2s;
        }
        .footer-license a:hover {
            color: #93c5fd;
        }
        .cc-badge {
            display: inline-block;
            padding: 0.2rem 0.4rem;
            background-color: rgba(96, 165, 250, 0.1);
            border: 1px solid #60a5fa;
            border-radius: 4px;
            font-size: 0.7rem;
            font-weight: 500;
            transition: all 0.2s;
        }
        .footer-license a:hover .cc-badge {
            background-color: rgba(96, 165, 250, 0.2);
            border-color: #93c5fd;
        }
        @media (max-width: 768px) {
            footer {
                padding: 0.625rem 0;
            }
            footer .container {
                justify-content: center;
                text-align: center;
                gap: 0.5rem;
            }
            .footer-copyright,
            .footer-license {
                font-size: 0.75rem;
            }
        }
    </style>
</head>
<body class="bg-slate-50 font-sans text-slate-900 overflow-x-hidden flex flex-col min-h-screen">

    <!-- 1. 標題列 -->
    <header class="bg-indigo-900 text-white shadow-md fixed w-full z-50 top-0">
        <div class="container">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <!-- 行動裝置選單按鈕 -->
                    <button id="mobile-toggle" class="mr-3 p-2 rounded-md hover:bg-indigo-800 md:hidden focus:outline-none">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                    <div class="flex-shrink-0 flex items-center gap-2 cursor-pointer" onclick="location.reload()">
                        <svg class="w-7 h-7 md:w-8 md:h-8 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        <span class="font-bold text-lg md:text-xl tracking-tight" id="app-title">物理模擬實驗平台</span>
                    </div>
                </div>
                
                <div class="flex items-center gap-3">
                    <button onclick="toggleLang()" class="px-3 py-1 md:px-4 md:py-1.5 rounded-full border border-indigo-400 hover:bg-white hover:text-indigo-900 transition-all text-xs md:text-sm font-medium">
                        中 / EN
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- 背景遮罩 (Mobile 選單開啟時) -->
    <div id="overlay" class="fixed inset-0 bg-black/50 z-30 md:hidden" onclick="toggleSidebar()"></div>

    <div class="flex-1 pt-16">
        <div class="container">
            <div class="flex">
                <!-- 2. 左方選單列 -->
                <aside id="sidebar" class="w-64 bg-slate-800 text-slate-300 flex-shrink-0 z-40 overflow-y-auto hidden-mobile md:block">
            
            <div class="p-4 pb-2 uppercase text-[10px] font-bold text-slate-500 tracking-[2px]" id="core-label">核心單元 Compulsory</div>
            
            <nav class="mt-2 space-y-1 px-2 pb-6" id="main-nav">
                <?php 
                $firstCategory = true;
                foreach ($groupedData as $category => $items): 
                    $categoryId = strtolower(str_replace(' ', '-', $category));
                    $categoryZh = isset($categoryMap[$category]) ? $categoryMap[$category]['zh'] : $category;
                    $categoryEn = isset($categoryMap[$category]) ? $categoryMap[$category]['en'] : $category;
                ?>
                <div class="nav-group <?php echo $firstCategory ? '' : 'border-t border-slate-700/50 mt-1'; ?>">
                    <button onclick="toggleSub(this); showCategory('<?php echo $categoryId; ?>')" class="group w-full flex items-center justify-between p-3 rounded-md hover:bg-slate-700 hover:text-white transition-colors">
                        <span class="main-label" data-zh="<?php echo htmlspecialchars($categoryZh); ?>" data-en="<?php echo htmlspecialchars($categoryEn); ?>"><?php echo htmlspecialchars($categoryZh); ?></span>
                        <svg class="w-4 h-4 rotate-icon <?php echo $firstCategory ? 'active' : ''; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div class="submenu bg-slate-900 rounded-md <?php echo $firstCategory ? 'open' : ''; ?>">
                        <?php foreach ($items as $item): 
                            $titleZh = isset($titleMap[$item['title']]) ? $titleMap[$item['title']]['zh'] : $item['title'];
                            $titleEn = isset($titleMap[$item['title']]) ? $titleMap[$item['title']]['en'] : $item['title'];
                        ?>
                        <div onclick="openModal('<?php echo htmlspecialchars($item['url']); ?>')" class="sub-label block py-2 px-6 text-sm hover:text-indigo-400 cursor-pointer" data-zh="<?php echo htmlspecialchars($titleZh); ?>" data-en="<?php echo htmlspecialchars($titleEn); ?>"><?php echo htmlspecialchars($titleZh); ?></div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php 
                $firstCategory = false;
                endforeach; 
                ?>
            </nav>
                </aside>

                <!-- 3. 主顯示區域 -->
                <main class="flex-1 transition-all duration-300 py-4 md:py-8 px-4 md:px-6 lg:px-8">
                <div class="mb-6 md:mb-8 border-b border-slate-200 pb-6">
                    <nav class="flex mb-2 text-xs md:text-sm text-slate-500">
                        <span id="breadcrumb-parent" data-zh="<?php echo isset($categoryMap[key($groupedData)]) ? htmlspecialchars($categoryMap[key($groupedData)]['zh']) : htmlspecialchars(key($groupedData)); ?>" data-en="<?php echo isset($categoryMap[key($groupedData)]) ? htmlspecialchars($categoryMap[key($groupedData)]['en']) : htmlspecialchars(key($groupedData)); ?>"><?php echo isset($categoryMap[key($groupedData)]) ? $categoryMap[key($groupedData)]['zh'] : key($groupedData); ?></span>
                        <span class="mx-2">/</span>
                        <span id="breadcrumb-child" class="text-indigo-600 font-medium" data-zh="所有實驗" data-en="All Experiments">所有實驗</span>
                    </nav>
                    <h1 id="page-title" class="text-2xl md:text-4xl font-extrabold text-slate-900 tracking-tight" data-zh="<?php echo isset($categoryMap[key($groupedData)]) ? htmlspecialchars($categoryMap[key($groupedData)]['zh'] . '模擬實驗') : htmlspecialchars(key($groupedData) . '模擬實驗'); ?>" data-en="<?php echo isset($categoryMap[key($groupedData)]) ? htmlspecialchars($categoryMap[key($groupedData)]['en'] . ' Simulations') : htmlspecialchars(key($groupedData) . ' Simulations'); ?>"><?php echo isset($categoryMap[key($groupedData)]) ? $categoryMap[key($groupedData)]['zh'] : key($groupedData); ?>模擬實驗</h1>
                </div>

                <div id="card-container" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6">
                <?php 
                $currentCategory = key($groupedData);
                foreach ($groupedData[$currentCategory] as $item): 
                ?>
                <div onclick="openModal('<?php echo htmlspecialchars($item['url']); ?>')" class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden hover:shadow-md transition-shadow flex flex-col cursor-pointer">
                    <div class="h-32 md:h-40 bg-slate-100 flex items-center justify-center border-b border-slate-100 relative group overflow-hidden">
                        <?php if (!empty($item['screenshot'])): ?>
                            <img src="<?php echo htmlspecialchars($item['screenshot']); ?>" alt="<?php echo htmlspecialchars(isset($titleMap[$item['title']]) ? $titleMap[$item['title']]['zh'] : $item['title']); ?>" class="w-full h-full object-cover" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                            <span class="text-slate-400 text-sm image-placeholder" data-zh="[實驗影像]" data-en="[Experiment Image]" style="display: none;">[實驗影像]</span>
                        <?php else: ?>
                            <span class="text-slate-400 text-sm image-placeholder" data-zh="[實驗影像]" data-en="[Experiment Image]">[實驗影像]</span>
                        <?php endif; ?>
                        <div class="absolute inset-0 bg-indigo-900/0 group-hover:bg-indigo-900/10 transition-colors"></div>
                    </div>
                    <div class="p-4 md:p-5 flex-grow">
                        <?php 
                        $titleZh = isset($titleMap[$item['title']]) ? $titleMap[$item['title']]['zh'] : $item['title'];
                        $titleEn = isset($titleMap[$item['title']]) ? $titleMap[$item['title']]['en'] : $item['title'];
                        ?>
                        <h3 class="font-bold text-base md:text-lg text-slate-800 mb-2 card-t" data-zh="<?php echo htmlspecialchars($titleZh); ?>" data-en="<?php echo htmlspecialchars($titleEn); ?>"><?php echo htmlspecialchars($titleZh); ?></h3>
                        <p class="text-slate-600 text-xs md:text-sm leading-relaxed mb-4 card-d" data-zh="點擊進入模擬實驗" data-en="Click to enter simulation">點擊進入模擬實驗</p>
                    </div>
                    <div class="px-4 py-2 md:px-5 md:py-3 bg-slate-50 border-t border-slate-100">
                        <p class="text-[10px] md:text-[11px] text-slate-400 font-medium tracking-wide update-text" data-zh="最後更新日期：2025-12-21" data-en="Last Updated: 2025-12-21">最後更新日期：2025-12-21</p>
                    </div>
                </div>
                        <?php endforeach; ?>
                    </div>
                </main>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-copyright" id="footer-copyright" data-zh="版權 © Mr. Bryan Leung" data-en="Copyright © Mr. Bryan Leung">版權 © Mr. Bryan Leung</div>
            <div class="footer-license" id="footer-license">
                <span data-zh="開源及 Creative Commons，可以自由使用。" data-en="Open source and Creative Commons, free to use.">開源及 Creative Commons，可以自由使用。</span>
                <a href="https://creativecommons.org/licenses/by/4.0/" target="_blank" rel="noopener noreferrer" class="cc-link" title="Creative Commons Attribution 4.0 International License">
                    <span class="cc-badge">CC BY 4.0</span>
                </a>
            </div>
        </div>
    </footer>

    <!-- Modal for Simulation -->
    <div id="sim-modal" onclick="closeModalOnBackdrop(event)">
        <button id="sim-modal-close" onclick="closeModal(); event.stopPropagation();" aria-label="關閉">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
        <button id="sim-modal-capture" onclick="captureModal(); event.stopPropagation();" aria-label="截圖" title="截圖並下載為 PNG">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
        </button>
        <div id="sim-modal-content" onclick="event.stopPropagation()">
            <iframe id="sim-modal-iframe" src=""></iframe>
        </div>
    </div>

    <script>
        let currentLang = 'zh';
        const categoryData = <?php echo json_encode($groupedData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
        const categoryMap = <?php echo json_encode($categoryMap, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
        const titleMap = <?php echo json_encode($titleMap, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

        // 摺疊/展開子選單
        function toggleSub(btn) {
            const submenu = btn.nextElementSibling;
            const icon = btn.querySelector('.rotate-icon');
            submenu.classList.toggle('open');
            icon.classList.toggle('active');
        }

        // 顯示特定類別的卡片
        function showCategory(categoryId) {
            const category = Object.keys(categoryData).find(cat => 
                cat.toLowerCase().replace(/\s+/g, '-') === categoryId
            ) || Object.keys(categoryData)[0];
            
            const items = categoryData[category];
            const container = document.getElementById('card-container');
            const breadcrumbParent = document.getElementById('breadcrumb-parent');
            const pageTitle = document.getElementById('page-title');
            
            // 更新麵包屑和標題
            const categoryZh = categoryMap[category] ? categoryMap[category]['zh'] : category;
            const categoryEn = categoryMap[category] ? categoryMap[category]['en'] : category;
            
            breadcrumbParent.setAttribute('data-zh', categoryZh);
            breadcrumbParent.setAttribute('data-en', categoryEn);
            breadcrumbParent.textContent = currentLang === 'zh' ? categoryZh : categoryEn;
            
            const titleZh = categoryZh + '模擬實驗';
            const titleEn = categoryEn + ' Simulations';
            pageTitle.setAttribute('data-zh', titleZh);
            pageTitle.setAttribute('data-en', titleEn);
            pageTitle.textContent = currentLang === 'zh' ? titleZh : titleEn;
            
            // 生成卡片
            container.innerHTML = items.map(item => {
                const titleZh = titleMap[item.title] ? titleMap[item.title]['zh'] : item.title;
                const titleEn = titleMap[item.title] ? titleMap[item.title]['en'] : item.title;
                const screenshot = item.screenshot || '';
                const hasScreenshot = screenshot && screenshot.trim() !== '';
                return `
                <div onclick="openModal('${escapeHtml(item.url)}')" class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden hover:shadow-md transition-shadow flex flex-col cursor-pointer">
                    <div class="h-32 md:h-40 bg-slate-100 flex items-center justify-center border-b border-slate-100 relative group overflow-hidden">
                        ${hasScreenshot ? 
                            `<img src="${escapeHtml(screenshot)}" alt="${escapeHtml(titleZh)}" class="w-full h-full object-cover">` :
                            `<span class="text-slate-400 text-sm image-placeholder" data-zh="[實驗影像]" data-en="[Experiment Image]">${currentLang === 'zh' ? '[實驗影像]' : '[Experiment Image]'}</span>`
                        }
                        <div class="absolute inset-0 bg-indigo-900/0 group-hover:bg-indigo-900/10 transition-colors"></div>
                    </div>
                    <div class="p-4 md:p-5 flex-grow">
                        <h3 class="font-bold text-base md:text-lg text-slate-800 mb-2 card-t" data-zh="${escapeHtml(titleZh)}" data-en="${escapeHtml(titleEn)}">${escapeHtml(currentLang === 'zh' ? titleZh : titleEn)}</h3>
                        <p class="text-slate-600 text-xs md:text-sm leading-relaxed mb-4 card-d" data-zh="點擊進入模擬實驗" data-en="Click to enter simulation">${currentLang === 'zh' ? '點擊進入模擬實驗' : 'Click to enter simulation'}</p>
                    </div>
                    <div class="px-4 py-2 md:px-5 md:py-3 bg-slate-50 border-t border-slate-100">
                        <p class="text-[10px] md:text-[11px] text-slate-400 font-medium tracking-wide update-text" data-zh="最後更新日期：2025-12-21" data-en="Last Updated: 2025-12-21">${currentLang === 'zh' ? '最後更新日期：2025-12-21' : 'Last Updated: 2025-12-21'}</p>
                    </div>
                </div>
            `;
            }).join('');
            
            updateUI();
        }

        function escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, m => map[m]);
        }

        // 行動裝置側邊欄切換
        const mobileToggle = document.getElementById('mobile-toggle');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');

        function toggleSidebar() {
            const isHidden = sidebar.classList.contains('hidden-mobile');
            if (isHidden) {
                sidebar.classList.remove('hidden-mobile');
                sidebar.classList.add('show-mobile');
                overlay.classList.add('active');
            } else {
                sidebar.classList.add('hidden-mobile');
                sidebar.classList.remove('show-mobile');
                overlay.classList.remove('active');
            }
        }

        mobileToggle.addEventListener('click', toggleSidebar);

        // 切換語言
        function toggleLang() {
            currentLang = currentLang === 'zh' ? 'en' : 'zh';
            updateUI();
        }

        // Modal 函數
        let currentModalUrl = '';
        function openModal(url) {
            const modal = document.getElementById('sim-modal');
            const iframe = document.getElementById('sim-modal-iframe');
            const captureBtn = document.getElementById('sim-modal-capture');
            currentModalUrl = url;
            iframe.src = url;
            modal.classList.add('active');
            document.body.style.overflow = 'hidden'; // 防止背景滾動
            
            // 等待 iframe 載入完成後啟用截圖按鈕
            captureBtn.disabled = true;
            iframe.onload = function() {
                setTimeout(() => {
                    captureBtn.disabled = false;
                }, 1000); // 給 iframe 內容一些時間渲染
            };
        }

        function closeModal() {
            const modal = document.getElementById('sim-modal');
            const iframe = document.getElementById('sim-modal-iframe');
            const captureBtn = document.getElementById('sim-modal-capture');
            modal.classList.remove('active');
            iframe.src = ''; // 清空 iframe 以停止載入
            document.body.style.overflow = ''; // 恢復背景滾動
            captureBtn.disabled = true;
            currentModalUrl = '';
        }
        
        // 截圖功能
        async function captureModal() {
            const captureBtn = document.getElementById('sim-modal-capture');
            const modalContent = document.getElementById('sim-modal-content');
            const iframe = document.getElementById('sim-modal-iframe');
            
            if (!modalContent || !iframe || !iframe.src) {
                alert(currentLang === 'zh' ? '無法截圖：內容尚未載入' : 'Cannot capture: Content not loaded');
                return;
            }
            
            try {
                captureBtn.disabled = true;
                
                // 等待字體加載完成
                await document.fonts.ready;
                
                // 嘗試從 iframe 中捕獲內容
                let canvas;
                try {
                    // 如果 iframe 是同源的，可以直接訪問其內容
                    const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
                    const iframeWin = iframe.contentWindow;
                    if (iframeDoc && iframeWin) {
                        // 等待 iframe 中的字體也加載完成
                        if (iframeWin.document && iframeWin.document.fonts) {
                            await iframeWin.document.fonts.ready;
                        }
                        
                        // 重置 iframe 的滾動位置到頂部
                        const originalScrollX = iframeWin.scrollX || 0;
                        const originalScrollY = iframeWin.scrollY || 0;
                        iframeWin.scrollTo(0, 0);
                        
                        // 等待滾動完成和渲染穩定
                        await new Promise(resolve => setTimeout(resolve, 200));
                        
                        // 處理固定定位元素
                        const fixedElements = [];
                        const allElements = iframeDoc.querySelectorAll('*');
                        allElements.forEach(el => {
                            const style = iframeWin.getComputedStyle(el);
                            if (style.position === 'fixed') {
                                fixedElements.push({
                                    element: el,
                                    originalPosition: el.style.position,
                                    originalTop: el.style.top,
                                    originalLeft: el.style.left
                                });
                                el.style.position = 'absolute';
                                const rect = el.getBoundingClientRect();
                                el.style.top = rect.top + 'px';
                                el.style.left = rect.left + 'px';
                            }
                        });
                        
                        canvas = await html2canvas(iframeDoc.body || iframeDoc.documentElement, {
                            backgroundColor: '#ffffff',
                            scale: 1,
                            useCORS: true,
                            logging: false,
                            allowTaint: false
                        });
                        
                        // 恢復固定定位元素的樣式
                        fixedElements.forEach(item => {
                            item.element.style.position = item.originalPosition;
                            item.element.style.top = item.originalTop;
                            item.element.style.left = item.originalLeft;
                        });
                        
                        // 恢復原始滾動位置
                        iframeWin.scrollTo(originalScrollX, originalScrollY);
                    } else {
                        throw new Error('Cannot access iframe content');
                    }
                } catch (e) {
                    // 如果無法訪問 iframe 內容（跨域限制），則捕獲整個 modal 內容區域
                    console.warn('Cannot access iframe content, capturing modal container:', e);
                    
                    canvas = await html2canvas(modalContent, {
                        backgroundColor: '#ffffff',
                        scale: 1,
                        useCORS: true,
                        logging: false,
                        allowTaint: false,
                        ignoreElements: (element) => {
                            // 忽略關閉按鈕和截圖按鈕
                            return element.id === 'sim-modal-close' || element.id === 'sim-modal-capture';
                        }
                    });
                }
                
                // 將 canvas 轉換為 PNG 並下載
                canvas.toBlob(function(blob) {
                    const url = URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    const fileName = getFileNameFromUrl(currentModalUrl) || 'simulation';
                    const timestamp = getFormattedTimestamp();
                    a.href = url;
                    a.download = `${fileName}_${timestamp}.png`;
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    URL.revokeObjectURL(url);
                    
                    captureBtn.disabled = false;
                }, 'image/png');
                
            } catch (error) {
                console.error('截圖失敗:', error);
                alert(currentLang === 'zh' ? '截圖失敗，請稍後再試' : 'Capture failed, please try again');
                captureBtn.disabled = false;
            }
        }
        
        // 從 URL 獲取文件名
        function getFileNameFromUrl(url) {
            if (!url) return '';
            try {
                const urlObj = new URL(url, window.location.origin);
                const pathname = urlObj.pathname;
                const fileName = pathname.split('/').pop().replace('.html', '');
                return fileName || 'simulation';
            } catch (e) {
                return 'simulation';
            }
        }
        
        // 獲取格式化的時間戳（年月日時分秒）
        function getFormattedTimestamp() {
            const now = new Date();
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            return `${year}${month}${day}${hours}${minutes}${seconds}`;
        }

        function closeModalOnBackdrop(event) {
            // 如果點擊的是背景（不是 modal 內容），則關閉
            if (event.target.id === 'sim-modal') {
                closeModal();
            }
        }

        // ESC 鍵關閉 modal
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                const modal = document.getElementById('sim-modal');
                if (modal.classList.contains('active')) {
                    closeModal();
                }
            }
        });

        function updateUI() {
            const texts = {
                zh: { title: "物理模擬實驗平台", core: "核心單元 Compulsory" },
                en: { title: "Physics Sim Platform", core: "Compulsory Part" }
            };
            document.getElementById('app-title').innerText = texts[currentLang].title;
            document.getElementById('core-label').innerText = texts[currentLang].core;

            // 更新所有帶有 data-zh 和 data-en 屬性的元素
            document.querySelectorAll('.main-label, .sub-label, .card-t, .card-d, .update-text, .image-placeholder, #breadcrumb-parent, #breadcrumb-child, #page-title, #footer-copyright, #footer-license span').forEach(el => {
                const val = el.getAttribute(`data-${currentLang}`);
                if (val) el.innerText = val;
            });
        }

        // 初始化顯示第一個類別
        window.addEventListener('DOMContentLoaded', function() {
            const firstCategory = Object.keys(categoryData)[0];
            const categoryId = firstCategory.toLowerCase().replace(/\s+/g, '-');
            showCategory(categoryId);
        });
    </script>
</body>
</html>

