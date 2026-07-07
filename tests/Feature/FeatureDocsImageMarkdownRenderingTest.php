<?php

test('markdown image in docs content has lightbox trigger wrapper', function () {
    $converter = app('blogr-docs.converter');

    $html = $converter->convert('![Alt text](https://example.com/image.jpg)')->getContent();

    expect($html)
        ->toContain('class="blogr-lightbox-trigger"')
        ->and($html)->toContain('<a href="https://example.com/image.jpg"')
        ->and($html)->toContain('<img src="https://example.com/image.jpg"');
});

test('markdown image is NOT wrapped when inside a link in docs', function () {
    $converter = app('blogr-docs.converter');

    $html = $converter->convert('[![Alt](https://example.com/img.jpg)](https://example.com/page)')->getContent();

    expect($html)->not->toContain('blogr-lightbox-trigger');
});

test('docs converter registers ImageLightboxRenderer', function () {
    $converter = app('blogr-docs.converter');

    $htmlWithoutImage = $converter->convert('# Just a heading')->getContent();

    expect($htmlWithoutImage)->toContain('<h1>Just a heading</h1>');
});
