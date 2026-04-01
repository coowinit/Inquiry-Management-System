<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$errors = [];
$warnings = [];
$notes = [];

function add_error(array &$errors, string $message): void { $errors[] = $message; }
function add_warning(array &$warnings, string $message): void { $warnings[] = $message; }
function add_note(array &$notes, string $message): void { $notes[] = $message; }
function rel_path(string $root, string $path): string { return ltrim(str_replace($root, '', $path), DIRECTORY_SEPARATOR); }

function collect_php_files(string $baseDir): array
{
    if (!is_dir($baseDir)) {
        return [];
    }
    $files = [];
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($baseDir, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $fileInfo) {
        if ($fileInfo->isFile() && strtolower($fileInfo->getExtension()) === 'php') {
            $files[] = $fileInfo->getPathname();
        }
    }
    sort($files);
    return $files;
}

function build_class_map(string $root): array
{
    $classMap = [];
    foreach (collect_php_files($root . '/app') as $file) {
        $code = file_get_contents($file) ?: '';
        $namespace = '';
        if (preg_match('~^\s*namespace\s+([^;]+);~m', $code, $nsMatch)) {
            $namespace = trim($nsMatch[1]);
        }
        if (preg_match('~^\s*(?:final\s+|abstract\s+)?class\s+([A-Za-z_][A-Za-z0-9_]*)~m', $code, $classMatch)) {
            $fqcn = $namespace !== '' ? $namespace . '\\' . $classMatch[1] : $classMatch[1];
            $classMap[$fqcn] = $file;
        }
    }
    ksort($classMap);
    return $classMap;
}

function run_php_lint(array $files, string $root, array &$errors, array &$warnings, array &$notes): void
{
    if (!defined('PHP_BINARY') || PHP_BINARY === '') {
        add_warning($warnings, 'PHP_BINARY unavailable, skipped syntax lint.');
        return;
    }

    $linted = 0;
    foreach ($files as $file) {
        $command = escapeshellarg(PHP_BINARY) . ' -l ' . escapeshellarg($file) . ' 2>&1';
        $output = [];
        $exitCode = 0;
        @exec($command, $output, $exitCode);
        if ($exitCode !== 0) {
            add_error($errors, 'Syntax error in ' . rel_path($root, $file) . ': ' . trim(implode("\n", $output)));
        }
        $linted++;
    }
    add_note($notes, 'PHP syntax lint checked ' . $linted . ' file(s).');
}

function check_use_statements(array $classMap, string $root, array &$errors, array &$notes): void
{
    $checked = 0;
    foreach (collect_php_files($root . '/app') as $file) {
        $code = file_get_contents($file) ?: '';
        if (!preg_match_all('~^\s*use\s+(App\\\\[^;]+);~m', $code, $matches)) {
            continue;
        }
        foreach ($matches[1] as $fqcn) {
            $checked++;
            if (!isset($classMap[$fqcn])) {
                add_error($errors, 'Missing App class for use statement: ' . $fqcn . ' in ' . rel_path($root, $file));
            }
        }
    }
    add_note($notes, 'App\\ use statements checked: ' . $checked . '.');
}

function check_routes(string $root, array $classMap, array &$errors, array &$notes): void
{
    $routeFile = $root . '/public/index.php';
    $code = file_get_contents($routeFile) ?: '';
    $aliases = [];
    if (preg_match_all('~^\s*use\s+([^;]+);~m', $code, $useMatches, PREG_SET_ORDER)) {
        foreach ($useMatches as $useMatch) {
            $fqcn = trim($useMatch[1]);
            $alias = substr($fqcn, (int) strrpos($fqcn, '\\') + 1);
            $aliases[$alias] = $fqcn;
        }
    }
    preg_match_all('~\$router->(?:get|post|put|delete|options)\(\s*[\'\"]([^\'\"]+)[\'\"]\s*,\s*\[\s*([A-Za-z_\\\\]+)::class\s*,\s*[\'\"]([^\'\"]+)[\'\"]\s*\]~', $code, $matches, PREG_SET_ORDER);

    $checked = 0;
    foreach ($matches as $match) {
        $path = $match[1];
        $rawClass = $match[2];
        $method = $match[3];
        $fqcn = $aliases[$rawClass] ?? (str_contains($rawClass, '\\') ? $rawClass : 'App\\Controllers\\' . $rawClass);
        $checked++;

        if (!isset($classMap[$fqcn])) {
            add_error($errors, 'Route target class missing for [' . $path . ']: ' . $fqcn);
            continue;
        }

        $controllerCode = file_get_contents($classMap[$fqcn]) ?: '';
        if (!preg_match('~function\s+' . preg_quote($method, '~') . '\s*\(~', $controllerCode)) {
            add_error($errors, 'Route target method missing for [' . $path . ']: ' . $fqcn . '::' . $method);
        }
    }

    add_note($notes, 'Routes checked: ' . $checked . '.');
}

