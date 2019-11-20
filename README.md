<p align="center">
    <img height="100" src="./docs/tarablade_logo.png"
                    alt="Tarablade Logo" title="Tarablade Logo">
</p>

# Tarablade
[![Build Status](https://travis-ci.org/barakamwakisha/tarablade.svg?branch=master)](https://travis-ci.org/barakamwakisha/tarablade)
[![codecov](https://codecov.io/gh/barakamwakisha/tarablade/branch/master/graph/badge.svg)](https://codecov.io/gh/barakamwakisha/tarablade)
[![StyleCI](https://github.styleci.io/repos/212110481/shield?branch=master)](https://github.styleci.io/repos/212110481)  
A package that quickly converts plain HTML files into Blade templates and integrates them into a Laravel project, together with their accompanying assets.

## Installation
Installation is straightforward, setup is similar to every other Laravel Package.

#### 1. Install via Composer

Begin by pulling in the package through Composer:

```
composer require mwakisha/tarablade
```

#### 2. Publish config File (OPTIONAL)

The config file allows you to override default settings of this package to meet your specific needs. The config file allows you to set a 'template namespace' for proper separation of template assets. It is optional if you intend to import only one template. You have to change the template namespace if you want to import another template.

To generate a config file type this command into your terminal:

```
php artisan vendor:publish --tag=tarablade-config
```

This generates a config file at `config/tarablade.php`.

## Usage
This package is easy to use. It quickly converts plain HTML files into Blade templates and integrates them into your Laravel project, together with their accompanying assets.

##### [IMPORTANT] What this package does NOT do

This package does not extract repeated code in the generated files into partial files. This is a feature in development and contributions are most welcome.

---

## 1. Importing the templates and assets
Run the command
```
php artisan tarablade:import
```
You will then proceed to enter the directory where your template files are.

## 2. That's basically it
All the template public assets will be copied into the public folder, the html files converted into blade files with asset and route helpers as well as an updated routes file to quickly get you up and running.

## Contact

I would love to hear from you. 
I am always on Twitter, and it is a great way to communicate with me or follow me. [Check me out on Twitter](https://twitter.com/_mwaks).

You can also email me at barakamwakisha@gmail.com for any other requests.
