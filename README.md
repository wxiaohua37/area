# 区域工具库

这是一个用于处理区域数据的PHP工具库，提供便捷的区域信息查询和操作功能。

## 安装
使用 Composer 安装：

```bash
composer require wxiaohua/area
```

## 功能特性

- 支持通过ID获取区域信息
- 路径解析与构建功能
- 地址格式化输出
- 按类型筛选区域数据
- 获取指定类型的上级区域

## 目录结构

```bash
src/
├── AreaFacade.php        # 门面类，提供静态调用接口
├── AreaUtils.php         # 核心工具类，实现主要功能
├── config/               # 配置文件目录
└── database/             # 数据文件目录（包含area.csv区域数据文件）
```

## 使用示例

### 初始化

```php
// 使用默认配置初始化
AreaFacade::init();

// 自定义配置初始化
AreaFacade::init([
    'csv_file_path' => '/path/to/custom/area.csv'
]);
```

### 基础用法

```php
// 获取区域信息
$area = AreaFacade::get(110100); // 获取北京市信息

// 解析区域路径
$result = AreaFacade::parse('全球/中国/北京市/朝阳区');

// 格式化地址输出
$formatted = AreaFacade::format(110105, ' '); // 输出：北京市 朝阳区

// 按类型获取区域
$provinces = AreaFacade::byType(AreaUtils::PROVINCE, function($area) {
    return $area['name'];
});

// 获取指定类型的上级区域
$provinceId = AreaFacade::parentOfType(110105, AreaUtils::PROVINCE);
```

## 单元测试

项目包含完整的单元测试，测试覆盖率高，确保功能稳定性。

## 许可证

本项目采用 Apache-2.0 许可证。详见 LICENSE 文件。