function check_views(string $root, array &$errors, array &$notes): void
{
    $checked = 0;
    foreach (collect_php_files($root . '/app/Controllers') as $file) {
        $code = file_get_contents($file) ?: '';
        if (!preg_match_all('~->view\(\s*[\'\"]([^\'\"]+)[\'\"]~', $code, $matches, PREG_OFFSET_CAPTURE)) {
            continue;
        }

        foreach ($matches[1] as $index => $captured) {
            $view = $captured[0];
            $offset = $matches[0][$index][1];
            $statement = substr($code, $offset, 500);
            $statement = strstr($statement, ';', true) ?: $statement;
            $layout = 'layouts/main';
            if (preg_match('~[\'\"](layouts/[^\'\"]+)[\'\"]\s*\)~', $statement, $layoutMatch)) {
                $layout = $layoutMatch[1];
            }
            $checked++;

            $viewPath = $root . '/resources/views/' . $view . '.php';
            $layoutPath = $root . '/resources/views/' . $layout . '.php';
            if (!file_exists($viewPath)) {
                add_error($errors, 'Missing view file: ' . rel_path($root, $viewPath) . ' referenced from ' . rel_path($root, $file));
            }
            if (!file_exists($layoutPath)) {
                add_error($errors, 'Missing layout file: ' . rel_path($root, $layoutPath) . ' referenced from ' . rel_path($root, $file));
            }
        }
    }
    add_note($notes, 'Controller view references checked: ' . $checked . '.');
}

function schema_tables(string $schemaPath): array
{
    $schema = file_get_contents($schemaPath) ?: '';
    preg_match_all('~CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?`?([a-zA-Z0-9_]+)`?~i', $schema, $matches);
    $tables = array_map('strtolower', $matches[1] ?? []);
    sort($tables);
    return array_values(array_unique($tables));
}

function sql_string_literals(string $file): array
{
    $code = file_get_contents($file) ?: '';
    $tokens = token_get_all($code);
    $strings = [];
    foreach ($tokens as $token) {
        if (is_array($token) && $token[0] === T_CONSTANT_ENCAPSED_STRING) {
            $raw = $token[1];
            $quote = $raw[0] ?? '\'';
            $body = substr($raw, 1, -1);
            if ($quote === '\'') {
                $body = str_replace(["\\\\", "\\'"], ["\\", "'"], $body);
            } else {
                $body = stripcslashes($body);
            }
            $strings[] = $body;
        }
    }
    return $strings;
}

