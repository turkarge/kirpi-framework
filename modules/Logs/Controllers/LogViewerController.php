<?php

declare(strict_types=1);

namespace Modules\Logs\Controllers;

use Core\Auth\DashboardShellRenderer;
use Core\Auth\Facades\Auth;
use Core\Http\Request;
use Core\Http\Response;

final class LogViewerController
{
    public function index(): Response
    {
        return $this->renderPage(
            title: (string) __('logs.meta_title'),
            headerHtml: $this->headerHtml(),
            bodyHtml: $this->bodyHtml()
        );
    }

    private function renderPage(string $title, string $headerHtml, string $bodyHtml): Response
    {
        $user = Auth::guard('session')->user();
        $name = (string) ($user?->name ?? 'User');
        $email = (string) ($user?->email ?? '-');
        $appName = (string) config('app.name', 'Kirpi Framework');

        $renderer = new DashboardShellRenderer();
        $request = app(Request::class);
        $html = $renderer->render(
            title: $title,
            currentPath: $request->path(),
            appName: $appName,
            userName: $name,
            userEmail: $email,
            headerHtml: $headerHtml,
            bodyHtml: $bodyHtml
        );

        if ($html === null) {
            return Response::make('Logs template bulunamadi.', 500, ['Content-Type' => 'text/plain; charset=utf-8']);
        }

        return Response::make($html, 200, ['Content-Type' => 'text/html; charset=utf-8']);
    }

    private function headerHtml(): string
    {
        $pretitle = $this->e(__('logs.pretitle'));
        $title = $this->e(__('logs.title'));
        $subtitle = $this->e(__('logs.subtitle'));

        return <<<HTML
      <!-- BEGIN PAGE HEADER -->
      <div class="page-header d-print-none">
        <div class="container-xl">
          <div class="row g-2 align-items-center">
            <div class="col">
              <div class="page-pretitle">{$pretitle}</div>
              <h2 class="page-title">{$title}</h2>
              <div class="text-secondary">{$subtitle}</div>
            </div>
          </div>
        </div>
      </div>
      <!-- END PAGE HEADER -->
HTML;
    }

