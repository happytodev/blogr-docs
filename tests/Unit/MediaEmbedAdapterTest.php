<?php

use Happytodev\BlogrDocs\Extensions\MediaEmbedAdapter;
use League\CommonMark\Extension\Embed\Embed;
use League\CommonMark\Extension\Embed\EmbedExtension;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\MarkdownConverter;

beforeEach(function () {
    $this->adapter = new MediaEmbedAdapter([
        'youtube' => true,
        'vimeo' => true,
        'dailymotion' => true,
        'spotify' => true,
        'soundcloud' => true,
        'deezer' => true,
        'apple_podcasts' => true,
    ]);
});

function converterWith(MediaEmbedAdapter $adapter): MarkdownConverter
{
    $environment = new Environment([
        'html_input' => 'escape',
        'allow_unsafe_links' => false,
        'embed' => [
            'adapter' => $adapter,
            'allowed_domains' => [],
            'fallback' => 'link',
        ],
    ]);

    $environment->addExtension(new CommonMarkCoreExtension);
    $environment->addExtension(new EmbedExtension);

    return new MarkdownConverter($environment);
}

it('converts youtube watch url to embed iframe', function () {
    $result = converterWith($this->adapter)->convert(
        'https://www.youtube.com/watch?v=dQw4w9WgXcQ'
    );

    expect($result->getContent())
        ->toContain('youtube-nocookie.com/embed/dQw4w9WgXcQ')
        ->and($result->getContent())->toContain('iframe')
        ->and($result->getContent())->toContain('media-embed');
});

it('converts youtube short url to embed iframe', function () {
    $result = converterWith($this->adapter)->convert(
        'https://youtu.be/dQw4w9WgXcQ'
    );

    expect($result->getContent())
        ->toContain('youtube-nocookie.com/embed/dQw4w9WgXcQ');
});

it('converts vimeo url to embed iframe', function () {
    $result = converterWith($this->adapter)->convert(
        'https://vimeo.com/123456789'
    );

    expect($result->getContent())
        ->toContain('player.vimeo.com/video/123456789')
        ->and($result->getContent())->toContain('iframe');
});

it('converts dailymotion url to embed iframe', function () {
    $result = converterWith($this->adapter)->convert(
        'https://www.dailymotion.com/video/abc123'
    );

    expect($result->getContent())
        ->toContain('dailymotion.com/embed/video/abc123');
});

it('converts spotify track url to embed iframe', function () {
    $result = converterWith($this->adapter)->convert(
        'https://open.spotify.com/track/4cOdK2wGLETKBW3PvgPWqT'
    );

    expect($result->getContent())
        ->toContain('open.spotify.com/embed/track/4cOdK2wGLETKBW3PvgPWqT');
});

it('converts spotify episode url to embed iframe', function () {
    $result = converterWith($this->adapter)->convert(
        'https://open.spotify.com/episode/7GxUhbCgR'
    );

    expect($result->getContent())
        ->toContain('open.spotify.com/embed/episode/7GxUhbCgR');
});

it('converts spotify playlist url to embed iframe', function () {
    $result = converterWith($this->adapter)->convert(
        'https://open.spotify.com/playlist/37i9dQZF1DXcBWIGoYBM5M'
    );

    expect($result->getContent())
        ->toContain('open.spotify.com/embed/playlist/37i9dQZF1DXcBWIGoYBM5M');
});

it('converts soundcloud url to embed iframe', function () {
    $result = converterWith($this->adapter)->convert(
        'https://soundcloud.com/user/track-name'
    );

    expect($result->getContent())
        ->toContain('w.soundcloud.com/player/')
        ->and($result->getContent())->toContain('soundcloud.com');
});

it('converts deezer track url to embed iframe', function () {
    $result = converterWith($this->adapter)->convert(
        'https://www.deezer.com/track/123456789'
    );

    expect($result->getContent())
        ->toContain('widget.deezer.com/widget/auto/track/123456789');
});

it('handles url not on its own line as plain text', function () {
    $result = converterWith($this->adapter)->convert(
        'Watch this video: https://www.youtube.com/watch?v=dQw4w9WgXcQ'
    );

    expect($result->getContent())
        ->toContain('youtube.com/watch')
        ->and($result->getContent())->not->toContain('iframe');
});

it('returns link when url is unknown', function () {
    $result = converterWith($this->adapter)->convert(
        'https://example.com/video'
    );

    expect($result->getContent())
        ->toContain('example.com/video')
        ->and($result->getContent())->not->toContain('iframe');
});

it('respects disabled platforms', function () {
    $adapter = new MediaEmbedAdapter([
        'youtube' => false,
    ]);

    $result = converterWith($adapter)->convert(
        'https://www.youtube.com/watch?v=dQw4w9WgXcQ'
    );

    expect($result->getContent())
        ->toContain('youtube.com/watch')
        ->and($result->getContent())->not->toContain('iframe');
});
