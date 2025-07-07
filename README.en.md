

# Regional Tools Library

This is a PHP tool library for handling regional data, providing convenient functions for querying and manipulating regional information.

## Installation
Install via Composer:

```bash
composer require wxiaohua/area
```

## Features

- Retrieve regional information by ID
- Path parsing and building capabilities
- Address formatting output
- Filter regional data by type
- Obtain parent regions of a specified type

## Directory Structure

```bash
src/
├── AreaFacade.php        # Facade class, provides static calling interfaces
├── AreaUtils.php         # Core utility class, implements main functionalities
├── config/               # Configuration file directory
└── database/             # Data file directory (contains area.csv, the regional data file)
```

## Usage Examples

### Initialization

```php
// Initialize using default configuration
AreaFacade::init();

// Initialize with custom configuration
AreaFacade::init([
    'csv_file_path' => '/path/to/custom/area.csv'
]);
```

### Basic Usage

```php
// Retrieve regional information
$area = AreaFacade::get(110100); // Get information about Beijing

// Parse a regional path
$result = AreaFacade::parse('Global/China/Beijing/C朝阳区'); 

// Format address output
$formatted = AreaFacade::format(110105, ' '); // Output: Beijing Chaoyang District

// Get regions by type
$provinces = AreaFacade::byType(AreaUtils::PROVINCE, function($area) {
    return $area['name'];
});

// Get parent region of a specified type
$provinceId = AreaFacade::parentOfType(110105, AreaUtils::PROVINCE);
```

## Unit Testing

The project includes comprehensive unit tests with high test coverage to ensure functional stability.

## License

This project is released under the Apache-2.0 License. Please see the LICENSE file for details.