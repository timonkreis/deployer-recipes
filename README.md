# ReadMe

This package provides several default configurations and recipes for [Deployer](https://deployer.org/).

Use the following command to install it via Composer:

```bash
composer require --dev timonkreis/deployer-recipes:@dev
```

## Usage

Import the desired recipe into your `deploy.php`:

```php
// For TYPO3
import(__DIR__ . '/vendor/timonkreis/deployer-recipes/recipes/typo3.php');

// For WordPress
import(__DIR__ . '/vendor/timonkreis/deployer-recipes/recipes/wordpress.php');
```
