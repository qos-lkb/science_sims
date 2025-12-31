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
                    'url' => $row[2]
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

$csvData = parseCSV('index.csv');
$groupedData = groupByCategory($csvData);

// Category mapping for navigation
$categoryMap = [
    'Integrated Science' => ['zh' => '綜合科學', 'en' => 'Integrated Science'],
    'Biology' => ['zh' => '生物學', 'en' => 'Biology'],
    'Chemistry' => ['zh' => '化學', 'en' => 'Chemistry'],
    'Physics' => ['zh' => '物理', 'en' => 'Physics'],
    'Astronomy' => ['zh' => '天文學', 'en' => 'Astronomy']
];
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
            #sidebar.hidden-mobile { transform: translateX(-100%); }
            #sidebar.show-mobile { transform: translateX(0); }
        }

        /* 自定義捲軸 */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #1e293b; }
        ::-webkit-scrollbar-thumb { background: #475569; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #64748b; }
        
        /* 遮罩層 */
        #overlay { display: none; }
        #overlay.active { display: block; }
    </style>
</head>
<body class="bg-slate-50 font-sans text-slate-900 overflow-x-hidden">

    <!-- 1. 標題列 -->
    <header class="bg-indigo-900 text-white shadow-md fixed w-full z-50 top-0">
        <div class="max-w-full mx-auto px-4 sm:px-6">
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

    <div class="flex pt-16 min-h-screen">
        <!-- 2. 左方選單列 -->
        <aside id="sidebar" class="w-64 bg-slate-800 text-slate-300 flex-shrink-0 fixed h-[calc(100vh-64px)] z-40 overflow-y-auto hidden-mobile md:translate-x-0">
            
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
                        <?php foreach ($items as $item): ?>
                        <a href="<?php echo htmlspecialchars($item['url']); ?>" class="sub-label block py-2 px-6 text-sm hover:text-indigo-400" data-zh="<?php echo htmlspecialchars($item['title']); ?>" data-en="<?php echo htmlspecialchars($item['title']); ?>"><?php echo htmlspecialchars($item['title']); ?></a>
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
        <main class="flex-1 ml-0 md:ml-64 p-4 md:p-8 transition-all duration-300">
            
            <div class="mb-6 md:mb-8 border-b border-slate-200 pb-6">
                <nav class="flex mb-2 text-xs md:text-sm text-slate-500">
                    <span id="breadcrumb-parent"><?php echo isset($categoryMap[key($groupedData)]) ? $categoryMap[key($groupedData)]['zh'] : key($groupedData); ?></span>
                    <span class="mx-2">/</span>
                    <span id="breadcrumb-child" class="text-indigo-600 font-medium">所有實驗</span>
                </nav>
                <h1 id="page-title" class="text-2xl md:text-4xl font-extrabold text-slate-900 tracking-tight"><?php echo isset($categoryMap[key($groupedData)]) ? $categoryMap[key($groupedData)]['zh'] : key($groupedData); ?>模擬實驗</h1>
            </div>

            <div id="card-container" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6">
                <?php 
                $currentCategory = key($groupedData);
                foreach ($groupedData[$currentCategory] as $item): 
                ?>
                <a href="<?php echo htmlspecialchars($item['url']); ?>" class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden hover:shadow-md transition-shadow flex flex-col">
                    <div class="h-32 md:h-40 bg-slate-100 flex items-center justify-center border-b border-slate-100 relative group">
                        <span class="text-slate-400 text-sm">[實驗影像]</span>
                        <div class="absolute inset-0 bg-indigo-900/0 group-hover:bg-indigo-900/10 transition-colors"></div>
                    </div>
                    <div class="p-4 md:p-5 flex-grow">
                        <h3 class="font-bold text-base md:text-lg text-slate-800 mb-2 card-t"><?php echo htmlspecialchars($item['title']); ?></h3>
                        <p class="text-slate-600 text-xs md:text-sm leading-relaxed mb-4 card-d" data-zh="點擊進入模擬實驗" data-en="Click to enter simulation">點擊進入模擬實驗</p>
                    </div>
                    <div class="px-4 py-2 md:px-5 md:py-3 bg-slate-50 border-t border-slate-100">
                        <p class="text-[10px] md:text-[11px] text-slate-400 font-medium tracking-wide update-text" data-zh="最後更新日期：2025-12-21" data-en="Last Updated: 2025-12-21">最後更新日期：2025-12-21</p>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </main>
    </div>

    <script>
        let currentLang = 'zh';
        const categoryData = <?php echo json_encode($groupedData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
        const categoryMap = <?php echo json_encode($categoryMap, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

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
            
            breadcrumbParent.textContent = currentLang === 'zh' ? categoryZh : categoryEn;
            pageTitle.textContent = (currentLang === 'zh' ? categoryZh : categoryEn) + (currentLang === 'zh' ? '模擬實驗' : ' Simulations');
            
            // 生成卡片
            container.innerHTML = items.map(item => `
                <a href="${escapeHtml(item.url)}" class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden hover:shadow-md transition-shadow flex flex-col">
                    <div class="h-32 md:h-40 bg-slate-100 flex items-center justify-center border-b border-slate-100 relative group">
                        <span class="text-slate-400 text-sm">[實驗影像]</span>
                        <div class="absolute inset-0 bg-indigo-900/0 group-hover:bg-indigo-900/10 transition-colors"></div>
                    </div>
                    <div class="p-4 md:p-5 flex-grow">
                        <h3 class="font-bold text-base md:text-lg text-slate-800 mb-2 card-t">${escapeHtml(item.title)}</h3>
                        <p class="text-slate-600 text-xs md:text-sm leading-relaxed mb-4 card-d" data-zh="點擊進入模擬實驗" data-en="Click to enter simulation">${currentLang === 'zh' ? '點擊進入模擬實驗' : 'Click to enter simulation'}</p>
                    </div>
                    <div class="px-4 py-2 md:px-5 md:py-3 bg-slate-50 border-t border-slate-100">
                        <p class="text-[10px] md:text-[11px] text-slate-400 font-medium tracking-wide update-text" data-zh="最後更新日期：2025-12-21" data-en="Last Updated: 2025-12-21">${currentLang === 'zh' ? '最後更新日期：2025-12-21' : 'Last Updated: 2025-12-21'}</p>
                    </div>
                </a>
            `).join('');
            
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

        function updateUI() {
            const texts = {
                zh: { title: "物理模擬實驗平台", core: "核心單元 Compulsory", elective: "選修單元 Elective" },
                en: { title: "Physics Sim Platform", core: "Compulsory Part", elective: "Elective Part" }
            };
            document.getElementById('app-title').innerText = texts[currentLang].title;
            document.getElementById('core-label').innerText = texts[currentLang].core;
            document.getElementById('elective-label').innerText = texts[currentLang].elective;

            document.querySelectorAll('.main-label, .sub-label, .card-t, .card-d, .update-text').forEach(el => {
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

