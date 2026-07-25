<header class="bg-white border-b border-gray-200 px-8 py-4 sticky top-0 z-10">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Database Backup</h1>
            <p class="text-sm text-gray-500 mt-0.5">Export your database in various formats</p>
        </div>
    </div>
</header>

<main class="p-8">
    <div class="max-w-2xl">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Backup Options</h2>

            <div class="mb-5">
                <label class="block text-sm font-medium text-gray-700 mb-2">Content</label>
                <div class="flex gap-3">
                    <label class="flex items-center gap-2 px-4 py-2.5 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50 transition-colors">
                        <input type="radio" name="content" value="structure" class="text-blue-600 focus:ring-blue-500">
                        <span class="text-sm text-gray-700">Structure Only</span>
                    </label>
                    <label class="flex items-center gap-2 px-4 py-2.5 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50 transition-colors">
                        <input type="radio" name="content" value="data" class="text-blue-600 focus:ring-blue-500">
                        <span class="text-sm text-gray-700">Data Only</span>
                    </label>
                    <label class="flex items-center gap-2 px-4 py-2.5 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50 transition-colors">
                        <input type="radio" name="content" value="both" checked class="text-blue-600 focus:ring-blue-500">
                        <span class="text-sm text-gray-700">Structure & Data</span>
                    </label>
                </div>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">File Format</label>
                <div class="flex gap-3">
                    <label class="flex items-center gap-2 px-4 py-2.5 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50 transition-colors">
                        <input type="radio" name="format" value="sql" checked class="text-blue-600 focus:ring-blue-500">
                        <div>
                            <span class="text-sm font-medium text-gray-700">SQL</span>
                            <p class="text-xs text-gray-500">MySQL / MariaDB</p>
                        </div>
                    </label>
                    <label class="flex items-center gap-2 px-4 py-2.5 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50 transition-colors">
                        <input type="radio" name="format" value="tsql" class="text-blue-600 focus:ring-blue-500">
                        <div>
                            <span class="text-sm font-medium text-gray-700">T-SQL</span>
                            <p class="text-xs text-gray-500">SQL Server</p>
                        </div>
                    </label>
                    <label class="flex items-center gap-2 px-4 py-2.5 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50 transition-colors">
                        <input type="radio" name="format" value="accessdb" class="text-blue-600 focus:ring-blue-500">
                        <div>
                            <span class="text-sm font-medium text-gray-700">Access DB</span>
                            <p class="text-xs text-gray-500">.accdb file copy</p>
                        </div>
                    </label>
                </div>
            </div>

            <div id="backupInfo" class="bg-blue-50 border border-blue-100 rounded-lg p-4 mb-6 hidden">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-blue-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <div id="backupInfoText" class="text-sm text-blue-800"></div>
                </div>
            </div>

            <button id="backupBtn" onclick="downloadBackup()" class="w-full bg-green-500 hover:bg-blue-600 text-white font-semibold py-3 px-5 rounded-lg shadow hover:shadow-lg transition-all flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Download Backup
            </button>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-3">Format Descriptions</h2>
            <div class="space-y-3 text-sm text-gray-600">
                <div class="flex items-start gap-3">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-green-100 text-green-800 shrink-0">SQL</span>
                    <p>Standard MySQL/MariaDB dump. Contains <code class="bg-gray-100 px-1 rounded">CREATE TABLE</code> and <code class="bg-gray-100 px-1 rounded">INSERT INTO</code> statements compatible with MySQL 5.7+ and MariaDB 10.3+.</p>
                </div>
                <div class="flex items-start gap-3">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-blue-100 text-blue-800 shrink-0">T-SQL</span>
                    <p>SQL Server compatible script. Uses <code class="bg-gray-100 px-1 rounded">NVARCHAR</code>, <code class="bg-gray-100 px-1 rounded">BIT</code>, <code class="bg-gray-100 px-1 rounded">MONEY</code>, and <code class="bg-gray-100 px-1 rounded">IDENTITY</code> types. Execute in SQL Server Management Studio.</p>
                </div>
                <div class="flex items-start gap-3">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-purple-100 text-purple-800 shrink-0">.accdb</span>
                    <p>A direct copy of the Access database file. Opens with Microsoft Access, LibreOffice Base, or any OLEDB/ACE compatible tool.</p>
                </div>
            </div>
        </div>
    </div>
</main>


