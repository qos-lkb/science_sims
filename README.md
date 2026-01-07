# 科學模擬實驗平台 | Science Simulations Platform

一個專為香港中學文憑試（HKDSE）及中學科學課程設計的互動式科學模擬實驗網站。本平台提供物理、化學、生物、綜合科學及天文學等多個學科的互動模擬實驗，幫助學生更直觀地理解科學概念。

An interactive science simulation platform designed for the Hong Kong Diploma of Secondary Education (HKDSE) and secondary school science curriculum. This platform provides interactive simulations across multiple subjects including Physics, Chemistry, Biology, Integrated Science, and Astronomy to help students better understand scientific concepts.

## 📋 專案簡介 | Project Overview

本專案是一個教育網站，收錄了多個獨立的 HTML 模擬實驗檔案，每個檔案實現特定的科學概念視覺化。網站支援兩種部署模式：

This project is an educational website hosting a collection of standalone HTML simulation files, each implementing a specific scientific concept visualization. The site supports two deployment modes:

- **PHP 模式**：從 CSV 資料檔動態生成內容（需要 PHP 伺服器）
- **靜態模式**：純 HTML/CSS/JavaScript 檔案（可在 GitHub Pages 上運行）

- **PHP Mode**: Dynamic content generation from CSV data files (requires PHP server)
- **Static Mode**: Pure HTML/CSS/JavaScript files (works on GitHub Pages)

## ✨ 功能特色 | Features

- 🌐 **雙語支援**：完整的中文（繁體）和英文介面
- 📱 **響應式設計**：適配桌面、平板和手機裝置
- 🎯 **分類導航**：按學科和單元組織的清晰分類系統
- 🖼️ **模態視窗**：模擬實驗在彈出視窗中運行，提供沉浸式體驗
- 📸 **截圖功能**：可將模擬實驗截圖並下載為 PNG 檔案
- 💾 **源碼下載**：每個模擬實驗的源碼可直接下載
- 🔍 **搜尋與篩選**：快速找到所需的模擬實驗

- 🌐 **Bilingual Support**: Complete Traditional Chinese and English interfaces
- 📱 **Responsive Design**: Optimized for desktop, tablet, and mobile devices
- 🎯 **Categorized Navigation**: Clear categorization system organized by subject and unit
- 🖼️ **Modal Interface**: Simulations run in popup windows for an immersive experience
- 📸 **Screenshot Feature**: Capture and download simulation screenshots as PNG files
- 💾 **Source Code Download**: Direct download of source code for each simulation
- 🔍 **Search & Filter**: Quickly find the desired simulation

## 🛠️ 技術棧 | Technology Stack

### 核心技術 | Core Technologies

- **HTML5**：語義化標記
- **CSS3**：自訂樣式與 Tailwind CSS 工具類
- **Vanilla JavaScript**：無需框架（部分模擬使用 React 獨立版本）
- **PHP**（可選）：伺服器端處理，用於動態內容生成

### 外部依賴（CDN）| External Dependencies (CDN)

| 函式庫 | 用途 | 版本 |
|--------|------|------|
| Tailwind CSS | 樣式框架 | 最新版（CDN） |
| Three.js | 3D 圖形渲染 | r128, 0.154.0, 0.160.0 |
| Chart.js | 資料視覺化 | 最新版 |
| MathJax | 數學公式渲染 | 3.x |
| React | UI 框架（部分模擬） | 18（獨立版本） |
| Babel Standalone | JSX 轉譯 | 最新版 |
| html2canvas | 截圖功能 | 1.4.1 |

## 📁 專案結構 | Project Structure

```
science_sims/
├── index.html              # 主頁面（英文，標籤式導航）
├── index.php               # 動態主頁面（PHP，CSV 驅動，模態介面）
├── index.csv               # 模擬實驗資料來源
├── markdown_reader.php      # Markdown 檔案讀取器
├── architecture.md         # 架構文件
├── README.md               # 本檔案
│
├── physics/                # 物理模擬（HKDSE 課程）
│   ├── 01/                # 單元 1：熱與氣體
│   ├── 02/                # 單元 2：力與運動
│   ├── 03a/               # 單元 3a：光學（反射與折射）
│   ├── 03b/               # 單元 3b：波干涉與繞射
│   ├── 04b/               # 單元 4b：電磁學
│   ├── 05/                # 單元 5：放射性
│   ├── e01/               # 選修 1：天文學
│   ├── e02/               # 選修 2：原子物理
│   └── e03/               # 選修 3：能量與能源使用
│
├── chemistry/              # 化學模擬
├── biology/                # 生物學模擬
├── science/                # 綜合科學模擬
├── astronomy/              # 天文學模擬
├── s4_physics/            # S4 物理課程模擬
├── other/                  # 其他模擬
├── geography/              # 地理模擬
├── music/                  # 音樂模擬
└── travel/                 # 旅行相關內容
```

