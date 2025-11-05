<p align="center"><a href="https://github.com/sphamster/bayes" target="_blank"><img src="./arts/logo.png" width="400" alt="Bayes Logo"></a></p>

<h1 align="center">Bayes: Naive Bayes Classifier for PHP</h1>

<p align="center">
  <strong>A powerful machine learning library for text classification, sentiment analysis, and multi-label categorization</strong>
</p>

<p align="center">
<a href="https://packagist.org/packages/sphamster/bayes"><img src="https://img.shields.io/packagist/v/sphamster/bayes.svg?style=flat-square&label=Latest%20Version" alt="Latest Version on Packagist"></a>
<a href="https://packagist.org/packages/sphamster/bayes"><img src="https://img.shields.io/packagist/dt/sphamster/bayes.svg?style=flat-square&label=Downloads" alt="Total Downloads"></a>
<a href="https://github.com/sphamster/bayes/actions"><img src="https://img.shields.io/github/actions/workflow/status/sphamster/bayes/test.yml?style=flat-square&label=tests" alt="GitHub Actions Status"></a>
<a href="https://codecov.io/gh/sphamster/bayes"><img src="https://img.shields.io/codecov/c/github/sphamster/bayes?style=flat-square&token=LUNB5R5SKZ" alt="Code Coverage"></a>
<br>
<a href="https://packagist.org/packages/sphamster/bayes"><img src="https://img.shields.io/packagist/dependency-v/sphamster/bayes/php.svg?style=flat-square&label=PHP" alt="PHP Version"></a>
<a href="https://phpstan.org/"><img src="https://img.shields.io/badge/PHPStan-level%20max-brightgreen.svg?style=flat-square" alt="PHPStan Level"></a>
<a href="https://github.com/sphamster/bayes/blob/master/LICENSE"><img src="https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square" alt="MIT License"></a>
<a href="https://github.com/sphamster/bayes"><img src="https://img.shields.io/badge/Maintained%3F-yes-brightgreen.svg?style=flat-square" alt="Maintained"></a>
</p>

---

## About Bayes

**Bayes** is a high-performance **Naive Bayes classifier** for **PHP 8.2+** that leverages **machine learning** to automatically categorize text documents into arbitrary categories. Built with modern PHP practices and rigorous code quality standards, it's the perfect solution for **natural language processing (NLP)** tasks in PHP applications.

Whether you're building **spam filters**, **sentiment analysis systems**, **content recommendation engines**, or **multi-label text categorization** tools, Bayes provides a simple yet powerful API backed by solid mathematical foundations.

