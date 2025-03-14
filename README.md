<p align="center">
<picture>
 <source media="(prefers-color-scheme: dark)" srcset="./public/logo.png" width="250">
 <img alt="sphamster logo" src="./arts/logo.png" width="250">
</picture>
</p>

# `Bayes`: A Naive-Bayes classifier for PHP 8

`Bayes` takes a document (piece of text), and tells you what category that document belongs to.

This library is a fork from php lib @ https://github.com/niiknow/bayes

## What can I use this for?

You can use this for categorizing any text content into any arbitrary set of **categories**. For example:

- is an email **spam**, or **not spam** ?
- is a news article about **technology**, **politics**, or **sports** ?
- is a piece of text expressing **positive** emotions, or **negative** emotions?

## Usage

```php
$classifier = new \Sphamster\Bayes();

// teach it positive phrases

$classifier->train('amazing, awesome movie!! Yeah!! Oh boy.', 'positive');
$classifier->train('Sweet, this is incredibly, amazing, perfect, great!!', 'positive');

// teach it a negative phrase

$classifier->predict('terrible, shitty thing. Damn. Sucks!!', 'negative');

// now ask it to predict a document it has never seen before

$classifier->predict('awesome, cool, amazing!! Yay.');
// => 'positive'

// serialize the classifier's state as a JSON string.
$stateJson = $classifier->export();

// load the classifier back from its JSON representation.
$classifier->import($stateJson);

```

## Setup

```
composer require sphamster/bayes
```

## Customizing the Tokenizer

To use your own custom tokenizer, create a class that implements the `Tokenizer` interface and pass an instance of it to
the `Bayes` constructor. For example:

```php
<?php
use Sphamster\Contracts\Tokenizer;

class MyCustomTokenizer implements Tokenizer
{
    public function tokenize(string $text): array
    {
        // Define your stopwords
        $stopwords = ['der', 'die', 'das', 'the'];
        // Build a regex pattern to match stopwords
        $pattern = '~\b(' . implode('|', array_map('preg_quote', $stopwords)) . ')\b~i';
        
        // Convert the text to lowercase and remove stopwords
        $cleanText = preg_replace($pattern, '', mb_strtolower($text));
        
        // Extract tokens consisting only of alphabetic characters
        preg_match_all('/[[:alpha:]]+/u', $cleanText, $matches);
        
        return $matches[0] ?? [];
    }
}

// Instantiate your custom tokenizer and pass it to XBayes
$tokenizer = new MyCustomTokenizer();
$classifier = new \Sphamster\Bayes(tokenizer:$tokenizer);

```

## Testing

```bash
  composer test
```