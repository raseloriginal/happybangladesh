<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Manager Activities</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">View logs of all actions performed by managers.</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-5 mb-8">
        <form method="GET" action="<?= url('admin/manager-logs') ?>" class="flex flex-col sm:flex-row gap-4 items-end">
            <div class="flex-1 w-full sm:w-auto">
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Date From</label>
                <input type="date" name="date_from" value="<?= e($dateFrom) ?>" 
                       class="w-full bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5 dark:bg-slate-900 dark:border-slate-600 dark:placeholder-slate-400 dark:text-white">
            </div>
            <div class="flex-1 w-full sm:w-auto">
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Date To</label>
                <input type="date" name="date_to" value="<?= e($dateTo) ?>" 
                       class="w-full bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5 dark:bg-slate-900 dark:border-slate-600 dark:placeholder-slate-400 dark:text-white">
            </div>
            <div class="flex-1 w-full sm:w-auto">
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Manager</label>
                <select name="manager_id" class="w-full bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5 dark:bg-slate-900 dark:border-slate-600 dark:placeholder-slate-400 dark:text-white">
                    <option value="">All Managers</option>
                    <?php foreach ($managers as $mgr): ?>
                        <option value="<?= $mgr['id'] ?>" <?= $mgr['id'] == $managerId ? 'selected' : '' ?>>
                            <?= e($mgr['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="w-full sm:w-auto flex gap-2">
                <button type="submit" class="w-full sm:w-auto px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm focus:ring-4 focus:ring-blue-300 dark:focus:ring-blue-800">
                    Filter
                </button>
                <a href="<?= url('admin/manager-logs') ?>" class="w-full sm:w-auto px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-medium rounded-lg transition-colors text-center dark:bg-slate-700 dark:hover:bg-slate-600 dark:text-slate-200">
                    Clear
                </a>
            </div>
        </form>
    </div>

    <!-- Logs Table -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-slate-500 dark:text-slate-400">
                <thead class="text-xs text-slate-700 uppercase bg-slate-50 dark:bg-slate-900/50 dark:text-slate-300 border-b border-slate-200 dark:border-slate-700">
                    <tr>
                        <th scope="col" class="px-6 py-4 font-semibold">Time</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Manager</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Action</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Description</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Target ID</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                    <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-slate-500 dark:text-slate-400">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="w-12 h-12 mb-3 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span class="text-base font-medium">No activity logs found</span>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-slate-900 dark:text-white font-medium">
                                        <?= date('h:i A', strtotime($log['created_at'])) ?>
                                    </div>
                                    <div class="text-xs text-slate-500">
                                        <?= date('M d, Y', strtotime($log['created_at'])) ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900/50 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold text-xs">
                                            <?= \Helpers::initials($log['manager_name'] ?: 'M') ?>
                                        </div>
                                        <span class="font-medium text-slate-900 dark:text-white">
                                            <?= e($log['manager_name'] ?: 'Unknown') ?>
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                        <?= e($log['action_type']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 min-w-[300px]">
                                    <span class="text-slate-700 dark:text-slate-300"><?= e($log['description']) ?></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-slate-500 font-mono text-xs">
                                    <?= $log['target_id'] ? e($log['target_id']) : '-' ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <?php if ($totalPages > 1): ?>
            <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 flex items-center justify-between">
                <span class="text-sm text-slate-500 dark:text-slate-400">
                    Page <span class="font-medium text-slate-900 dark:text-white"><?= $page ?></span> of <span class="font-medium text-slate-900 dark:text-white"><?= $totalPages ?></span>
                </span>
                <div class="flex gap-2">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?= $page - 1 ?>&date_from=<?= urlencode($dateFrom) ?>&date_to=<?= urlencode($dateTo) ?>&manager_id=<?= urlencode($managerId) ?>" 
                           class="px-3 py-1.5 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 dark:bg-slate-800 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-700 transition-colors">
                            Previous
                        </a>
                    <?php endif; ?>
                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?= $page + 1 ?>&date_from=<?= urlencode($dateFrom) ?>&date_to=<?= urlencode($dateTo) ?>&manager_id=<?= urlencode($managerId) ?>" 
                           class="px-3 py-1.5 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 dark:bg-slate-800 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-700 transition-colors">
                            Next
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