    private function bodyHtml(): string
    {
        $request = app(Request::class);
        $file = trim((string) $request->get('file', ''));
        $lines = (int) $request->integer('lines', 200);
        $lines = max(50, min($lines, 1000));

        $files = $this->logFiles();
        if ($files === []) {
            $empty = $this->e(__('logs.table.empty'));
            return <<<HTML
      <!-- BEGIN PAGE BODY -->
      <div class="page-body">
        <div class="container-xl">
          <div class="alert alert-warning mb-0">{$empty}</div>
        </div>
      </div>
      <!-- END PAGE BODY -->
HTML;
        }

        $selectedFile = isset($files[$file]) ? $file : array_key_first($files);
        $selectedMeta = $files[$selectedFile];
        $content = $this->tailFile((string) $selectedMeta['path'], $lines);
        $entries = $this->parseEntries($content);

        $fileOptions = $this->fileOptionsHtml($files, (string) $selectedFile);
        $safeLines = $this->e((string) $lines);
        $safeRows = $this->e((string) count(explode("\n", trim($content))));
        $safeParsedRows = $this->e((string) count($entries));
        $safeSize = $this->e($this->formatBytes((int) $selectedMeta['size']));
        $safeUpdated = $this->e($this->formatDateTime((int) $selectedMeta['mtime']));
        $safeContent = $this->e($content);
        $tableRows = $this->tableRowsHtml($entries);
        $channelOptions = $this->channelOptionsHtml($entries);
        $levelOptions = $this->levelOptionsHtml($entries);

        $fileLabel = $this->e(__('logs.form.file'));
        $linesLabel = $this->e(__('logs.form.lines'));
        $refresh = $this->e(__('logs.actions.refresh'));
        $download = $this->e(__('logs.actions.download'));
        $searchLabel = $this->e(__('logs.form.search'));
        $channelLabel = $this->e(__('logs.form.channel'));
        $levelLabel = $this->e(__('logs.form.level'));
        $all = $this->e(__('logs.form.all'));
        $statsTitle = $this->e(__('logs.stats.title'));
        $rowsLabel = $this->e(__('logs.stats.rows'));
        $parsedRowsLabel = $this->e(__('logs.stats.parsed_rows'));
        $sizeLabel = $this->e(__('logs.stats.size'));
        $updatedLabel = $this->e(__('logs.stats.updated_at'));
        $outputTitle = $this->e(__('logs.output.title'));
        $rawTitle = $this->e(__('logs.output.raw_title'));
        $tableTitle = $this->e(__('logs.output.table_title'));
        $tableCountLabel = $this->e(__('logs.output.filtered_rows'));

        $timeTh = $this->e(__('logs.table.time'));
        $channelTh = $this->e(__('logs.table.channel'));
        $levelTh = $this->e(__('logs.table.level'));
        $messageTh = $this->e(__('logs.table.message'));
        $requestIdTh = $this->e(__('logs.table.request_id'));
        $pathTh = $this->e(__('logs.table.path'));
        $statusTh = $this->e(__('logs.table.status'));
        $durationTh = $this->e(__('logs.table.duration_ms'));
        $userTh = $this->e(__('logs.table.user_id'));
        $emptyRows = $this->e(__('logs.table.empty_rows'));

        $downloadHref = '/logs?file=' . rawurlencode((string) $selectedFile) . '&lines=1000';

        return <<<HTML
      <!-- BEGIN PAGE BODY -->
      <div class="page-body">
        <div class="container-xl">
          <div class="row row-cards">
            <div class="col-12 col-lg-4">
              <div class="card">
                <div class="card-header">
                  <h3 class="card-title">{$statsTitle}</h3>
                </div>
                <div class="card-body">
                  <form method="GET" action="/logs" class="row g-3">
                    <div class="col-12">
                      <label class="form-label">{$fileLabel}</label>
                      <select class="form-select" name="file">
{$fileOptions}
                      </select>
                    </div>
                    <div class="col-12">
                      <label class="form-label">{$linesLabel}</label>
                      <input type="number" class="form-control" name="lines" min="50" max="1000" step="50" value="{$safeLines}">
                    </div>
                    <div class="col-12">
                      <button class="btn btn-primary w-100" type="submit">{$refresh}</button>
                    </div>
                  </form>
                  <hr>
                  <dl class="row mb-0">
                    <dt class="col-5">{$rowsLabel}</dt>
                    <dd class="col-7">{$safeRows}</dd>
                    <dt class="col-5">{$parsedRowsLabel}</dt>
                    <dd class="col-7">{$safeParsedRows}</dd>
                    <dt class="col-5">{$sizeLabel}</dt>
                    <dd class="col-7">{$safeSize}</dd>
                    <dt class="col-5">{$updatedLabel}</dt>
                    <dd class="col-7">{$safeUpdated}</dd>
                  </dl>
                  <a href="{$downloadHref}" class="btn btn-outline-primary w-100 mt-3">{$download}</a>
                </div>
              </div>
            </div>
            <div class="col-12 col-lg-8">
              <div class="card">
                <div class="card-header">
                  <h3 class="card-title">{$outputTitle}</h3>
                </div>
                <div class="card-header border-top">
                  <ul class="nav nav-tabs card-header-tabs" data-bs-toggle="tabs">
                    <li class="nav-item">
                      <a href="#logs-table-pane" class="nav-link active">{$tableTitle}</a>
                    </li>
                    <li class="nav-item">
                      <a href="#logs-raw-pane" class="nav-link">{$rawTitle}</a>
                    </li>
                  </ul>
                </div>
                <div class="card-body border-bottom">
                  <div class="row g-2">
                    <div class="col-12 col-md-5">
                      <label class="form-label mb-1">{$searchLabel}</label>
                      <input type="text" id="log-search" class="form-control" placeholder="{$searchLabel}">
                    </div>
                    <div class="col-6 col-md-3">
                      <label class="form-label mb-1">{$channelLabel}</label>
                      <select id="log-channel" class="form-select">
                        <option value="">{$all}</option>
{$channelOptions}
                      </select>
                    </div>
                    <div class="col-6 col-md-2">
                      <label class="form-label mb-1">{$levelLabel}</label>
                      <select id="log-level" class="form-select">
                        <option value="">{$all}</option>
{$levelOptions}
                      </select>
                    </div>
                    <div class="col-12 col-md-2 d-flex align-items-end">
                      <div class="text-secondary small">{$tableCountLabel}: <span id="log-row-count">0</span></div>
                    </div>
                  </div>
                </div>
                <div class="card-body p-0 tab-content">
                  <div class="tab-pane active show" id="logs-table-pane">
                    <div class="table-responsive" style="max-height: 70vh;">
                      <table class="table table-vcenter table-sm card-table mb-0" id="logs-table">
                        <thead>
                          <tr>
                            <th><button class="btn btn-link p-0 text-reset text-decoration-none" data-sort="timestamp" type="button">{$timeTh}</button></th>
                            <th><button class="btn btn-link p-0 text-reset text-decoration-none" data-sort="channel" type="button">{$channelTh}</button></th>
                            <th><button class="btn btn-link p-0 text-reset text-decoration-none" data-sort="level" type="button">{$levelTh}</button></th>
                            <th><button class="btn btn-link p-0 text-reset text-decoration-none" data-sort="message" type="button">{$messageTh}</button></th>
                            <th>{$requestIdTh}</th>
                            <th>{$pathTh}</th>
                            <th><button class="btn btn-link p-0 text-reset text-decoration-none" data-sort="status" type="button">{$statusTh}</button></th>
                            <th><button class="btn btn-link p-0 text-reset text-decoration-none" data-sort="duration_ms" type="button">{$durationTh}</button></th>
                            <th><button class="btn btn-link p-0 text-reset text-decoration-none" data-sort="user_id" type="button">{$userTh}</button></th>
                          </tr>
                        </thead>
                        <tbody id="logs-table-body">
{$tableRows}
                        </tbody>
                      </table>
                    </div>
                  </div>
                  <div class="tab-pane" id="logs-raw-pane">
                    <pre class="m-0 p-3" style="max-height: 70vh; overflow: auto; background: var(--tblr-bg-surface-secondary); color: var(--tblr-body-color); border-top: 1px solid var(--tblr-border-color);">{$safeContent}</pre>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <script>
        (() => {
          const searchInput = document.getElementById('log-search');
          const channelInput = document.getElementById('log-channel');
          const levelInput = document.getElementById('log-level');
          const body = document.getElementById('logs-table-body');
          const rowCount = document.getElementById('log-row-count');
          const table = document.getElementById('logs-table');
          if (!searchInput || !channelInput || !levelInput || !body || !rowCount || !table) return;

          const rows = () => Array.from(body.querySelectorAll('tr[data-log-row="1"]'));
          const syncCount = () => {
            const visible = rows().filter((row) => row.style.display !== 'none').length;
            rowCount.textContent = String(visible);
          };

          const applyFilters = () => {
            const q = searchInput.value.trim().toLowerCase();
            const channel = channelInput.value.trim().toLowerCase();
            const level = levelInput.value.trim().toLowerCase();

            rows().forEach((row) => {
              const text = (row.dataset.search || '').toLowerCase();
              const rowChannel = (row.dataset.channel || '').toLowerCase();
              const rowLevel = (row.dataset.level || '').toLowerCase();

              const matchQ = q === '' || text.includes(q);
              const matchChannel = channel === '' || rowChannel === channel;
              const matchLevel = level === '' || rowLevel === level;
              row.style.display = (matchQ && matchChannel && matchLevel) ? '' : 'none';
            });

            syncCount();
          };

          let sortKey = 'timestamp';
          let sortDirection = 'desc';

          const sortRows = (key, direction) => {
            const allRows = rows();
            allRows.sort((a, b) => {
              const av = a.dataset[key] ?? '';
              const bv = b.dataset[key] ?? '';
              const an = Number(av);
              const bn = Number(bv);
              let cmp = 0;
              if (!Number.isNaN(an) && !Number.isNaN(bn) && av !== '' && bv !== '') {
                cmp = an - bn;
              } else {
                cmp = String(av).localeCompare(String(bv), undefined, { sensitivity: 'base' });
              }
              return direction === 'asc' ? cmp : -cmp;
            });

            allRows.forEach((row) => body.appendChild(row));
            applyFilters();
          };

          table.querySelectorAll('[data-sort]').forEach((btn) => {
            btn.addEventListener('click', () => {
              const key = btn.getAttribute('data-sort');
              if (!key) return;

              if (sortKey === key) {
                sortDirection = sortDirection === 'asc' ? 'desc' : 'asc';
              } else {
                sortKey = key;
                sortDirection = key === 'timestamp' ? 'desc' : 'asc';
              }
              sortRows(sortKey, sortDirection);
            });
          });

          searchInput.addEventListener('input', applyFilters);
          channelInput.addEventListener('change', applyFilters);
          levelInput.addEventListener('change', applyFilters);

          sortRows(sortKey, sortDirection);
        })();
      </script>
      <!-- END PAGE BODY -->
HTML;
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    private function parseEntries(string $content): array
    {
        $lines = preg_split("/\r\n|\n|\r/", $content) ?: [];
        $entries = [];
        $index = 0;

        foreach ($lines as $line) {
            $raw = trim((string) $line);
            if ($raw === '') {
                continue;
            }

            $entry = $this->parseLine($raw);
            $entry['_index'] = $index++;
            $entries[] = $entry;
        }

        usort($entries, function (array $a, array $b): int {
            $left = (int) ($a['timestamp_unix'] ?? 0);
            $right = (int) ($b['timestamp_unix'] ?? 0);
            if ($left === $right) {
                return ((int) ($b['_index'] ?? 0)) <=> ((int) ($a['_index'] ?? 0));
            }
            return $right <=> $left;
        });

        return $entries;
    }

    /**
     * @return array<string,mixed>
     */
    private function parseLine(string $line): array
    {
        $decoded = json_decode($line, true);
        if (is_array($decoded) && isset($decoded['timestamp'], $decoded['message'])) {
            $context = is_array($decoded['context'] ?? null) ? $decoded['context'] : [];
            $timestamp = (string) ($decoded['timestamp'] ?? '-');
            return [
                'timestamp' => $timestamp,
                'timestamp_unix' => strtotime($timestamp) ?: 0,
                'channel' => (string) ($decoded['channel'] ?? '-'),
                'level' => (string) ($decoded['level'] ?? '-'),
                'message' => (string) ($decoded['message'] ?? ''),
                'request_id' => (string) ($context['request_id'] ?? ''),
                'path' => (string) ($context['path'] ?? ''),
                'status' => (string) ($context['status'] ?? ''),
                'duration_ms' => (string) ($context['duration_ms'] ?? ''),
                'user_id' => (string) ($context['user_id'] ?? ''),
                'raw' => $line,
                'search' => $line,
            ];
        }

        if (preg_match('/^\[(?<ts>[^\]]+)\]\s+(?<channel>[a-zA-Z0-9\-_]+)\.(?<level>[A-Z]+):\s+(?<message>.*)$/', $line, $m) === 1) {
            $message = (string) ($m['message'] ?? '');
            return [
                'timestamp' => (string) ($m['ts'] ?? '-'),
                'timestamp_unix' => strtotime((string) ($m['ts'] ?? '')) ?: 0,
                'channel' => (string) ($m['channel'] ?? '-'),
                'level' => (string) ($m['level'] ?? '-'),
                'message' => $message,
                'request_id' => '',
                'path' => '',
                'status' => '',
                'duration_ms' => '',
                'user_id' => '',
                'raw' => $line,
                'search' => $line,
            ];
        }

        return [
            'timestamp' => '-',
            'timestamp_unix' => 0,
            'channel' => '-',
            'level' => '-',
            'message' => $line,
            'request_id' => '',
            'path' => '',
            'status' => '',
            'duration_ms' => '',
            'user_id' => '',
            'raw' => $line,
            'search' => $line,
        ];
    }

    /**
     * @param array<int,array<string,mixed>> $entries
     */
    private function tableRowsHtml(array $entries): string
    {
        $rows = '';
        foreach ($entries as $entry) {
            $timestamp = (string) ($entry['timestamp'] ?? '-');
            $channel = (string) ($entry['channel'] ?? '-');
            $level = (string) ($entry['level'] ?? '-');
            $message = (string) ($entry['message'] ?? '');
            $requestId = (string) ($entry['request_id'] ?? '');
            $path = (string) ($entry['path'] ?? '');
            $status = (string) ($entry['status'] ?? '');
            $duration = (string) ($entry['duration_ms'] ?? '');
            $userId = (string) ($entry['user_id'] ?? '');
            $search = (string) ($entry['search'] ?? $message);
            $timestampUnix = (string) ((int) ($entry['timestamp_unix'] ?? 0));

            $levelBadge = $this->levelBadge($level);

            $rows .= <<<HTML
                          <tr data-log-row="1"
                              data-search="{$this->e($search)}"
                              data-timestamp="{$this->e($timestampUnix)}"
                              data-channel="{$this->e($channel)}"
                              data-level="{$this->e($level)}"
                              data-message="{$this->e($message)}"
                              data-status="{$this->e($status)}"
                              data-duration_ms="{$this->e($duration)}"
                              data-user_id="{$this->e($userId)}">
                            <td><span class="text-secondary">{$this->e($timestamp)}</span></td>
                            <td><span class="badge bg-azure-lt">{$this->e($channel !== '' ? $channel : '-')}</span></td>
                            <td>{$levelBadge}</td>
                            <td title="{$this->e($message)}">{$this->e($this->truncate($message, 120))}</td>
                            <td><code>{$this->e($requestId !== '' ? $requestId : '-')}</code></td>
                            <td><code>{$this->e($path !== '' ? $path : '-')}</code></td>
                            <td>{$this->e($status !== '' ? $status : '-')}</td>
                            <td>{$this->e($duration !== '' ? $duration : '-')}</td>
                            <td>{$this->e($userId !== '' ? $userId : '-')}</td>
                          </tr>
HTML;
        }

        if ($rows === '') {
            $emptyRows = $this->e(__('logs.table.empty_rows'));
            return <<<HTML
                          <tr>
                            <td colspan="9" class="text-center text-secondary py-4">{$emptyRows}</td>
                          </tr>
HTML;
        }

        return $rows;
    }

    /**
     * @param array<int,array<string,mixed>> $entries
     */
    private function channelOptionsHtml(array $entries): string
    {
        $channels = [];
        foreach ($entries as $entry) {
            $channel = trim((string) ($entry['channel'] ?? ''));
            if ($channel !== '' && $channel !== '-') {
                $channels[$channel] = true;
            }
        }

        $keys = array_keys($channels);
        sort($keys);
        $html = '';
        foreach ($keys as $channel) {
            $safe = $this->e($channel);
            $html .= '                        <option value="' . $safe . '">' . $safe . '</option>' . PHP_EOL;
        }
        return rtrim($html);
    }

    /**
     * @param array<int,array<string,mixed>> $entries
     */
    private function levelOptionsHtml(array $entries): string
    {
        $levels = [];
        foreach ($entries as $entry) {
            $level = strtoupper(trim((string) ($entry['level'] ?? '')));
            if ($level !== '' && $level !== '-') {
                $levels[$level] = true;
            }
        }

        $keys = array_keys($levels);
        sort($keys);
        $html = '';
        foreach ($keys as $level) {
            $safe = $this->e($level);
            $html .= '                        <option value="' . $safe . '">' . $safe . '</option>' . PHP_EOL;
        }
        return rtrim($html);
    }

    private function levelBadge(string $level): string
    {
        $normalized = strtoupper(trim($level));
        $class = match ($normalized) {
            'ERROR', 'CRITICAL', 'ALERT', 'EMERGENCY' => 'bg-red-lt',
            'WARNING' => 'bg-yellow-lt',
            'NOTICE' => 'bg-blue-lt',
            'INFO' => 'bg-green-lt',
            'DEBUG' => 'bg-cyan-lt',
            default => 'bg-secondary-lt',
        };

        $safeLevel = $this->e($normalized !== '' ? $normalized : '-');
        return '<span class="badge ' . $class . '">' . $safeLevel . '</span>';
    }

    private function truncate(string $text, int $max): string
    {
        if (mb_strlen($text, 'UTF-8') <= $max) {
            return $text;
        }

        return mb_substr($text, 0, $max - 1, 'UTF-8') . '…';
    }

    /**
     * @return array<string,array{path:string,size:int,mtime:int}>
     */
    private function logFiles(): array
    {
        $dir = storage_path('logs');
        if (!is_dir($dir)) {
            return [];
        }

        $items = glob($dir . DIRECTORY_SEPARATOR . '*.log') ?: [];
        $result = [];
        foreach ($items as $path) {
            if (!is_file($path)) {
                continue;
            }

            $name = basename($path);
            $result[$name] = [
                'path' => $path,
                'size' => (int) filesize($path),
                'mtime' => (int) filemtime($path),
            ];
        }

        uasort($result, static fn(array $a, array $b): int => $b['mtime'] <=> $a['mtime']);
        return $result;
    }

    /**
     * @param array<string,array{path:string,size:int,mtime:int}> $files
     */
    private function fileOptionsHtml(array $files, string $selected): string
    {
        $html = '';
        foreach ($files as $name => $meta) {
            $sel = $name === $selected ? ' selected' : '';
            $label = $this->e($name . ' (' . $this->formatBytes((int) $meta['size']) . ')');
            $safeName = $this->e($name);
            $html .= '                        <option value="' . $safeName . '"' . $sel . '>' . $label . '</option>' . PHP_EOL;
        }

        return rtrim($html);
    }

    private function tailFile(string $path, int $lines): string
    {
        if (!is_file($path)) {
            return '';
        }

        $file = new \SplFileObject($path, 'r');
        $file->seek(PHP_INT_MAX);
        $last = $file->key();
        $start = max(0, $last - $lines);

        $buffer = [];
        for ($i = $start; $i <= $last; $i++) {
            $file->seek($i);
            $buffer[] = (string) $file->current();
        }

        return trim(implode('', $buffer));
    }

    private function formatDateTime(int $timestamp): string
    {
        if ($timestamp <= 0) {
            return '-';
        }

        $format = (string) config('app.datetime_format', 'd.m.Y H:i');
        return date($format, $timestamp);
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $size = max(0, $bytes);
        $unitIndex = 0;
        while ($size >= 1024 && $unitIndex < count($units) - 1) {
            $size /= 1024;
            $unitIndex++;
        }

        return number_format($size, $unitIndex === 0 ? 0 : 2, '.', '') . ' ' . $units[$unitIndex];
    }

    private function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