function check_sql_files(string $root, array &$errors, array &$warnings, array &$notes): void
{
    $dbDir = $root . '/database';
    $required = ['schema.sql', 'seed.sql', 'upgrade-v0.3.0.sql', 'upgrade-v0.4.0.sql', 'upgrade-v0.5.0.sql', 'upgrade-v0.6.0.sql', 'upgrade-v0.7.0.sql', 'upgrade-v0.8.0.sql', 'upgrade-v0.8.1.sql', 'upgrade-v0.8.2.sql'];
    foreach ($required as $file) {
        if (!file_exists($dbDir . '/' . $file)) {
            add_error($errors, 'Missing database file: database/' . $file);
        }
    }

    $schemaTables = schema_tables($dbDir . '/schema.sql');
    if ($schemaTables === []) {
        add_error($errors, 'No tables detected in database/schema.sql');
        return;
    }

    $sqlTableRefs = [];
    $sources = array_merge(collect_php_files($root . '/app/Models'), collect_php_files($root . '/app/Services'));
    foreach ($sources as $file) {
        foreach (sql_string_literals($file) as $string) {
            if (!preg_match('~\b(SELECT|INSERT|UPDATE|DELETE)\b~i', $string)) {
                continue;
            }
            $patterns = [
                '~\bFROM\s+`?([a-zA-Z_][a-zA-Z0-9_]*)`?~i',
                '~\bJOIN\s+`?([a-zA-Z_][a-zA-Z0-9_]*)`?~i',
                '~^\s*UPDATE\s+`?([a-zA-Z_][a-zA-Z0-9_]*)`?~i',
                '~\bINSERT\s+INTO\s+`?([a-zA-Z_][a-zA-Z0-9_]*)`?~i',
                '~\bDELETE\s+FROM\s+`?([a-zA-Z_][a-zA-Z0-9_]*)`?~i',
            ];
            foreach ($patterns as $pattern) {
                if (preg_match_all($pattern, $string, $matches)) {
                    foreach ($matches[1] as $table) {
                        $table = strtolower($table);
                        $sqlTableRefs[$table][] = rel_path($root, $file);
                    }
                }
            }
        }
    }

    foreach ($sqlTableRefs as $table => $fromFiles) {
        if (!in_array($table, $schemaTables, true)) {
            add_error($errors, 'SQL table reference not present in schema.sql: ' . $table . ' (seen in ' . implode(', ', array_unique($fromFiles)) . ')');
        }
    }

    $seed = file_get_contents($dbDir . '/seed.sql') ?: '';
    if (!str_contains($seed, 'INSERT INTO admins')) {
        add_warning($warnings, 'seed.sql does not contain INSERT INTO admins.');
    }

    add_note($notes, 'Schema tables detected: ' . count($schemaTables) . '.');
    add_note($notes, 'SQL table references checked: ' . count($sqlTableRefs) . '.');
}

$phpFiles = array_merge(collect_php_files($root . '/app'), collect_php_files($root . '/bootstrap'), collect_php_files($root . '/config'), collect_php_files($root . '/public'), collect_php_files($root . '/scripts'));
$classMap = build_class_map($root);
run_php_lint($phpFiles, $root, $errors, $warnings, $notes);
check_use_statements($classMap, $root, $errors, $notes);
check_routes($root, $classMap, $errors, $notes);
check_views($root, $errors, $notes);
check_sql_files($root, $errors, $warnings, $notes);

foreach (['RELEASE-CHECKLIST.md', 'MANUAL-TEST-CHECKLIST.md', 'API-TEST-EXAMPLES.md'] as $doc) {
    if (!file_exists($root . '/' . $doc)) {
        add_error($errors, 'Missing release document: ' . $doc);
    }
}

$version = trim((string) @file_get_contents($root . '/VERSION.txt'));
$lines = [
    'Inquiry Management System release check',
    'Version target: ' . ($version !== '' ? $version : 'unknown'),
    'Project root: ' . $root,
    str_repeat('=', 48),
    'Errors: ' . count($errors),
    'Warnings: ' . count($warnings),
    'Notes: ' . count($notes),
    '',
];
if ($errors !== []) {
    $lines[] = '[ERRORS]';
    foreach ($errors as $error) { $lines[] = '- ' . $error; }
    $lines[] = '';
}
if ($warnings !== []) {
    $lines[] = '[WARNINGS]';
    foreach ($warnings as $warning) { $lines[] = '- ' . $warning; }
    $lines[] = '';
}
if ($notes !== []) {
    $lines[] = '[NOTES]';
    foreach ($notes as $note) { $lines[] = '- ' . $note; }
    $lines[] = '';
}
echo implode(PHP_EOL, $lines) . PHP_EOL;
exit($errors === [] ? 0 : 1);