> **Fork Notice:** This library is an enhanced fork of [niiknow/bayes](https://github.com/niiknow/bayes), rewritten with modern PHP 8.2 features, comprehensive test coverage, and strict type safety.

## Table of Contents

- [About Bayes](#about-bayes)
- [Table of Contents](#table-of-contents)
- [Key Features](#key-features)
- [Use Cases](#use-cases)
- [Setup](#setup)
  - [Requirements](#requirements)
- [Quick Start](#quick-start)
- [Single-Label Classification](#single-label-classification)
  - [Batch Training](#batch-training)
  - [Persistence](#persistence)
- [Multi-Label Classification](#multi-label-classification)
  - [Choosing Between Single-Label and Multi-Label](#choosing-between-single-label-and-multi-label)
  - [Available Prediction Filters](#available-prediction-filters)
  - [Custom Filters](#custom-filters)
- [Advanced Usage](#advanced-usage)
  - [Customizing the Tokenizer](#customizing-the-tokenizer)
  - [Working with Probabilities](#working-with-probabilities)
  - [Running Tests](#running-tests)
- [Credits](#credits)
- [License](#license)

## Key Features

- 🚀 **High Performance** - Optimized for speed with efficient probability calculations
- 🎯 **Multi-Label Support** - Classify documents into multiple categories simultaneously
- 🔧 **Customizable Tokenizers** - Plug in your own tokenization logic for any language
- 💾 **State Persistence** - Export and import trained classifiers as JSON
- 📊 **Multiple Filtering Strategies** - Threshold, Top-K, and Above-Mean filters included
- ✅ **Production Ready** - PHPStan max level, 100% type coverage, comprehensive test suite
- 🌍 **Framework Agnostic** - Works with Laravel, Symfony, or standalone PHP applications
- 📦 **Zero Dependencies** - Pure PHP implementation, no external libraries required

## Use Cases

Bayes excels at a wide range of **text classification** and **machine learning** tasks like:

- Spam Detection & Email Filtering
- Sentiment Analysis
- Content Categorization
- Intent Recognition
- Multi-Label Tagging
- Language Detection

## Setup

You can install the package via Composer:

```bash
composer require sphamster/bayes
```

### Requirements

- **PHP 8.2** or higher
- No other dependencies required

## Quick Start

Get up and running with Bayes in less than 5 minutes:

```php
<?php

use Sphamster\SingleLabelBayes;

// Create a new classifier instance (uses DefaultTokenizer automatically)
$classifier = new SingleLabelBayes();

// Train with positive examples
$classifier->train('amazing, awesome movie!! Yeah!! Oh boy.', 'positive');
$classifier->train('Sweet, this is incredibly, amazing, perfect, great!!', 'positive');

// Train with negative examples
$classifier->train('terrible, shitty thing. Damn. Sucks!!', 'negative');

// Predict the category of new text
$result = $classifier->predict('awesome, cool, amazing!! Yay.');
// Returns: 'positive'

// Get probability scores for all categories
$probabilities = $classifier->probabilities('awesome, cool, amazing!! Yay.');
// Returns: array of Probability objects with log probabilities

// Export the trained model for later use
$json = $classifier->export();

// Import a previously trained model
$classifier->import($json);
```

## Single-Label Classification

Use the `SingleLabelBayes` class when each document belongs to **exactly one category**. This is ideal for tasks like spam detection, sentiment analysis, or any classification where outcomes are mutually exclusive.

### Batch Training

Train on multiple examples at once:

```php
$classifier->trainOn([
    ['sample' => 'This movie is fantastic!', 'label' => 'positive'],
    ['sample' => 'Loved every minute of it', 'label' => 'positive'],
    ['sample' => 'Waste of time and money', 'label' => 'negative'],
    ['sample' => 'Absolutely terrible film', 'label' => 'negative'],
]);
```

### Persistence

Save and restore your trained models:

```php
// Export to JSON
$json = $classifier->export();
file_put_contents('sentiment-model.json', $json);

// Import from JSON
$json = file_get_contents('sentiment-model.json');
$classifier->import($json);
```

## Multi-Label Classification

For documents that can belong to **multiple categories simultaneously**, use `MultiLabelBayes`. Perfect for news article tagging, product categorization, or any scenario where a single piece of content fits multiple classifications:

```php
use Sphamster\MultiLabelBayes;
use Sphamster\Support\Filters\ThresholdFilter;
use Sphamster\Support\Filters\TopKFilter;
use Sphamster\Support\Filters\AboveMeanFilter;

// Create a new multi-label classifier (uses DefaultTokenizer automatically)
$classifier = new MultiLabelBayes();

// Train with multiple labels per sample
$classifier->train(
    'iPhone 15 Pro price drops as Samsung releases new Galaxy',
    ['technology', 'business', 'mobile']
);

$classifier->train(
    'AI breakthrough helps detect cancer in early stages',
    ['technology', 'health', 'science']
);

$classifier->train(
    'Stock market crashes amid banking crisis',
    ['business', 'finance', 'economy']
);

// Predict using different strategies:

// 1. Threshold Filter: Get all categories above 30% probability
$predictions = $classifier->predict(
    'Tech company stocks rise after AI announcement',
    new ThresholdFilter(0.3)
);
// Returns: [Probability('technology', ...), Probability('business', ...)]

// 2. Top-K Filter: Get top 2 most likely categories
$predictions = $classifier->predict(
    'Medical AI startup secures funding',
    new TopKFilter(2)
);
// Returns: top 2 categories by probability

// 3. Above Mean Filter: Get categories above average probability
$predictions = $classifier->predict(
    'Electric cars impact oil industry',
    new AboveMeanFilter()
);
// Returns: categories with above-average probability

// Extract category names
$categories = array_map(fn($p) => $p->category(), $predictions);

// Batch training
$classifier->trainOn([
    [
        'sample' => 'New electric vehicle startup raises $500M',
        'labels' => ['technology', 'business', 'automotive']
    ],
    [
        'sample' => 'Machine learning improves weather forecasting',
        'labels' => ['technology', 'science', 'environment']
    ],
]);
```

### Choosing Between Single-Label and Multi-Label

| Classifier         | Best For                         | Example Use Cases                                      |
| ------------------ | -------------------------------- | ------------------------------------------------------ |
| `SingleLabelBayes` | One category per document        | Spam detection, sentiment analysis, language detection |
| `MultiLabelBayes`  | Multiple categories per document | News tagging, product categorization, skill assessment |

### Available Prediction Filters

| Filter                                    | Description                                     | Use Case                                          |
| ----------------------------------------- | ----------------------------------------------- | ------------------------------------------------- |
| `ThresholdFilter(float $threshold = 0.3)` | Returns categories with probability ≥ threshold | Medical diagnosis (flag all conditions above 30%) |
| `TopKFilter(int $k = 3)`                  | Returns top K categories by probability         | Content recommendations (show top 3 topics)       |
| `AboveMeanFilter()`                       | Returns categories above mean probability       | Adaptive filtering based on distribution          |

### Custom Filters

You can create custom filtering strategies by implementing the `PredictionFilter` interface:

```php
use Sphamster\Contracts\PredictionFilter;
use Sphamster\Support\Probability;

class MyCustomFilter implements PredictionFilter
{
    public function filter(array $probabilities): array
    {
        // Your custom filtering logic
        return array_filter($probabilities, function(Probability $p) {
            return exp($p->log()) > 0.5;
        });
    }
}

$predictions = $classifier->predict('Sample text', new MyCustomFilter());
```

## Advanced Usage

### Customizing the Tokenizer

By default, the classifier uses `DefaultTokenizer` which:
- Converts text to lowercase
- Extracts only alphabetic characters
- Does NOT remove stopwords or perform stemming

To use your own custom tokenizer, create a class that implements the `Tokenizer` interface and pass an instance of it to the constructor. For example:

```php
<?php
use Sphamster\Contracts\Tokenizer;

class MyCustomTokenizer implements Tokenizer
{
    public function tokenize(string $text): array
    {
        // Define your custom stopwords
        $stopwords = ['the', 'and', 'is', 'are', 'was', 'were'];
        // Build a regex pattern to match stopwords
        $pattern = '~\b(' . implode('|', array_map('preg_quote', $stopwords)) . ')\b~i';

        // Convert the text to lowercase and remove stopwords
        $clean_text = preg_replace($pattern, '', mb_strtolower($text));

        // Extract tokens consisting only of alphabetic characters
        preg_match_all('/[[:alpha:]]+/u', $clean_text, $matches);

        return $matches[0] ?? [];
    }
}

// Instantiate your custom tokenizer and pass it to SingleLabelBayes
$tokenizer = new MyCustomTokenizer();
$classifier = new \Sphamster\SingleLabelBayes(tokenizer: $tokenizer);

// Works with both single-label and multi-label classifiers
$multi_label_classifier = new \Sphamster\MultiLabelBayes(tokenizer: $tokenizer);
```

### Working with Probabilities

Access raw probability scores for deeper analysis:

```php
// Get probabilities for all categories
$probabilities = $classifier->probabilities('This is amazing!');

foreach ($probabilities as $probability) {
    echo sprintf(
        "Category: %s, Log Probability: %.4f, Probability: %.4f\n",
        $probability->category(),
        $probability->log(),
        exp($probability->log())
    );
}
```

### Running Tests

```bash
# Run all tests (refactor, lint, types, unit)
composer test

# Run individual test suites
composer test:unit       # PHPUnit/Pest tests
composer test:types      # PHPStan static analysis
composer test:lint       # PSR-12 code style check
composer test:refactor   # Rector refactoring rules

# Auto-fix code style issues
composer lint            # Format code with Pint
composer refactor        # Apply Rector rules
```

## Credits

- **Author**: [Andrea Civita](https://github.com/andreacivita)
- **Original Library**: [niiknow/bayes](https://github.com/niiknow/bayes)
- Built with ❤️ by [Sphamster](https://github.com/sphamster)

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.

---

<p align="center">
  <strong>Need help?</strong> Open an issue on <a href="https://github.com/sphamster/bayes/issues">GitHub</a>
  <br>
  <strong>⭐ Found this useful?</strong> Give it a star on <a href="https://github.com/sphamster/bayes">GitHub</a>!
</p>
