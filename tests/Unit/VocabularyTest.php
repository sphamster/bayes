<?php

declare(strict_types=1);

use Sphamster\Support\Vocabularies\Vocabulary;

beforeEach(function (): void {
    $this->vocabulary = new Vocabulary();
});

it('starts empty', function (): void {
    expect($this->vocabulary->tokens())->toEqual([])
        ->and($this->vocabulary->size())->toBe(0);
});

it('adds tokens and checks existence', function (): void {
    $this->vocabulary->add('hello');
    expect($this->vocabulary->exists('hello'))->toBeTrue()
        ->and($this->vocabulary->tokens())->toEqual(['hello']);
});

it('does not add duplicate tokens', function (): void {
    $this->vocabulary->add('world');
    $this->vocabulary->add('world');
    expect($this->vocabulary->size())->toBe(1)
        ->and($this->vocabulary->tokens())->toEqual(['world']);
});

it('returns correct size after adding multiple tokens', function (): void {
    $this->vocabulary->add('a');
    $this->vocabulary->add('b');
    $this->vocabulary->add('c');
    expect($this->vocabulary->size())->toBe(3)
        ->and($this->vocabulary->tokens())->toEqual(['a', 'b', 'c']);
});

it('resets correctly', function (): void {
    $this->vocabulary->add('test');
    expect($this->vocabulary->size())->toBe(1);
    $this->vocabulary->reset();
    expect($this->vocabulary->tokens())->toEqual([])
        ->and($this->vocabulary->size())->toBe(0);
});