## 🚀 快速開始 | Quick Start

### 本地開發 | Local Development

#### 靜態模式（推薦用於 GitHub Pages）

1. 克隆或下載此儲存庫
2. 使用任何本地伺服器開啟 `index.html`
   ```bash
   # 使用 Python
   python -m http.server 8000
   
   # 或使用 Node.js
   npx serve
   ```
3. 在瀏覽器中訪問 `http://localhost:8000`

#### PHP 模式

1. 確保已安裝 PHP 7.4+ 和 Web 伺服器（Apache/Nginx）
2. 將檔案上傳到 PHP 伺服器
3. 確保 `index.csv` 可讀取
4. 訪問 `index.php`

### 部署 | Deployment

#### GitHub Pages（靜態模式）

1. 將儲存庫推送到 GitHub
2. 在儲存庫設定中啟用 GitHub Pages
3. 選擇 `main` 分支作為來源
4. 網站將自動部署到 `https://[username].github.io/[repository-name]`

**注意**：GitHub Pages 不支援 PHP，請使用 `index.html` 而非 `index.php`

#### PHP 伺服器部署

1. 將所有檔案上傳到 PHP 伺服器
2. 確保 `index.csv` 檔案可讀取
3. 驗證 PHP 版本相容性（建議 7.4+）
4. 測試 `index.php` 和 `markdown_reader.php`

## 📝 新增模擬實驗 | Adding a New Simulation

1. **建立 HTML 檔案**：在適當的目錄中建立新的 HTML 檔案
2. **包含 CDN 依賴**：在 `<head>` 中加入必要的 CDN 連結
   ```html
   <script src="https://cdn.tailwindcss.com"></script>
   <!-- 根據需要添加其他依賴 -->
   ```
3. **結構化內容**：
   - 標題列
   - 主要內容區域
   - 控制項/輸入（如為互動式）
   - 視覺化畫布/容器
4. **更新 CSV**（如使用 `index.php`）：
   - 開啟 `index.csv`
   - 新增一行，包含：類別、標題、URL、截圖路徑、中英文翻譯、最後更新日期
5. **測試**：在多個瀏覽器中測試
6. **提交**：提交並推送變更

## 🌐 瀏覽器相容性 | Browser Compatibility

- **現代瀏覽器**：Chrome、Firefox、Safari、Edge（最新版本）
- **ES6+ 功能**：箭頭函數、模板字面量、解構賦值
- **WebGL**：Three.js 模擬需要
- **Canvas API**：Chart.js 和自訂視覺化需要

## 📄 授權 | License

本專案採用 [Creative Commons Attribution 4.0 International License](https://creativecommons.org/licenses/by/4.0/) (CC BY 4.0) 授權。

This project is licensed under the [Creative Commons Attribution 4.0 International License](https://creativecommons.org/licenses/by/4.0/) (CC BY 4.0).

## 👤 維護者 | Maintainer

**Mr. Bryan Leung**

- 版權 © Mr. Bryan Leung
- Copyright © Mr. Bryan Leung

## 📚 相關文件 | Related Documentation

- [架構文件](architecture.md) - 詳細的技術架構說明
- [開發指南](rule.md) - 開發規範與指南
- [提示模板](prompt.md) - 用於建立新模擬實驗的提示模板

## 🔗 相關連結 | Links

- **儲存庫**：`https://github.com/qos-lkb/qos-lkb.github.io`
- **線上網站**：`https://qos-lkb.github.io`
- **其他連結**：請參閱 `link.txt`

## 📅 更新記錄 | Changelog

**最後更新**：2026-01-01  
**Last Updated**: 2026-01-01

---

如有任何問題或建議，歡迎提出 Issue 或 Pull Request！

If you have any questions or suggestions, please feel free to open an Issue or Pull Request!
