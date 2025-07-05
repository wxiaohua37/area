<?php

namespace XiaoHua\Area;

use RuntimeException;

class AreaUtils
{
    /** @var int 全球 */
    const ID_GLOBAL = 0;

    /** @var int 中国 */
    const ID_CHINA = 1;

    /** @var int 国家 */
    const COUNTRY = 1;

    /** @var int 省份 */
    const PROVINCE = 2;

    /** @var int 城市 */
    const CITY = 3;

    /** @var int 地区 */
    const DISTRICT = 4;

    private static ?self $instance = null;

    /** @var array<int, array{id: int, name: string, type: int, parent_id: ?int, children: array}> */
    private array $areas = [];
    private array $cache = [];
    private array $indexByParent = [];

    private function __construct(array $config = [])
    {
        $this->loadData($config);
    }

    public static function instance(array $config = []): self
    {
        return self::$instance ??= new self($config);
    }

    private function loadData(array $config): void
    {
        $csvPath = $config['csv_file_path'] ?? dirname(__DIR__) . "/src/database/area.csv";

        if (!is_readable($csvPath)) {
            throw new RuntimeException("Region data file not accessible: {$csvPath}");
        }

        $this->addGlobalNode();
        $this->parseCsv($csvPath);

        // 构建树形结构
        foreach ($this->areas as $id => &$area) {
            if ($area['parent_id'] !== null && isset($this->areas[$area['parent_id']])) {
                $this->areas[$area['parent_id']]['children'][] = &$area;
            }
        }
        unset($area);
    }

    private function addGlobalNode(): void
    {
        $this->areas[self::ID_GLOBAL] = [
            'id' => self::ID_GLOBAL,
            'name' => '全球',
            'type' => self::COUNTRY,
            'parent_id' => null,
        ];

        $this->indexByParent[null]['全球'] = &$this->areas[self::ID_GLOBAL];
    }

    private function parseCsv(string $filePath): void
    {
        $file = fopen($filePath, 'r');
        fgetcsv($file); // Skip header

        while ($row = fgetcsv($file)) {
            $this->processRow($row);
        }

        fclose($file);
    }

    private function processRow(array $row): void
    {
        if (count($row) < 4) return;

        [$id, $name, $type, $parentId] = array_map(fn($v) => trim($v), $row);
        $id = (int)$id;
        $parentId = (int)$parentId;

        if ($id === $parentId) {
            throw new RuntimeException("Invalid parent-child relationship: {$name} ({$id})");
        }

        $this->areas[$id] = [
            'id' => $id,
            'name' => $name,
            'type' => (int)$type,
            'parent_id' => $parentId,
            'children' => []  // 初始化 children 数组
        ];

        $this->indexByParent[$parentId][$name] = &$this->areas[$id];
    }

    public function get(int $id): ?array
    {
        return $this->areas[$id] ?? null;
    }

    public function parse(string $path, int $rootId = self::ID_CHINA): ?array
    {
        return $this->cache[__METHOD__][$path] ??= $this->resolvePath($path, $rootId);
    }

    private function resolvePath(string $path, int $rootId): ?array
    {
        $segments = array_filter(explode('/', $path), 'trim');
        foreach ($segments as $segment) {
            $rootId = $this->indexByParent[$rootId][$segment]['id'] ?? null;
            if ($rootId === null) return null;
        }

        return $this->areas[$rootId] ?? null;
    }

    public function path(array $areas): array
    {
        $result = [];
        foreach ($areas as $area) {
            $this->buildPaths($area, '', $result);
        }
        return $result;
    }

    private function buildPaths(array $area, string $parentPath, array &$result): void
    {
        // 构建当前路径
        $currentPath = $parentPath ? $parentPath . '/' . $area['name'] : $area['name'];
        $result[] = $currentPath;

        // 递归处理子节点
        if (!empty($area['children'])) {
            foreach ($area['children'] as $child) {
                $this->buildPaths($child, $currentPath, $result);
            }
        }
    }

    public function format(int $id, string $separator = ' '): ?string
    {
        $cacheKey = "{$id}{$separator}";
        return $this->cache[__METHOD__][$cacheKey] ??= $this->buildFormattedPath($id, $separator);
    }

    private function buildFormattedPath(int $id, string $separator): ?string
    {
        $path = [];
        $area = $this->get($id);

        while ($area && !in_array($area['id'], [self::ID_GLOBAL, self::ID_CHINA])) {
            if ($area['type'] === self::PROVINCE && mb_substr($area['name'], -1) === '市') {
                $path[] = mb_substr($area['name'], 0, -1);
            } else {
                $path[] = $area['name'];
            }
            $area = $area['parent_id'] ? $this->areas[$area['parent_id']] ?? null : null;
        }

        return $path ? implode($separator, array_reverse($path)) : null;
    }

    public function byType(int $type, callable $func): array
    {
        $result = [];
        foreach ($this->areas as $area) {
            if ($area['type'] === $type) {
                $result[] = $func($area);
            }
        }

        return $result;
    }

    public function parentOfType(int $id, int $type): ?int
    {
        $cacheKey = "{$id}_{$type}";
        return $this->cache[__METHOD__][$cacheKey] ??= $this->findParent($id, $type);
    }

    private function findParent(int $id, int $type): ?int
    {
        $area = $this->get($id);
        for ($i = 0; $i < 100 && $area; $i++) {
            if ($area['type'] === $type) return $area['id'];
            $area = $area['parent_id'] ? $this->areas[$area['parent_id']] ?? null : null;
        }

        return $area['id'];
    }
}